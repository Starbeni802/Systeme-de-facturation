<?php
/**
 * Page d'accueil
 * Système de facturation
 */

// Démarrer la session
session_start();

// Inclure les fonctions d'authentification
require_once 'includes/fonctions-auth.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    // Rediriger vers la page de connexion
    header('Location: auth/login.php');
    exit;
}

$utilisateur = get_utilisateur_connecte();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Facturation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 0; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .user-info { position: absolute; top: 20px; right: 20px; }
    </style>
</head>
<body>
    <!-- Informations utilisateur connecté -->
    <div class="user-info">
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user me-2"></i>
                <?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?>
                <span class="badge bg-<?= $utilisateur['role'] === 'admin' ? 'danger' : ($utilisateur['role'] === 'manager' ? 'warning' : 'info') ?>">
                    <?= ucfirst($utilisateur['role']) ?>
                </span>
            </button>
            <ul class="dropdown-menu">
                <li><span class="dropdown-item-text text-muted">
                    <i class="fas fa-id-badge me-2"></i><?= htmlspecialchars($utilisateur['identifiant']) ?>
                </span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="auth/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </a></li>
            </ul>
        </div>
    </div>

    <div class="hero text-center">
        <div class="container">
            <h1 class="display-4">🧾 Système de Facturation</h1>
            <p class="lead">Gestion des produits et factures en PHP procédural</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <!-- Gestion des Produits -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-header bg-primary text-white">
                        <h5>📦 Produits</h5>
                    </div>
                    <div class="card-body">
                        <p>Gérer les produits</p>
                        <a href="modules/produits/produits/ajouter-produit.php" class="btn btn-primary mb-2">➕ Ajouter Produit</a>
                        <a href="modules/produits/liste.php" class="btn btn-outline-primary">📋 Liste Produits</a>
                    </div>
                </div>
            </div>

            <!-- Nouvelle Facture -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-header bg-success text-white">
                        <h5>🧾 Facturation</h5>
                    </div>
                    <div class="card-body">
                        <p>Créer une nouvelle facture</p>
                        <a href="modules/produits/facturation/nouvelle-facture.php" class="btn btn-success">➕ Nouvelle Facture</a>
                    </div>
                </div>
            </div>

            <!-- Rapports -->
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-header bg-info text-white">
                        <h5>📊 Rapports</h5>
                    </div>
                    <div class="card-body">
                        <p>Voir les rapports</p>
                        <a href="rapport/rapport-journalier.php" class="btn btn-info mb-2">📅 Journalier</a>
                        <a href="rapport/rapport-mensuel.php" class="btn btn-outline-info">📆 Mensuel</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Administration (visible uniquement pour admin et manager) -->
            <?php 
            $role = $utilisateur['role'];
            $allowed_roles = ['admin', 'manager'];
            $can_access = in_array($role, $allowed_roles);
            ?>
            <?php if ($can_access): ?>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-header bg-warning text-white">
                        <h5>👥 Gestion des Employés</h5>
                    </div>
                    <div class="card-body">
                        <p>Gérer les employés de la plateforme</p>
                        <a href="modules/admin/gestion-compte.php" class="btn btn-warning mb-2">👤 Gestion Comptes</a>
                        <a href="modules/admin/ajouter-compte.php" class="btn btn-outline-warning">➕ Ajouter Employé</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mon compte -->
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-header bg-dark text-white">
                        <h5>👤 Mon Compte</h5>
                    </div>
                    <div class="card-body">
                        <p>Informations du compte</p>
                        <div class="text-start">
                            <p class="mb-1"><strong>Identifiant:</strong> <?= htmlspecialchars($utilisateur['identifiant']) ?></p>
                            <p class="mb-1"><strong>Nom:</strong> <?= htmlspecialchars($utilisateur['nom']) ?></p>
                            <p class="mb-1"><strong>Prénom:</strong> <?= htmlspecialchars($utilisateur['prenom']) ?></p>
                            <p class="mb-0"><strong>Rôle:</strong> 
                                <span class="badge bg-<?= $utilisateur['role'] === 'admin' ? 'danger' : ($utilisateur['role'] === 'manager' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($utilisateur['role']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light text-center py-3 mt-5">
        <p class="text-muted mb-0">Système de Facturation - PHP Procédural - JSON</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>