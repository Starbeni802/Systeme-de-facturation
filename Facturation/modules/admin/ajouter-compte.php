<?php
/**
 * Ajouter un nouveau compte utilisateur
 * Système de facturation - Admin
 */

// Inclure la vérification de session
require_once __DIR__ . '/../../auth/session.php';

// Vérifier que l'utilisateur est admin ou manager
verifier_manager();
verifier_admin();

require_once __DIR__ . '/../../includes/fonctions-auth.php';

$message = '';
$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $role = $_POST['role'] ?? '';
    
    // Validation
    if (empty($identifiant) || empty($mot_de_passe) || empty($nom) || empty($prenom) || empty($role)) {
        $erreur = 'Veuillez remplir tous les champs';
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif ($mot_de_passe !== $confirmer_mdp) {
        $erreur = 'Les mots de passe ne correspondent pas';
    } elseif (!in_array($role, ['admin', 'manager', 'caissier'])) {
        $erreur = 'Rôle invalide';
    } else {
        // Créer l'utilisateur
        $resultat = creer_utilisateur($identifiant, $mot_de_passe, $nom, $prenom, $role);
        
        if ($resultat['success']) {
            $message = $resultat['message'];
            // Rediriger vers la gestion des comptes
            header('Location: gestion-comptes.php?success=1');
            exit;
        } else {
            $erreur = $resultat['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Compte - Système de Facturation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.2);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h4 class="text-white text-center mb-4">🧾 Facturation</h4>
                <nav>
                    <a href="../../index.php"><i class="fas fa-home me-2"></i> Accueil</a>
                    <a href="../admin/gestion-comptes.php"><i class="fas fa-users me-2"></i> Comptes</a>
                    <a href="../../modules/produits/produits/liste.php"><i class="fas fa-box me-2"></i> Produits</a>
                    <a href="../../modules/produits/facturation/nouvelle-facture.php"><i class="fas fa-file-invoice me-2"></i> Nouvelle Facture</a>
                    <a href="../../rapport/rapport-journalier.php"><i class="fas fa-chart-line me-2"></i> Rapports</a>
                </nav>
                <div class="mt-5 pt-5">
                    <div class="d-flex align-items-center text-white mb-3">
                        <div class="user-avatar me-2">
                            <?= strtoupper(substr($_SESSION['utilisateur']['prenom'], 0, 1)) ?>
                        </div>
                        <div>
                            <small><?= htmlspecialchars($_SESSION['utilisateur']['prenom']) ?></small><br>
                            <small><?= htmlspecialchars(ucfirst($_SESSION['utilisateur']['role'])) ?></small>
                        </div>
                    </div>
                    <a href="../../auth/logout.php" class="btn btn-outline-light btn-sm w-100">
                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-plus me-2"></i>Ajouter un Nouveau Compte</h2>
                    <a href="../admin/gestion-comptes.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <!-- Formulaire -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="identifiant" class="form-label">Identifiant *</label>
                                    <input type="text" class="form-control" id="identifiant" name="identifiant" 
                                            placeholder="Nom d'utilisateur" required
                                            value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Rôle *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Sélectionner un rôle</option>
                                        <option value="caissier" <?= ($_POST['role'] ?? '') === 'caissier' ? 'selected' : '' ?>>Caissier</option>
                                        <option value="manager" <?= ($_POST['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                                        <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           placeholder="Nom de famille" required
                                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" 
                                           placeholder="Prénom" required
                                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mot_de_passe" class="form-label">Mot de passe *</label>
                                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                                           placeholder="Minimum 6 caractères" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirmer_mdp" class="form-label">Confirmer le mot de passe *</label>
                                    <input type="password" class="form-control" id="confirmer_mdp" name="confirmer_mdp" 
                                            placeholder="Confirmer le mot de passe" required>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Créer le compte
                                </button>
                                <a href="../admin/gestion-comptes.php" class="btn btn-secondary ms-2">
                                    Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informations sur les rôles -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations sur les rôles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-danger"><i class="fas fa-crown me-2"></i>Administrateur</h6>
                                <ul class="list-unstyled small">
                                    <li>✓ Gestion complète des utilisateurs</li>
                                    <li>✓ Accès à toutes les fonctionnalités</li>
                                    <li>✓ Création et suppression de comptes</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-warning"><i class="fas fa-user-tie me-2"></i>Manager</h6>
                                <ul class="list-unstyled small">
                                    <li>✓ Gestion des produits</li>
                                    <li>✓ Création de factures</li>
                                    <li>✓ Consultation des rapports</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-info"><i class="fas fa-cash-register me-2"></i>Caissier</h6>
                                <ul class="list-unstyled small">
                                    <li>✓ Création de factures</li>
                                    <li>✓ Consultation des produits</li>
                                    <li>✗ Pas de gestion des utilisateurs</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>