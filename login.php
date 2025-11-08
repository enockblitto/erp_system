<?php
session_start();

// ============================================
// CONFIGURATION
// ============================================

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'erp_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration Google OAuth - REMPLACEZ PAR VOS VRAIES CLÉS
define('GOOGLE_CLIENT_ID', '896550687348-iqc3gm5tljuue4u9to6s0qf2r3f7nfeu.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'GOCSPX-votre_client_secret');
define('GOOGLE_REDIRECT_URI', 'http://localhost/erp-system/login.php');

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// ============================================
// FONCTIONS UTILITAIRES
// ============================================

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// ============================================
// GESTION DE L'AUTHENTIFICATION LOCALE
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CONNEXION
    if ($action === 'login') {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitize($_POST['role'] ?? '');
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password) || empty($role)) {
            jsonResponse(false, 'Tous les champs sont obligatoires');
        }
        
        try {
            // Vérifier selon le rôle
            if ($role === 'admin' || $role === 'employe') {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ? AND statut = 'actif'");
                $stmt->execute([$email, $role]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    jsonResponse(false, 'Identifiants incorrects ou compte inactif');
                }
                
                // Vérification du mot de passe
                if (!password_verify($password, $user['mot_de_passe'])) {
                    jsonResponse(false, 'Mot de passe incorrect');
                }
                
                // Créer la session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['role'] = $role;
                $_SESSION['login_time'] = time();
                
            } else if ($role === 'client') {
                $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ? AND statut = 'actif'");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    jsonResponse(false, 'Identifiants incorrects ou compte inactif');
                }
                
                // Vérifier le mot de passe (si le champ existe)
                if (isset($user['mot_de_passe']) && !empty($user['mot_de_passe'])) {
                    if (!password_verify($password, $user['mot_de_passe'])) {
                        jsonResponse(false, 'Mot de passe incorrect');
                    }
                } else {
                    // Si pas de mot de passe enregistré pour ce client
                    jsonResponse(false, 'Aucun mot de passe défini pour ce compte client. Veuillez contacter l\'administrateur.');
                }
                
                // Créer la session
                $_SESSION['user_id'] = $user['id_client'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = '';
                $_SESSION['role'] = 'client';
                $_SESSION['login_time'] = time();
                
            } else {
                jsonResponse(false, 'Rôle invalide');
            }
            
            // Cookie "Se souvenir de moi"
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('erp_remember', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }
            
            // Déterminer l'URL de redirection
            $redirectUrl = match($role) {
                'admin' => 'admin-dashboard.php',
                'employe' => 'employee-dashboard.php',
                'client' => 'client-dashboard.php',
                default => 'index.php'
            };
            
            jsonResponse(true, 'Connexion réussie', [
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'email' => $_SESSION['email'],
                    'nom' => $_SESSION['nom'],
                    'prenom' => $_SESSION['prenom'],
                    'role' => $_SESSION['role']
                ],
                'redirect' => $redirectUrl
            ]);
            
        } catch (PDOException $e) {
            jsonResponse(false, 'Erreur serveur : ' . $e->getMessage());
        }
    }
    
    // INSCRIPTION
    else if ($action === 'register') {
        $role = sanitize($_POST['role'] ?? '');
        $prenom = sanitize($_POST['prenom'] ?? '');
        $nom = sanitize($_POST['nom'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telephone = sanitize($_POST['telephone'] ?? '');
        $password = $_POST['password'] ?? '';
        $entreprise = sanitize($_POST['entreprise'] ?? '');
        
        // Validation
        if (empty($role) || empty($prenom) || empty($nom) || empty($email) || empty($password)) {
            jsonResponse(false, 'Tous les champs obligatoires doivent être remplis');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(false, 'Email invalide');
        }
        
        if (strlen($password) < 6) {
            jsonResponse(false, 'Le mot de passe doit contenir au moins 6 caractères');
        }
        
        // Vérifier si l'email existe déjà
        try {
            // Vérifier dans les deux tables
            $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ? UNION SELECT email FROM clients WHERE email = ?");
            $stmt->execute([$email, $email]);
            if ($stmt->fetch()) {
                jsonResponse(false, 'Cet email est déjà utilisé');
            }
            
            // Créer le compte
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($role === 'employe') {
                $stmt = $pdo->prepare("
                    INSERT INTO users (nom, prenom, email, mot_de_passe, role, telephone, date_embauche, statut)
                    VALUES (?, ?, ?, ?, 'employe', ?, CURDATE(), 'actif')
                ");
                $stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone]);
                $userId = $pdo->lastInsertId();
                
                // CRÉER LA SESSION IMMÉDIATEMENT APRÈS L'INSCRIPTION
                $_SESSION['user_id'] = $userId;
                $_SESSION['email'] = $email;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['role'] = 'employe';
                $_SESSION['login_time'] = time();
                
                jsonResponse(true, 'Compte employé créé avec succès !', [
                    'user' => [
                        'id' => $userId,
                        'email' => $email,
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'role' => 'employe'
                    ],
                    'redirect' => 'employee-dashboard.php'
                ]);
                
            } else if ($role === 'client') {
                // Ajouter le champ mot_de_passe à la table clients si nécessaire
                $stmt = $pdo->query("SHOW COLUMNS FROM clients LIKE 'mot_de_passe'");
                $columnExists = $stmt->fetch();
                
                if (!$columnExists) {
                    // Ajouter la colonne mot_de_passe
                    $pdo->exec("ALTER TABLE clients ADD COLUMN mot_de_passe VARCHAR(255) DEFAULT NULL AFTER email");
                }
                
                $nomComplet = $prenom . ' ' . $nom;
                $stmt = $pdo->prepare("
                    INSERT INTO clients (nom, email, mot_de_passe, telephone, entreprise, statut)
                    VALUES (?, ?, ?, ?, ?, 'actif')
                ");
                $stmt->execute([$nomComplet, $email, $hashedPassword, $telephone, $entreprise]);
                $userId = $pdo->lastInsertId();
                
                // CRÉER LA SESSION IMMÉDIATEMENT APRÈS L'INSCRIPTION
                $_SESSION['user_id'] = $userId;
                $_SESSION['email'] = $email;
                $_SESSION['nom'] = $nomComplet;
                $_SESSION['prenom'] = '';
                $_SESSION['role'] = 'client';
                $_SESSION['login_time'] = time();
                
                jsonResponse(true, 'Compte client créé avec succès !', [
                    'user' => [
                        'id' => $userId,
                        'email' => $email,
                        'nom' => $nomComplet,
                        'prenom' => '',
                        'role' => 'client'
                    ],
                    'redirect' => 'client-dashboard.php'
                ]);
                
            } else if ($role === 'admin') {
                // Vérifier si un admin existe déjà
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    jsonResponse(false, 'Un compte administrateur existe déjà. Contactez l\'administrateur actuel.');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (nom, prenom, email, mot_de_passe, role, telephone, date_embauche, statut)
                    VALUES (?, ?, ?, ?, 'admin', ?, CURDATE(), 'actif')
                ");
                $stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone]);
                $userId = $pdo->lastInsertId();
                
                // CRÉER LA SESSION IMMÉDIATEMENT APRÈS L'INSCRIPTION
                $_SESSION['user_id'] = $userId;
                $_SESSION['email'] = $email;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['role'] = 'admin';
                $_SESSION['login_time'] = time();
                
                jsonResponse(true, 'Compte administrateur créé avec succès !', [
                    'user' => [
                        'id' => $userId,
                        'email' => $email,
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'role' => 'admin'
                    ],
                    'redirect' => 'admin-dashboard.php'
                ]);
                
            } else {
                jsonResponse(false, 'Rôle invalide pour l\'inscription');
            }
            
        } catch (PDOException $e) {
            jsonResponse(false, 'Erreur lors de la création du compte : ' . $e->getMessage());
        }
    }
    
    // MOT DE PASSE OUBLIÉ
    else if ($action === 'forgot_password') {
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($email)) {
            jsonResponse(false, 'Email requis');
        }
        
        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ? UNION SELECT email FROM clients WHERE email = ?");
        $stmt->execute([$email, $email]);
        
        if (!$stmt->fetch()) {
            jsonResponse(false, 'Aucun compte associé à cet email');
        }
        
        // Générer un token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Envoyer l'email (simulé ici)
        // Pour l'activer, configurez mail() ou utilisez PHPMailer
        // $resetLink = "http://localhost/erp-system/reset-password.php?token=" . $token;
        // mail($email, "Réinitialisation mot de passe", "Votre lien : " . $resetLink);
        
        jsonResponse(true, 'Un email de réinitialisation a été envoyé à ' . $email . ' (fonctionnalité simulée)');
    }
}

