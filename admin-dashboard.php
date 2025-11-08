<?php
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'erp_system');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Créer les tables manquantes si elles n'existent pas
createMissingTables($pdo);

function createMissingTables($pdo) {
    $tables = [
        'activites' => "CREATE TABLE IF NOT EXISTS activites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(255) NOT NULL,
            description TEXT,
            date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
            user_id INT
        )",
        
        'notifications' => "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            message TEXT,
            type VARCHAR(50),
            statut VARCHAR(20) DEFAULT 'non_lu',
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            user_id INT
        )",
        
        'messages' => "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expediteur_id INT,
            destinataire_id INT,
            sujet VARCHAR(255),
            contenu TEXT,
            statut VARCHAR(20) DEFAULT 'non_lu',
            date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $tableName => $createSQL) {
        try {
            // Vérifier si la table existe
            $pdo->query("SELECT 1 FROM $tableName LIMIT 1");
        } catch (PDOException $e) {
            // Table n'existe pas, la créer
            $pdo->exec($createSQL);
            
            // Insérer des données d'exemple pour les nouvelles tables
            if ($tableName === 'activites') {
                $sampleActivities = [
                    ['Connexion', 'Connexion au système', $_SESSION['user_id']],
                    ['Création projet', 'Nouveau projet "Site E-commerce" créé', $_SESSION['user_id']],
                    ['Mise à jour client', 'Informations client ABC Corporation mises à jour', $_SESSION['user_id']],
                    ['Génération facture', 'Facture #F2024001 générée', $_SESSION['user_id']],
                    ['Rapport mensuel', 'Rapport du mois généré', $_SESSION['user_id']]
                ];
                
                $stmt = $pdo->prepare("INSERT INTO activites (action, description, user_id) VALUES (?, ?, ?)");
                foreach ($sampleActivities as $activity) {
                    $stmt->execute($activity);
                }
            }
            
            if ($tableName === 'notifications') {
                $sampleNotifications = [
                    ['Nouveau message', 'Vous avez reçu un nouveau message de Jean Dupont', 'info', $_SESSION['user_id']],
                    ['Projet en retard', 'Le projet "Application Mobile" est en retard', 'warning', $_SESSION['user_id']],
                    ['Facture payée', 'La facture #F2024001 a été payée', 'success', $_SESSION['user_id']]
                ];
                
                $stmt = $pdo->prepare("INSERT INTO notifications (titre, message, type, user_id) VALUES (?, ?, ?, ?)");
                foreach ($sampleNotifications as $notification) {
                    $stmt->execute($notification);
                }
            }
            
            if ($tableName === 'messages') {
                $sampleMessages = [
                    [2, $_SESSION['user_id'], 'Réunion projet', 'Bonjour, la réunion est prévue pour demain 10h.', 'non_lu'],
                    [3, $_SESSION['user_id'], 'Documentation', 'La documentation que vous avez demandée est prête.', 'non_lu']
                ];
                
                $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu, statut) VALUES (?, ?, ?, ?, ?)");
                foreach ($sampleMessages as $message) {
                    $stmt->execute($message);
                }
            }
        }
    }
}

// Récupérer les statistiques avec gestion d'erreurs
$stats = [];
$projetsRecents = [];
$facturesRecentes = [];
$activitesRecentes = [];
$notifications = [];
$messages = [];

