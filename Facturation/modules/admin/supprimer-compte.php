<?php
/**
 * Supprimer un compte utilisateur
 * Système de facturation - Admin
 * Note: La suppression se fait principalement via gestion-comptes.php
 * Ce fichier peut être utilisé pour une suppression directe par ID
 */

// Inclure la vérification de session
require_once __DIR__ . '/../../auth/session.php';

// Vérifier que l'utilisateur est admin ou manager
verifier_manager();
verifier_admin();

require_once __DIR__ . '/../../includes/fonctions-auth.php';

$message = '';
$erreur = '';

// Vérifier si un ID est passé en paramètre
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Empêcher l'auto-suppression
    if ($id === $_SESSION['utilisateur']['id']) {
        $erreur = 'Vous ne pouvez pas supprimer votre propre compte';
    } else {
        $resultat = supprimer_utilisateur($id);
        
        if ($resultat['success']) {
            $message = $resultat['message'];
            header('Location: gestion-comptes.php?success=1');
            exit;
        } else {
            $erreur = $resultat['message'];
        }
    }
} else {
    // Pas d'ID fourni, rediriger vers la gestion des comptes
    header('Location: gestion-comptes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Compte - Système de Facturation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <a href="gestion-comptes.php" class="btn btn-primary">Retour à la gestion des comptes</a>
            </div>
        </div>
    </div>
</body>
</html>