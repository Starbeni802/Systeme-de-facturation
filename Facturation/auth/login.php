<?php
/**
 * Page de connexion
 * Système de facturation
 */

session_start();

// Si déjà connecté, rediriger vers la page d'accueil
if (isset($_SESSION['utilisateur'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';
$erreur = '';

// Charger les utilisateurs pour la liste de suggestions
$utilisateurs_liste = [];
if (file_exists('../data/utilisateurs.json')) {
    $data = json_decode(file_get_contents('../data/utilisateurs.json'), true);
    if ($data) {
        foreach ($data as $u) {
            $utilisateurs_liste[] = $u['identifiant'];
        }
    }
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/fonctions-auth.php';
    
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($identifiant) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs';
    } else {
        $utilisateur = verifier_connexion($identifiant, $mot_de_passe);
        
        if ($utilisateur) {
            // Créer la session
            connecter_utilisateur($utilisateur);
            
            // Rediriger vers la page d'accueil
            header('Location: ../index.php');
            exit;
        } else {
            $erreur = 'Identifiant ou mot de passe incorrect';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de Facturation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <h3 class="mb-0">🔐 Connexion</h3>
                        <p class="mb-0 mt-2">Système de Facturation</p>
                    </div>
                    <div class="login-body">
                        <?php if ($erreur): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="identifiant" class="form-label">Identifiant</label>
                                <input type="text" class="form-control" id="identifiant" name="identifiant" 
                                        placeholder="Sélectionnez ou tapez votre identifiant" required
                                        list="identifiants-list"
                                        value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>">
                                <datalist id="identifiants-list">
                                    <?php foreach ($utilisateurs_liste as $ident): ?>
                                    <option value="<?= htmlspecialchars($ident) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                                        placeholder="Entrez votre mot de passe" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-login btn-lg">
                                    Se connecter
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                Accès réservé aux utilisateurs autorisés
                            </small>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted d-block mb-2"><strong>Comptes de test :</strong></small>
                            <ul class="list-unstyled mb-0 small">
                                <li>🔑 admin / password</li>
                                <li>🔑 manager / password</li>
                                <li>🔑 caissier / password</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>