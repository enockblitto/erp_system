<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'erp_system');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Erreur de connexion");
}

$clientId = $_SESSION['user_id'];

// Récupérer les infos client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
$stmt->execute([$clientId]);
$clientInfo = $stmt->fetch();

// Récupérer les projets
$stmt = $pdo->prepare("SELECT * FROM projets WHERE id_client = ? ORDER BY date_creation DESC");
$stmt->execute([$clientId]);
$mesProjets = $stmt->fetchAll();

// Récupérer les factures
$stmt = $pdo->prepare("
    SELECT f.*, p.titre as projet_titre
    FROM factures f
    JOIN projets p ON f.id_projet = p.id_projet
    WHERE f.id_client = ?
    ORDER BY f.date_creation DESC
    LIMIT 5
");
$stmt->execute([$clientId]);
$mesFactures = $stmt->fetchAll();

// Statistiques
$totalProjets = count($mesProjets);
$totalFactures = array_sum(array_column($mesFactures, 'montant'));
$totalPaye = array_sum(array_column($mesFactures, 'montant_paye'));
$soldeRestant = $totalFactures - $totalPaye;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Client - ERP System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #16a34a;
            --primary-dark: #15803d;
            --secondary: #0d9488;
            --success: #16a34a;
            --warning: #eab308;
            --danger: #dc2626;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --white: #ffffff;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
        }

        /* Même structure que les autres dashboards */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }

        .header-content {
            max-width: 1920px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray-700);
            padding: 8px;
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .menu-toggle:hover {
            background: var(--gray-100);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .icon-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border: none;
            background: var(--gray-100);
            border-radius: var(--radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            color: var(--gray-700);
        }

        .icon-btn:hover {
            background: var(--gray-200);
            transform: translateY(-2px);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .user-profile:hover {
            background: var(--gray-100);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            box-shadow: var(--shadow);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--gray-900);
        }

        .user-role {
            font-size: 12px;
            color: var(--gray-600);
        }

        /* Profile Modal */
        .profile-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .profile-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 480px;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translate(-50%, -40%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .profile-header {
            padding: 32px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 16px 16px 0 0;
            color: white;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            color: white;
            font-size: 18px;
            transition: var(--transition);
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 4px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            margin: 0 auto 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .profile-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .profile-email {
            font-size: 14px;
            opacity: 0.9;
        }

        .profile-body {
            padding: 24px 32px;
        }

        .profile-section {
            margin-bottom: 24px;
        }

        .profile-section-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .profile-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--gray-50);
            border-radius: var(--radius);
            margin-bottom: 8px;
            transition: var(--transition);
        }

        .profile-info-item:hover {
            background: var(--gray-100);
        }

        .profile-info-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 18px;
        }

        .profile-info-text {
            flex: 1;
        }

        .profile-info-label {
            font-size: 12px;
            color: var(--gray-600);
            margin-bottom: 2px;
        }

        .profile-info-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .profile-action-btn {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary-action {
            background: var(--primary);
            color: white;
        }

        .btn-primary-action:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary-action {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-secondary-action:hover {
            background: var(--gray-200);
        }

        .btn-danger-action {
            background: var(--danger);
            color: white;
        }

        .btn-danger-action:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Sidebar */
        .dashboard-layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--gray-200);
            padding: 24px 16px;
            transition: var(--transition);
        }

        .nav-section {
            margin-bottom: 32px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 12px 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--radius);
            margin-bottom: 4px;
            transition: var(--transition);
            font-size: 14px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: var(--gray-100);
            color: var(--gray-900);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .nav-item i {
            width: 20px;
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 32px;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--gray-600);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.primary {
            background: rgba(22, 163, 74, 0.1);
            color: var(--primary);
        }

        .stat-icon.secondary {
            background: rgba(13, 148, 136, 0.1);
            color: var(--secondary);
        }

        .stat-icon.warning {
            background: rgba(234, 179, 8, 0.1);
            color: var(--warning);
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .stat-subvalue {
            font-size: 14px;
            color: var(--gray-600);
            margin-top: 4px;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px;
            background: var(--gray-50);
            color: var(--gray-700);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--gray-100);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: var(--gray-50);
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.success {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        .badge.warning {
            background: rgba(234, 179, 8, 0.1);
            color: var(--warning);
        }

        .badge.danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
        }

        .badge.primary {
            background: rgba(22, 163, 74, 0.1);
            color: var(--primary);
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                left: -280px;
                top: 70px;
                height: calc(100vh - 70px);
                z-index: 99;
                box-shadow: var(--shadow-lg);
            }

            .sidebar.open {
                left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Profile Modal -->
    <div class="profile-modal" id="profileModal">
        <div class="profile-modal-content">
            <div class="profile-header">
                <button class="close-modal" onclick="closeProfileModal()">&times;</button>
                <div class="profile-avatar-large">
                    <?= strtoupper(substr($_SESSION['nom'], 0, 2)) ?>
                </div>
                <div class="profile-name"><?= htmlspecialchars($_SESSION['nom']) ?></div>
                <div class="profile-email"><?= htmlspecialchars($_SESSION['email']) ?></div>
            </div>

            <div class="profile-body">
                <div class="profile-section">
                    <div class="profile-section-title">Informations</div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Type de compte</div>
                            <div class="profile-info-value">Client</div>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Entreprise</div>
                            <div class="profile-info-value"><?= htmlspecialchars($clientInfo['entreprise'] ?? 'Non renseigné') ?></div>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Téléphone</div>
                            <div class="profile-info-value"><?= htmlspecialchars($clientInfo['telephone'] ?? 'Non renseigné') ?></div>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Localisation</div>
                            <div class="profile-info-value"><?= htmlspecialchars($clientInfo['ville'] ?? 'Non renseigné') ?></div>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <div class="profile-section-title">Actions</div>
                    <div class="profile-actions">
                        <a href="#" class="profile-action-btn btn-primary-action">
                            <i class="fas fa-user-edit"></i>
                            Modifier mon profil
                        </a>
                        <a href="#" class="profile-action-btn btn-secondary-action">
                            <i class="fas fa-cog"></i>
                            Paramètres
                        </a>
                        <a href="#" class="profile-action-btn btn-secondary-action">
                            <i class="fas fa-lock"></i>
                            Changer le mot de passe
                        </a>
                        <a href="login.php?logout=1" class="profile-action-btn btn-danger-action">
                            <i class="fas fa-sign-out-alt"></i>
                            Se déconnecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo">
                    <i class="fas fa-handshake"></i>
                    <span>ERP System</span>
                </div>
            </div>

            <div class="header-actions">
                <button class="icon-btn">
                    <i class="fas fa-bell"></i>
                </button>

                <button class="icon-btn">
                    <i class="fas fa-envelope"></i>
                </button>

                <div class="user-profile" onclick="openProfileModal()">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['nom'], 0, 2)) ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['nom']) ?></div>
                        <div class="user-role">Client</div>
                    </div>
                    <i class="fas fa-chevron-down" style="color: var(--gray-600); font-size: 12px;"></i>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <nav>
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <a href="client-dashboard.php" class="nav-item active">
                        <i class="fas fa-home"></i>
                        <span>Tableau de bord</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-project-diagram"></i>
                        <span>Mes Projets</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-file-invoice"></i>
                        <span>Mes Factures</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Communication</div>
                    <a href="#" class="nav-item">
                        <i class="fas fa-comments"></i>
                        <span>Messages</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-headset"></i>
                        <span>Support</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Personnel</div>
                    <a href="#" class="nav-item">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-file-alt"></i>
                        <span>Documents</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Bienvenue, <?= htmlspecialchars(explode(' ', $_SESSION['nom'])[0]) ?> ! 👋</h1>
                <p class="page-subtitle">Suivi de vos projets et factures</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon primary">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                    <div class="stat-label">Mes Projets</div>
                    <div class="stat-value"><?= $totalProjets ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon secondary">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <div class="stat-label">Total Facturé</div>
                    <div class="stat-value"><?= number_format($totalFactures / 1000000, 1) ?>M</div>
                    <div class="stat-subvalue">FCFA</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon warning">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <div class="stat-label">Solde Restant</div>
                    <div class="stat-value" style="color: <?= $soldeRestant > 0 ? 'var(--warning)' : 'var(--primary)' ?>">
                        <?= number_format($soldeRestant / 1000000, 1) ?>M
                    </div>
                    <div class="stat-subvalue">FCFA</div>
                </div>
            </div>

            <!-- Projets -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-project-diagram"></i>
                        Mes Projets
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (empty($mesProjets)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>Aucun projet pour le moment</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Titre du Projet</th>
                                    <th>Budget</th>
                                    <th>Date Début</th>
                                    <th>Statut</th>
                                    <th>Progression</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mesProjets as $projet): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($projet['titre']) ?></strong></td>
                                    <td><?= number_format($projet['budget'], 0, ',', ' ') ?> FCFA</td>
                                    <td><?= date('d/m/Y', strtotime($projet['date_debut'])) ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match($projet['statut']) {
                                            'en_cours' => 'primary',
                                            'termine' => 'success',
                                            'annule' => 'danger',
                                            default => 'warning'
                                        };
                                        $statutLabel = match($projet['statut']) {
                                            'en_cours' => 'En cours',
                                            'termine' => 'Terminé',
                                            'annule' => 'Annulé',
                                            default => 'En attente'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="flex: 1; height: 6px; background: var(--gray-200); border-radius: 3px; overflow: hidden;">
                                                <div style="width: <?= $projet['progression'] ?>%; height: 100%; background: var(--primary); transition: width 0.3s;"></div>
                                            </div>
                                            <span style="font-size: 12px; font-weight: 600; color: var(--gray-700);"><?= $projet['progression'] ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Factures -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-file-invoice"></i>
                        Factures Récentes
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (empty($mesFactures)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>Aucune facture pour le moment</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Projet</th>
                                    <th>Montant</th>
                                    <th>Payé</th>
                                    <th>Restant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mesFactures as $facture): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($facture['numero_facture']) ?></strong></td>
                                    <td><?= htmlspecialchars($facture['projet_titre']) ?></td>
                                    <td><?= number_format($facture['montant'], 0, ',', ' ') ?></td>
                                    <td style="color: var(--success);"><?= number_format($facture['montant_paye'], 0, ',', ' ') ?></td>
                                    <td style="color: <?= ($facture['montant'] - $facture['montant_paye']) > 0 ? 'var(--warning)' : 'var(--gray-600)' ?>">
                                        <?= number_format($facture['montant'] - $facture['montant_paye'], 0, ',', ' ') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($facture['statut']) {
                                            'payee' => 'success',
                                            'partiellement_payee' => 'warning',
                                            'annulee' => 'danger',
                                            default => 'primary'
                                        };
                                        $statutLabel = match($facture['statut']) {
                                            'payee' => 'Payée',
                                            'partiellement_payee' => 'Partielle',
                                            'annulee' => 'Annulée',
                                            default => 'En attente'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($facture['date_facture'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        function openProfileModal() {
            document.getElementById('profileModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('profileModal');
            if (event.target === modal) {
                closeProfileModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeProfileModal();
            }
        });

        // Animations au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Animation scroll header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 0) {
                header.style.boxShadow = 'var(--shadow-md)';
            } else {
                header.style.boxShadow = 'var(--shadow)';
            }
        });
    </script>
</body>
</html>