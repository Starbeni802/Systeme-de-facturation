<?php
/**
 * Gestion des comptes utilisateurs
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

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'supprimer' && isset($_POST['id'])) {
        $resultat = supprimer_utilisateur($_POST['id']);
        if ($resultat['success']) {
            $message = $resultat['message'];
        } else {
            $erreur = $resultat['message'];
        }
    } elseif ($_POST['action'] === 'desactiver' && isset($_POST['id'])) {
        $resultat = changer_statut($_POST['id'], 'inactif');
        if ($resultat['success']) {
            $message = 'Utilisateur désactivé avec succès';
        } else {
            $erreur = $resultat['message'];
        }
    } elseif ($_POST['action'] === 'activer' && isset($_POST['id'])) {
        $resultat = changer_statut($_POST['id'], 'actif');
        if ($resultat['success']) {
            $message = 'Utilisateur activé avec succès';
        } else {
            $erreur = $resultat['message'];
        }
    }
}

// Récupérer la liste des utilisateurs
$utilisateurs = lire_utilisateurs();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Comptes - Système de Facturation</title>
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
                    <a href="gestion-comptes.php" class="active"><i class="fas fa-users me-2"></i> Comptes</a>
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
                    <h2><i class="fas fa-users-cog me-2"></i>Gestion des Comptes</h2>
                    <a href="ajouter-compte.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouveau Compte
                    </a>
                </div>
                
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <!-- Tableau des utilisateurs -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Liste des utilisateurs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Identifiant</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utilisateurs as $utilisateur): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($utilisateur['id']) ?></td>
                                            <td><strong><?= htmlspecialchars($utilisateur['identifiant']) ?></strong></td>
                                            <td><?= htmlspecialchars($utilisateur['nom']) ?></td>
                                            <td><?= htmlspecialchars($utilisateur['prenom']) ?></td>
                                            <td>
                                                <?php
                                                $badge_class = [
                                                    'admin' => 'danger',
                                                    'manager' => 'warning',
                                                    'caissier' => 'info'
                                                ];
                                                $role = $utilisateur['role'];
                                                $class = $badge_class[$role] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $class ?>"><?= ucfirst($role) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $statut = $utilisateur['statut'] ?? 'actif';
                                                $statut_class = $statut === 'actif' ? 'success' : 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $statut_class ?>"><?= ucfirst($statut) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($utilisateur['date_creation']) ?></td>
                                            <td>
                                                <?php if ($utilisateur['id'] != $_SESSION['utilisateur']['id']): ?>
                                                    <!-- Bouton changer statut -->
                                                    <?php if ($statut === 'actif'): ?>
                                                        <button class="btn btn-sm btn-warning" 
                                                                onclick="confirmerDesactivation(<?= $utilisateur['id'] ?>, '<?= htmlspecialchars($utilisateur['identifiant']) ?>')"
                                                                title="Désactiver">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-success" 
                                                                onclick="confirmerActivation(<?= $utilisateur['id'] ?>, '<?= htmlspecialchars($utilisateur['identifiant']) ?>')"
                                                                title="Activer">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="confirmerSuppression(<?= $utilisateur['id'] ?>, '<?= htmlspecialchars($utilisateur['identifiant']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fas fa-minus"></i></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Légende des rôles -->
                <div class="mt-4">
                    <h5>Rôles disponibles :</h5>
                    <div class="d-flex gap-3">
                        <span class="badge bg-danger">Admin</span> - Accès complet
                        <span class="badge bg-warning text-dark">Manager</span> - Gestion + Rapports
                        <span class="badge bg-info">Caissier</span> - Facturation uniquement
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation suppression -->
    <div class="modal fade" id="modalSuppression" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le compte <strong id="nomUtilisateur"></strong> ?</p>
                    <p class="text-danger"><small>Cette action est irréversible.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" id="idUtilisateur">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation désactivation -->
    <div class="modal fade" id="modalDesactivation" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Désactiver le compte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir désactiver le compte <strong id="nomUtilisateurDesact"></strong> ?</p>
                    <p><small>L'utilisateur ne pourra plus se connecter.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="desactiver">
                        <input type="hidden" name="id" id="idUtilisateurDesact">
                        <button type="submit" class="btn btn-warning">Désactiver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation activation -->
    <div class="modal fade" id="modalActivation" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Activer le compte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir activer le compte <strong id="nomUtilisateurAct"></strong> ?</p>
                    <p><small>L'utilisateur pourra à nouveau se connecter.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="activer">
                        <input type="hidden" name="id" id="idUtilisateurAct">
                        <button type="submit" class="btn btn-success">Activer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmerSuppression(id, identifiant) {
            document.getElementById('idUtilisateur').value = id;
            document.getElementById('nomUtilisateur').textContent = identifiant;
            var modal = new bootstrap.Modal(document.getElementById('modalSuppression'));
            modal.show();
        }
        
        function confirmerDesactivation(id, identifiant) {
            document.getElementById('idUtilisateurDesact').value = id;
            document.getElementById('nomUtilisateurDesact').textContent = identifiant;
            var modal = new bootstrap.Modal(document.getElementById('modalDesactivation'));
            modal.show();
        }
        
        function confirmerActivation(id, identifiant) {
            document.getElementById('idUtilisateurAct').value = id;
            document.getElementById('nomUtilisateurAct').textContent = identifiant;
            var modal = new bootstrap.Modal(document.getElementById('modalActivation'));
            modal.show();
        }
    </script>
</body>
</html>