// ============================================
// AUTHENTIFICATION GOOGLE OAUTH
// ============================================

// Redirection vers Google
if (isset($_GET['google_login'])) {
    // Vérifier si les identifiants Google sont configurés
    if (GOOGLE_CLIENT_ID === 'VOTRE_CLIENT_ID.apps.googleusercontent.com' || 
        GOOGLE_CLIENT_SECRET === 'VOTRE_CLIENT_SECRET') {
        $_SESSION['error'] = 'Google OAuth n\'est pas configuré. Veuillez configurer vos identifiants Google dans login.php';
        header('Location: login.php');
        exit;
    }
    
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    
    $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    header('Location: ' . $googleAuthUrl);
    exit;
}

// Callback Google OAuth
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $code = $_GET['code'];
    
    try {
        // Échanger le code contre un token
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $postData = [
            'code' => $code,
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Pour le dev local
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $_SESSION['error'] = 'Erreur cURL : ' . $error;
            header('Location: login.php');
            exit;
        }
        
        $tokenData = json_decode($response, true);
        
        if (isset($tokenData['error'])) {
            $_SESSION['error'] = 'Erreur Google OAuth : ' . ($tokenData['error_description'] ?? $tokenData['error']);
            header('Location: login.php');
            exit;
        }
        
        if (isset($tokenData['access_token'])) {
            // Récupérer les infos utilisateur
            $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
            $ch = curl_init($userInfoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tokenData['access_token']
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $userInfoResponse = curl_exec($ch);
            curl_close($ch);
            
            $userInfo = json_decode($userInfoResponse, true);
            
            if (isset($userInfo['email'])) {
                // Vérifier si l'utilisateur existe
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND statut = 'actif'");
                $stmt->execute([$userInfo['email']]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Créer la session
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['google_login'] = true;
                    
                    // Rediriger selon le rôle
                    $redirectUrl = match($user['role']) {
                        'admin' => 'admin-dashboard.php',
                        'employe' => 'employee-dashboard.php',
                        default => 'employee-dashboard.php'
                    };
                    
                    header('Location: ' . $redirectUrl);
                    exit;
                } else {
                    // Créer un nouveau compte employé automatiquement
                    $prenom = $userInfo['given_name'] ?? '';
                    $nom = $userInfo['family_name'] ?? '';
                    $picture = $userInfo['picture'] ?? '';
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO users (nom, prenom, email, mot_de_passe, role, date_embauche, statut, photo)
                        VALUES (?, ?, ?, '', 'employe', CURDATE(), 'actif', ?)
                    ");
                    $stmt->execute([$nom, $prenom, $userInfo['email'], $picture]);
                    
                    $userId = $pdo->lastInsertId();
                    
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $userInfo['email'];
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $_SESSION['role'] = 'employe';
                    $_SESSION['login_time'] = time();
                    $_SESSION['google_login'] = true;
                    
                    $_SESSION['success'] = 'Compte créé avec succès via Google !';
                    header('Location: employee-dashboard.php');
                    exit;
                }
            } else {
                $_SESSION['error'] = 'Impossible de récupérer les informations de votre compte Google';
                header('Location: login.php');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'obtention du token d\'accès Google';
            header('Location: login.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur lors de la connexion avec Google : ' . $e->getMessage();
        header('Location: login.php');
        exit;
    }
}

// Vérifier si déjà connecté
if (isset($_SESSION['user_id']) && !isset($_GET['logout'])) {
    $redirectUrl = match($_SESSION['role'] ?? '') {
        'admin' => 'admin-dashboard.php',
        'employe' => 'employee-dashboard.php',
        'client' => 'client-dashboard.php',
        default => 'index.php'
    };
    header('Location: ' . $redirectUrl);
    exit;
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('erp_remember', '', time() - 3600, '/');
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ERP System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0d9488;
            --primary-light: #14b8a6;
            --primary-dark: #0f766e;
            --secondary: #f97316;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #eab308;
            --dark: #1e293b;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius: 8px;
            --radius-lg: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1200px;
            width: 100%;
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-left {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 60px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .branding {
            position: relative;
            z-index: 2;
        }

        .branding h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .branding p {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 48px;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            font-size: 16px;
            opacity: 0.95;
            transition: var(--transition);
            padding: 8px 0;
        }

        .features-list li:hover {
            transform: translateX(8px);
            opacity: 1;
        }

        .features-list li::before {
            content: '✓';
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.2);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .security-badge {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            z-index: 2;
        }

        .login-right {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--white);
        }

        .form-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .form-header h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .form-header p {
            color: var(--gray-600);
            font-size: 16px;
        }

        .alert {
            padding: 16px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
            border-left: 4px solid transparent;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.1);
            color: var(--success);
            border-left-color: var(--success);
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
            border-left-color: var(--danger);
        }

        .alert-warning {
            background: rgba(234, 179, 8, 0.1);
            color: var(--warning);
            border-left-color: var(--warning);
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--gray-700);
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .required {
            color: var(--danger);
        }

        .input-wrapper {
            position: relative;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 15px;
            transition: var(--transition);
            background: var(--white);
            font-family: inherit;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1);
        }

        input.error,
        select.error {
            border-color: var(--danger);
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-600);
            cursor: pointer;
            font-size: 18px;
            transition: var(--transition);
            background: none;
            border: none;
            padding: 4px;
        }

        .input-icon:hover {
            color: var(--primary);
        }

        .error-message {
            color: var(--danger);
            font-size: 13px;
            margin-top: 6px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--gray-700);
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-password:hover {
            color: var(--primary-dark);
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: var(--radius);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-google {
            background: var(--white);
            color: var(--gray-700);
            border: 2px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
        }

        .btn-google:hover {
            border-color: var(--gray-300);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--primary);
            border: 2px solid var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }

        .role-selector {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .role-option {
            padding: 20px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            background: var(--white);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }

        .role-option:hover {
            border-color: var(--primary);
            background: rgba(13, 148, 136, 0.02);
        }

        .role-option.selected {
            border-color: var(--primary);
            background: rgba(13, 148, 136, 0.1);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .role-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .role-option.admin .role-icon {
            color: var(--primary);
        }

        .role-option.employe .role-icon {
            color: var(--secondary);
        }

        .role-option.client .role-icon {
            color: var(--success);
        }

        .role-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 16px;
            margin-bottom: 4px;
        }

        .role-desc {
            color: var(--gray-600);
            font-size: 12px;
        }

        .auth-switch {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
        }

        .auth-switch p {
            color: var(--gray-600);
            font-size: 14px;
            margin-bottom: 16px;
        }

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
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray-600);
        }

        .close-modal:hover {
            color: var(--danger);
        }

        .modal-body {
            padding: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 968px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .login-left {
                display: none;
            }

            .login-right {
                padding: 40px 30px;
            }

            .role-selector {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Modal d'inscription -->
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Créer un compte</h3>
                <button class="close-modal" onclick="closeRegisterModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="registerAlertContainer"></div>
                <form id="registerForm">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="registerRole">
                            <i class="fas fa-user-tag"></i>
                            Type de compte <span class="required">*</span>
                        </label>
                        <select id="registerRole" name="role" required>
                            <option value="">Sélectionnez un type de compte</option>
                            <option value="admin">Administrateur (si aucun admin n'existe)</option>
                            <option value="employe">Employé</option>
                            <option value="client">Client</option>
                        </select>
                        <div class="error-message" id="registerRoleError">Veuillez sélectionner un type de compte</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="registerPrenom">
                                <i class="fas fa-user"></i>
                                Prénom <span class="required">*</span>
                            </label>
                            <input type="text" id="registerPrenom" name="prenom" placeholder="Votre prénom" required>
                            <div class="error-message" id="registerPrenomError">Le prénom est requis</div>
                        </div>

                        <div class="form-group">
                            <label for="registerNom">
                                <i class="fas fa-user"></i>
                                Nom <span class="required">*</span>
                            </label>
                            <input type="text" id="registerNom" name="nom" placeholder="Votre nom" required>
                            <div class="error-message" id="registerNomError">Le nom est requis</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="registerEmail">
                            <i class="fas fa-envelope"></i>
                            Adresse email <span class="required">*</span>
                        </label>
                        <input type="email" id="registerEmail" name="email" placeholder="votre@email.com" required>
                        <div class="error-message" id="registerEmailError">Veuillez saisir une adresse email valide</div>
                    </div>

                    <div class="form-group" id="entrepriseField" style="display: none;">
                        <label for="registerEntreprise">
                            <i class="fas fa-building"></i>
                            Entreprise
                        </label>
                        <input type="text" id="registerEntreprise" name="entreprise" placeholder="Nom de votre entreprise">
                    </div>

                    <div class="form-group">
                        <label for="registerTelephone">
                            <i class="fas fa-phone"></i>
                            Téléphone
                        </label>
                        <input type="tel" id="registerTelephone" name="telephone" placeholder="+225 XX XX XX XX">
                    </div>

                    <div class="form-group">
                        <label for="registerPassword">
                            <i class="fas fa-lock"></i>
                            Mot de passe <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="registerPassword" name="password" placeholder="Créez un mot de passe" required>
                            <button type="button" class="input-icon" onclick="toggleRegisterPassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="registerPasswordError">Le mot de passe doit contenir au moins 6 caractères</div>
                    </div>

                    <div class="form-group">
                        <label for="registerConfirmPassword">
                            <i class="fas fa-lock"></i>
                            Confirmer le mot de passe <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="registerConfirmPassword" placeholder="Confirmez votre mot de passe" required>
                            <button type="button" class="input-icon" onclick="toggleConfirmPassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="error-message" id="registerConfirmPasswordError">Les mots de passe ne correspondent pas</div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="registerBtn">
                        <i class="fas fa-user-plus"></i>
                        Créer mon compte
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <div class="branding">
                <h1>
                    <i class="fas fa-building"></i>
                    ERP System
                </h1>
                <p>Rejoignez notre plateforme de gestion d'entreprise et optimisez vos processus métiers.</p>
                
                <ul class="features-list">
                    <li>Gestion centralisée des activités</li>
                    <li>Interface adaptée à chaque métier</li>
                    <li>Tableaux de bord personnalisés</li>
                    <li>Collaboration en temps réel</li>
                    <li>Rapports détaillés par profil</li>
                </ul>
            </div>

            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Plateforme sécurisée</strong><br>
                    <span style="opacity: 0.8;">Inscription rapide et validation immédiate</span>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="form-header">
                <h2>Bienvenue</h2>
                <p>Connectez-vous à votre compte existant</p>
            </div>

            <div id="alertContainer">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
            </div>

            <!-- Connexion Google -->
            <a href="?google_login=1" class="btn btn-google">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Se connecter avec Google
            </a>

            <div style="text-align: center; margin: 20px 0; color: var(--gray-600); font-size: 14px;">
                <span style="background: var(--white); padding: 0 10px; position: relative; z-index: 1;">ou</span>
                <div style="border-top: 1px solid var(--gray-200); margin-top: -12px;"></div>
            </div>

            <form id="loginForm">
                <input type="hidden" name="action" value="login">
                
                <!-- Role Selection -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-user-tag"></i>
                        Sélectionnez votre profil <span class="required">*</span>
                    </label>
                    <div class="role-selector">
                        <div class="role-option admin" data-role="admin" onclick="selectRole('admin')">
                            <div class="role-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="role-name">Admin</div>
                            <div class="role-desc">Gestion complète</div>
                        </div>

                        <div class="role-option employe" data-role="employe" onclick="selectRole('employe')">
                            <div class="role-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="role-name">Employé</div>
                            <div class="role-desc">Projets assignés</div>
                        </div>

                        <div class="role-option client" data-role="client" onclick="selectRole('client')">
                            <div class="role-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="role-name">Client</div>
                            <div class="role-desc">Suivi projets</div>
                        </div>
                    </div>
                    <input type="hidden" name="role" id="selectedRole">
                    <div class="error-message" id="roleError">Veuillez sélectionner votre profil</div>
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Adresse email <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                    <div class="error-message" id="emailError">Veuillez saisir une adresse email valide</div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Mot de passe <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                        <button type="button" class="input-icon" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="passwordError">Le mot de passe est requis</div>
                </div>

                <!-- Options -->
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>
                    <a href="#" class="forgot-password" onclick="showForgotPassword(event)">
                        Mot de passe oublié ?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>

            <!-- Auth Switch -->
            <div class="auth-switch">
                <p>Vous n'avez pas de compte ?</p>
                <button class="btn btn-secondary" onclick="openRegisterModal()">
                    <i class="fas fa-user-plus"></i>
                    Créer un compte
                </button>
            </div>
        </div>
    </div>

    <script>
        let selectedRole = null;

        function selectRole(role) {
            selectedRole = role;
            document.getElementById('selectedRole').value = role;
            
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`.role-option[data-role="${role}"]`).classList.add('selected');
            
            document.getElementById('roleError').classList.remove('show');
            
            const emailInput = document.getElementById('email');
            switch(role) {
                case 'admin':
                    emailInput.placeholder = 'admin@erp-system.com';
                    break;
                case 'employe':
                    emailInput.placeholder = 'prenom.nom@entreprise.com';
                    break;
                case 'client':
                    emailInput.placeholder = 'contact@client.com';
                    break;
            }
            emailInput.focus();
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = passwordInput.parentNode.querySelector('.input-icon i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function toggleRegisterPassword() {
            const passwordInput = document.getElementById('registerPassword');
            const icon = passwordInput.parentNode.querySelector('.input-icon i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function toggleConfirmPassword() {
            const passwordInput = document.getElementById('registerConfirmPassword');
            const icon = passwordInput.parentNode.querySelector('.input-icon i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function showAlert(type, message, containerId = 'alertContainer') {
            const container = document.getElementById(containerId);
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = message;
            
            container.innerHTML = '';
            container.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.style.opacity = '0';
                    setTimeout(() => alertDiv.remove(), 300);
                }
            }, 5000);
        }

        function openRegisterModal() {
            document.getElementById('registerModal').style.display = 'block';
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').style.display = 'none';
        }

        document.getElementById('registerRole').addEventListener('change', function() {
            const entrepriseField = document.getElementById('entrepriseField');
            if (this.value === 'client') {
                entrepriseField.style.display = 'block';
            } else {
                entrepriseField.style.display = 'none';
            }
        });

        // Soumettre le formulaire de connexion
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!selectedRole) {
                document.getElementById('roleError').classList.add('show');
                showAlert('danger', '<i class="fas fa-exclamation-circle"></i> Veuillez sélectionner votre profil');
                return;
            }
            
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div> Connexion en cours...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', '<i class="fas fa-check-circle"></i> ' + result.message);
                    setTimeout(() => {
                        window.location.href = result.data.redirect;
                    }, 1000);
                } else {
                    showAlert('danger', '<i class="fas fa-exclamation-circle"></i> ' + result.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
                }
            } catch (error) {
                showAlert('danger', '<i class="fas fa-exclamation-circle"></i> Erreur de connexion au serveur');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
            }
        });

        // Soumettre le formulaire d'inscription
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('registerConfirmPassword').value;
            
            if (password !== confirmPassword) {
                document.getElementById('registerConfirmPasswordError').classList.add('show');
                showAlert('danger', '<i class="fas fa-exclamation-circle"></i> Les mots de passe ne correspondent pas', 'registerAlertContainer');
                return;
            }
            
            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div> Création du compte...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', '<i class="fas fa-check-circle"></i> ' + result.message, 'registerAlertContainer');
                    
                    // REDIRIGER VERS LE DASHBOARD APRÈS INSCRIPTION
                    if (result.data && result.data.redirect) {
                        setTimeout(() => {
                            window.location.href = result.data.redirect;
                        }, 1000);
                    } else {
                        // Fallback si pas de redirection définie
                        setTimeout(() => {
                            closeRegisterModal();
                            showAlert('success', '<i class="fas fa-check-circle"></i> ' + result.message);
                        }, 2000);
                    }
                } else {
                    showAlert('danger', '<i class="fas fa-exclamation-circle"></i> ' + result.message, 'registerAlertContainer');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-user-plus"></i> Créer mon compte';
                }
            } catch (error) {
                showAlert('danger', '<i class="fas fa-exclamation-circle"></i> Erreur de connexion au serveur', 'registerAlertContainer');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-user-plus"></i> Créer mon compte';
            }
        });

        async function showForgotPassword(event) {
            event.preventDefault();
            const email = document.getElementById('email').value.trim();

            if (!email) {
                showAlert('warning', '<i class="fas fa-exclamation-triangle"></i> Veuillez saisir votre adresse email');
                document.getElementById('email').focus();
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'forgot_password');
                formData.append('email', email);
                
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', '<i class="fas fa-check-circle"></i> ' + result.message);
                } else {
                    showAlert('danger', '<i class="fas fa-exclamation-circle"></i> ' + result.message);
                }
            } catch (error) {
                showAlert('danger', '<i class="fas fa-exclamation-circle"></i> Erreur de connexion au serveur');
            }
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('registerModal');
            if (event.target === modal) {
                closeRegisterModal();
            }
        });
    </script>
</body>
</html>