try {
    // Total utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE statut = 'actif'");
    $stats['users'] = $stmt->fetch()['total'];

    // Total clients
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clients WHERE statut = 'actif'");
    $stats['clients'] = $stmt->fetch()['total'];

    // Total projets
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projets");
    $stats['projets'] = $stmt->fetch()['total'];

    // Projets en cours
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projets WHERE statut = 'en_cours'");
    $stats['projets_actifs'] = $stmt->fetch()['total'];

    // Revenus total
    $stmt = $pdo->query("SELECT SUM(montant_paye) as total FROM factures");
    $stats['revenus'] = $stmt->fetch()['total'] ?? 0;

    // Factures en attente
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM factures WHERE statut = 'en_attente'");
    $stats['factures_attente'] = $stmt->fetch()['total'];

    // Projets récents
    $stmt = $pdo->query("
        SELECT p.*, c.nom as client_nom, c.entreprise
        FROM projets p
        JOIN clients c ON p.id_client = c.id_client
        ORDER BY p.date_creation DESC
        LIMIT 5
    ");
    $projetsRecents = $stmt->fetchAll();

    // Factures récentes
    $stmt = $pdo->query("
        SELECT f.*, c.nom as client_nom, p.titre as projet_titre
        FROM factures f
        JOIN clients c ON f.id_client = c.id_client
        JOIN projets p ON f.id_projet = p.id_projet
        ORDER BY f.date_creation DESC
        LIMIT 5
    ");
    $facturesRecentes = $stmt->fetchAll();

    // Activités récentes
    $stmt = $pdo->query("
        SELECT * FROM activites 
        ORDER BY date_action DESC 
        LIMIT 5
    ");
    $activitesRecentes = $stmt->fetchAll();

    // Notifications
    $stmt = $pdo->query("
        SELECT * FROM notifications 
        WHERE statut = 'non_lu' 
        ORDER BY date_creation DESC 
        LIMIT 10
    ");
    $notifications = $stmt->fetchAll();

    // Messages
    $stmt = $pdo->query("
        SELECT m.*, u.prenom, u.nom 
        FROM messages m 
        JOIN users u ON m.expediteur_id = u.id 
        WHERE m.destinataire_id = " . $_SESSION['user_id'] . " 
        AND m.statut = 'non_lu' 
        ORDER BY m.date_envoi DESC 
        LIMIT 10
    ");
    $messages = $stmt->fetchAll();

} catch (PDOException $e) {
    // Gérer les erreurs silencieusement pour ne pas casser l'interface
    error_log("Erreur base de données: " . $e->getMessage());
}

// Traitement de la recherche
$resultatsRecherche = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = '%' . $_GET['search'] . '%';
    
    try {
        // Recherche dans les projets
        $stmt = $pdo->prepare("
            SELECT p.*, c.nom as client_nom, 'projet' as type
            FROM projets p 
            JOIN clients c ON p.id_client = c.id_client 
            WHERE p.titre LIKE ? OR p.description LIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        $resultatsProjets = $stmt->fetchAll();
        
        // Recherche dans les clients
        $stmt = $pdo->prepare("
            SELECT *, 'client' as type 
            FROM clients 
            WHERE nom LIKE ? OR entreprise LIKE ? OR email LIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $resultatsClients = $stmt->fetchAll();
        
        // Recherche dans les utilisateurs
        $stmt = $pdo->prepare("
            SELECT *, 'utilisateur' as type 
            FROM users 
            WHERE prenom LIKE ? OR nom LIKE ? OR email LIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $resultatsUsers = $stmt->fetchAll();
        
        $resultatsRecherche = array_merge($resultatsProjets, $resultatsClients, $resultatsUsers);
    } catch (PDOException $e) {
        error_log("Erreur recherche: " . $e->getMessage());
    }
}

// Traitement des actions du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_profile'])) {
            // Mettre à jour le profil
            $prenom = $_POST['prenom'];
            $nom = $_POST['nom'];
            $email = $_POST['email'];
            
            $stmt = $pdo->prepare("UPDATE users SET prenom = ?, nom = ?, email = ? WHERE id = ?");
            $stmt->execute([$prenom, $nom, $email, $_SESSION['user_id']]);
            
            // Mettre à jour la session
            $_SESSION['prenom'] = $prenom;
            $_SESSION['nom'] = $nom;
            $_SESSION['email'] = $email;
            
            // Ajouter une activité
            $stmt = $pdo->prepare("INSERT INTO activites (action, description, user_id) VALUES (?, ?, ?)");
            $stmt->execute(['Mise à jour profil', 'Profil utilisateur mis à jour', $_SESSION['user_id']]);
            
            header('Location: admin-dashboard.php?success=profile_updated');
            exit;
        }
        
        if (isset($_POST['change_password'])) {
            // Changer le mot de passe
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Vérifier l'ancien mot de passe
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    
                    // Ajouter une activité
                    $stmt = $pdo->prepare("INSERT INTO activites (action, description, user_id) VALUES (?, ?, ?)");
                    $stmt->execute(['Changement mot de passe', 'Mot de passe utilisateur changé', $_SESSION['user_id']]);
                    
                    header('Location: admin-dashboard.php?success=password_changed');
                    exit;
                } else {
                    header('Location: admin-dashboard.php?error=password_mismatch');
                    exit;
                }
            } else {
                header('Location: admin-dashboard.php?error=wrong_password');
                exit;
            }
        }
        
        if (isset($_POST['update_settings'])) {
            // Mettre à jour les paramètres
            $theme = $_POST['theme'];
            $notifications_email = isset($_POST['notifications_email']) ? 1 : 0;
            $notifications_sms = isset($_POST['notifications_sms']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE users SET theme = ?, notifications_email = ?, notifications_sms = ? WHERE id = ?");
            $stmt->execute([$theme, $notifications_email, $notifications_sms, $_SESSION['user_id']]);
            
            // Ajouter une activité
            $stmt = $pdo->prepare("INSERT INTO activites (action, description, user_id) VALUES (?, ?, ?)");
            $stmt->execute(['Mise à jour paramètres', 'Paramètres du compte mis à jour', $_SESSION['user_id']]);
            
            header('Location: admin-dashboard.php?success=settings_updated');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erreur mise à jour profil: " . $e->getMessage());
        header('Location: admin-dashboard.php?error=database_error');
        exit;
    }
}

// Récupérer les paramètres utilisateur
try {
    $stmt = $pdo->prepare("SELECT theme, notifications_email, notifications_sms FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userSettings = $stmt->fetch() ?: ['theme' => 'light', 'notifications_email' => 1, 'notifications_sms' => 0];
} catch (PDOException $e) {
    $userSettings = ['theme' => 'light', 'notifications_email' => 1, 'notifications_sms' => 0];
    error_log("Erreur récupération paramètres: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur - ERP System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --secondary: #f97316;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #eab308;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
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
            height: 100vh;
            overflow: hidden;
        }

        /* HEADER */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0 32px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            box-shadow: var(--shadow);
            height: 70px;
        }

        .header-content {
            max-width: 1920px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
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

        .search-box {
            position: relative;
            width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 44px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-600);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .search-result-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-100);
            cursor: pointer;
            transition: var(--transition);
        }

        .search-result-item:hover {
            background: var(--gray-50);
        }

        .search-result-type {
            font-size: 10px;
            color: var(--gray-500);
            text-transform: uppercase;
            font-weight: 600;
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

        .icon-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--danger);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid var(--white);
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            min-width: 300px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .dropdown-header {
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            color: var(--gray-900);
        }

        .dropdown-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-100);
            cursor: pointer;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--gray-50);
        }

        .dropdown-footer {
            padding: 12px 16px;
            text-align: center;
            border-top: 1px solid var(--gray-200);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
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

        /* MODALS */
        .modal {
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

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 487px;
            max-height: 90vh;
            overflow-y: auto;
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

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray-600);
            transition: var(--transition);
        }

        .close-modal:hover {
            color: var(--gray-900);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* PROFILE MODAL */
        .profile-header {
            padding: 32px;
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 16px 16px 0 0;
            color: white;
            position: relative;
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
            padding: 24px;
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

        .btn {
            padding: 12px 20px;
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

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* FORM STYLES */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        /* SIDEBAR */
        .dashboard-layout {
            display: flex;
            height: calc(100vh - 70px);
            margin-top: 70px;
        }

        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--gray-200);
            padding: 24px 16px;
            transition: var(--transition);
            overflow-y: auto;
            flex-shrink: 0;
        }

        .sidebar.collapsed {
            width: 80px;
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

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
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

        /* STATS CARDS */
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
            background: rgba(13, 148, 136, 0.1);
            color: var(--primary);
        }

        .stat-icon.secondary {
            background: rgba(249, 115, 22, 0.1);
            color: var(--secondary);
        }

        .stat-icon.success {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        .stat-icon.warning {
            background: rgba(234, 179, 8, 0.1);
            color: var(--warning);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 20px;
        }

        .stat-trend.up {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
        }

        .stat-trend.down {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
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

        .stat-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--gray-100);
        }

        .stat-compare {
            font-size: 12px;
            color: var(--gray-600);
        }

        /* CARDS */
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

        .card-actions {
            display: flex;
            gap: 8px;
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
            background: rgba(13, 148, 136, 0.1);
            color: var(--primary);
        }

        /* ALERT MESSAGES */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
            border: 1px solid rgba(22, 163, 74, 0.2);
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.2);
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

            .search-box {
                display: none;
            }

            .main-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Profile Modal -->
    <div class="modal" id="profileModal">
        <div class="modal-content">
            <div class="profile-header">
                <button class="close-modal" onclick="closeModal('profileModal')">&times;</button>
                <div class="profile-avatar-large">
                    <?= strtoupper(substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1)) ?>
                </div>
                <div class="profile-name"><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></div>
                <div class="profile-email"><?= htmlspecialchars($_SESSION['email']) ?></div>
            </div>

            <div class="profile-body">
                <div class="profile-section">
                    <div class="profile-section-title">Informations</div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Rôle</div>
                            <div class="profile-info-value">Administrateur</div>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Dernière connexion</div>
                            <div class="profile-info-value"><?= date('d/m/Y à H:i', $_SESSION['login_time']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <div class="profile-section-title">Actions</div>
                    <div class="profile-actions">
                        <button class="btn btn-primary" onclick="openModal('editProfileModal')">
                            <i class="fas fa-user-edit"></i>
                            Modifier mon profil
                        </button>
                        <button class="btn btn-secondary" onclick="openModal('settingsModal')">
                            <i class="fas fa-cog"></i>
                            Paramètres du compte
                        </button>
                        <button class="btn btn-secondary" onclick="openModal('passwordModal')">
                            <i class="fas fa-lock"></i>
                            Changer le mot de passe
                        </button>
                        <a href="login.php?logout=1" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            Se déconnecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modifier le profil</h3>
                <button class="close-modal" onclick="closeModal('editProfileModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($_SESSION['prenom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($_SESSION['nom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_SESSION['email']) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary" name="update_profile">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div class="modal" id="passwordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Changer le mot de passe</h3>
                <button class="close-modal" onclick="closeModal('passwordModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('passwordModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary" name="change_password">Changer le mot de passe</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal" id="settingsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Paramètres du compte</h3>
                <button class="close-modal" onclick="closeModal('settingsModal')">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Thème</label>
                        <select class="form-control" name="theme">
                            <option value="light" <?= ($userSettings['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Clair</option>
                            <option value="dark" <?= ($userSettings['theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Sombre</option>
                            <option value="auto" <?= ($userSettings['theme'] ?? 'light') === 'auto' ? 'selected' : '' ?>>Automatique</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notifications</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="notifications_email" <?= ($userSettings['notifications_email'] ?? 1) ? 'checked' : '' ?>>
                            <label>Notifications par email</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="notifications_sms" <?= ($userSettings['notifications_sms'] ?? 0) ? 'checked' : '' ?>>
                            <label>Notifications par SMS</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('settingsModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary" name="update_settings">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications Dropdown -->
    <div class="dropdown">
        <div class="dropdown-content" id="notificationsDropdown">
            <div class="dropdown-header">
                <i class="fas fa-bell"></i>
                Notifications (<?= count($notifications) ?>)
            </div>
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="dropdown-item">
                        <div style="font-weight: 600; margin-bottom: 4px;"><?= htmlspecialchars($notif['titre']) ?></div>
                        <div style="font-size: 12px; color: var(--gray-600);"><?= htmlspecialchars($notif['message']) ?></div>
                        <div style="font-size: 10px; color: var(--gray-500); margin-top: 4px;">
                            <?= date('d/m/Y H:i', strtotime($notif['date_creation'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="dropdown-item" style="text-align: center; color: var(--gray-500);">
                    Aucune notification
                </div>
            <?php endif; ?>
            <div class="dropdown-footer">
                <a href="notifications.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                    Voir toutes les notifications
                </a>
            </div>
        </div>
    </div>

    <!-- Messages Dropdown -->
    <div class="dropdown">
        <div class="dropdown-content" id="messagesDropdown">
            <div class="dropdown-header">
                <i class="fas fa-envelope"></i>
                Messages (<?= count($messages) ?>)
            </div>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="dropdown-item">
                        <div style="font-weight: 600; margin-bottom: 4px;">
                            <?= htmlspecialchars($message['prenom'] . ' ' . $message['nom']) ?>
                        </div>
                        <div style="font-size: 12px; color: var(--gray-600);">
                            <?= htmlspecialchars(substr($message['contenu'], 0, 50)) ?>...
                        </div>
                        <div style="font-size: 10px; color: var(--gray-500); margin-top: 4px;">
                            <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="dropdown-item" style="text-align: center; color: var(--gray-500);">
                    Aucun nouveau message
                </div>
            <?php endif; ?>
            <div class="dropdown-footer">
                <a href="messages.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                    Voir tous les messages
                </a>
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
                    <i class="fas fa-building"></i>
                    <span>ERP System</span>
                </div>
            </div>

            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher..." onkeyup="handleSearch(event)">
                    <div class="search-results" id="searchResults"></div>
                </div>

                <button class="icon-btn" onclick="toggleDropdown('notificationsDropdown')">
                    <i class="fas fa-bell"></i>
                    <?php if (!empty($notifications)): ?>
                        <span class="badge"><?= count($notifications) ?></span>
                    <?php endif; ?>
                </button>

                <button class="icon-btn" onclick="toggleDropdown('messagesDropdown')">
                    <i class="fas fa-envelope"></i>
                    <?php if (!empty($messages)): ?>
                        <span class="badge"><?= count($messages) ?></span>
                    <?php endif; ?>
                </button>

                <div class="user-profile" onclick="openModal('profileModal')">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></div>
                        <div class="user-role">Administrateur</div>
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
                    <a href="admin-dashboard.php" class="nav-item active">
                        <i class="fas fa-home"></i>
                        <span>Tableau de bord</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytiques</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Gestion</div>
                    <a href="#" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Employés</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-handshake"></i>
                        <span>Clients</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-project-diagram"></i>
                        <span>Projets</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-file-invoice"></i>
                        <span>Factures</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Système</div>
                    <a href="#" class="nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Sécurité</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Messages d'alerte -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    $messages = [
                        'profile_updated' => 'Profil mis à jour avec succès!',
                        'password_changed' => 'Mot de passe changé avec succès!',
                        'settings_updated' => 'Paramètres mis à jour avec succès!'
                    ];
                    echo $messages[$_GET['success']] ?? 'Opération réussie!';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php
                    $messages = [
                        'password_mismatch' => 'Les mots de passe ne correspondent pas!',
                        'wrong_password' => 'Mot de passe actuel incorrect!',
                        'database_error' => 'Erreur de base de données!'
                    ];
                    echo $messages[$_GET['error']] ?? 'Une erreur est survenue!';
                    ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1 class="page-title">Tableau de bord</h1>
                <p class="page-subtitle">Vue d'ensemble de votre système ERP</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="fas fa-arrow-up"></i>
                            +12%
                        </div>
                    </div>
                    <div class="stat-label">Employés Actifs</div>
                    <div class="stat-value"><?= $stats['users'] ?? 0 ?></div>
                    <div class="stat-footer">
                        <div class="stat-compare">vs mois dernier</div>
                        <a href="#" style="color: var(--primary); font-size: 12px; font-weight: 600;">Voir détails →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon secondary">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="fas fa-arrow-up"></i>
                            +8%
                        </div>
                    </div>
                    <div class="stat-label">Clients Actifs</div>
                    <div class="stat-value"><?= $stats['clients'] ?? 0 ?></div>
                    <div class="stat-footer">
                        <div class="stat-compare">vs mois dernier</div>
                        <a href="#" style="color: var(--secondary); font-size: 12px; font-weight: 600;">Voir détails →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon success">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="fas fa-arrow-up"></i>
                            +15%
                        </div>
                    </div>
                    <div class="stat-label">Projets Actifs</div>
                    <div class="stat-value"><?= ($stats['projets_actifs'] ?? 0) ?> / <?= $stats['projets'] ?? 0 ?></div>
                    <div class="stat-footer">
                        <div class="stat-compare">Projets en cours</div>
                        <a href="#" style="color: var(--success); font-size: 12px; font-weight: 600;">Voir détails →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon warning">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="fas fa-arrow-up"></i>
                            +25%
                        </div>
                    </div>
                    <div class="stat-label">Revenus Total</div>
                    <div class="stat-value"><?= number_format(($stats['revenus'] ?? 0) / 1000000, 1) ?>M</div>
                    <div class="stat-footer">
                        <div class="stat-compare">FCFA ce mois</div>
                        <a href="#" style="color: var(--warning); font-size: 12px; font-weight: 600;">Voir détails →</a>
                    </div>
                </div>
            </div>

            <!-- Projets Récents -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-project-diagram"></i>
                        Projets Récents
                    </h2>
                    <div class="card-actions">
                        <button class="icon-btn">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button class="icon-btn">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($projetsRecents)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Projet</th>
                                    <th>Client</th>
                                    <th>Budget</th>
                                    <th>Statut</th>
                                    <th>Progression</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projetsRecents as $projet): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--gray-900);"><?= htmlspecialchars($projet['titre']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($projet['entreprise'] ?? $projet['client_nom']) ?></td>
                                    <td><?= number_format($projet['budget'], 0, ',', ' ') ?> FCFA</td>
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
                                                <div style="width: <?= $projet['progression'] ?? 0 ?>%; height: 100%; background: var(--primary); transition: width 0.3s;"></div>
                                            </div>
                                            <span style="font-size: 12px; font-weight: 600; color: var(--gray-700);"><?= $projet['progression'] ?? 0 ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="icon-btn" style="width: 32px; height: 32px;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                            <i class="fas fa-project-diagram" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>Aucun projet récent</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activités Récentes -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-history"></i>
                        Activités Récentes
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($activitesRecentes)): ?>
                        <?php foreach ($activitesRecentes as $activite): ?>
                            <div style="display: flex; gap: 16px; padding: 16px; border-bottom: 1px solid var(--gray-100); transition: var(--transition);" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='transparent'">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fas fa-circle-notch"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: var(--gray-900); margin-bottom: 4px;"><?= htmlspecialchars($activite['action']) ?></div>
                                    <div style="font-size: 14px; color: var(--gray-600); margin-bottom: 4px;"><?= htmlspecialchars($activite['description'] ?? '') ?></div>
                                    <div style="font-size: 12px; color: var(--gray-500);">
                                        <i class="fas fa-clock"></i>
                                        <?= date('d/m/Y à H:i', strtotime($activite['date_action'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                            <p>Aucune activité récente</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Dropdown Functions
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const isVisible = dropdown.style.display === 'block';
            
            // Fermer tous les dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
            
            // Ouvrir/fermer le dropdown sélectionné
            if (!isVisible) {
                dropdown.style.display = 'block';
            }
        }

        // Search Functionality
        function handleSearch(event) {
            const searchTerm = event.target.value.trim();
            const resultsContainer = document.getElementById('searchResults');
            
            if (searchTerm.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }
            
            // Simulation de recherche (remplacer par appel AJAX en production)
            const results = [
                { type: 'projet', title: 'Site Web E-commerce', description: 'Projet de développement' },
                { type: 'client', title: 'ABC Corporation', description: 'Entreprise de services' },
                { type: 'utilisateur', title: 'Jean Dupont', description: 'Chef de projet' }
            ];
            
            displaySearchResults(results);
        }
        
        function displaySearchResults(results) {
            const resultsContainer = document.getElementById('searchResults');
            resultsContainer.innerHTML = '';
            
            if (results.length === 0) {
                resultsContainer.innerHTML = '<div class="search-result-item">Aucun résultat trouvé</div>';
            } else {
                results.forEach(result => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    item.innerHTML = `
                        <div class="search-result-type">${result.type}</div>
                        <div style="font-weight: 600;">${result.title}</div>
                        <div style="font-size: 12px; color: var(--gray-600);">${result.description}</div>
                    `;
                    resultsContainer.appendChild(item);
                });
            }
            
            resultsContainer.style.display = 'block';
        }

        // Fermer les modals et dropdowns en cliquant à l'extérieur
        window.onclick = function(event) {
            // Fermer les modals
            document.querySelectorAll('.modal').forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
            
            // Fermer les dropdowns
            if (!event.target.matches('.icon-btn') && !event.target.closest('.dropdown-content')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.style.display = 'none';
                });
            }
            
            // Fermer les résultats de recherche
            if (!event.target.matches('#searchInput') && !event.target.closest('.search-results')) {
                document.getElementById('searchResults').style.display = 'none';
            }
        }

        // Fermer avec Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.style.display = 'none';
                });
                document.getElementById('searchResults').style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Animation au scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 0) {
                header.style.boxShadow = 'var(--shadow-md)';
            } else {
                header.style.boxShadow = 'var(--shadow)';
            }
        });

        // Animations des cartes au chargement
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

        // Mise à jour temps réel des notifications (simulation)
        setInterval(function() {
            const notifBadge = document.querySelector('.icon-btn .badge');
            if (notifBadge && Math.random() > 0.9) {
                const currentCount = parseInt(notifBadge.textContent);
                notifBadge.textContent = currentCount + 1;
                notifBadge.style.animation = 'pulse 0.5s ease';
                setTimeout(() => {
                    notifBadge.style.animation = '';
                }, 500);
            }
        }, 30000);
    </script>

    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
    </style>
</body>
</html>