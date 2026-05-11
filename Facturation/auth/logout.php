<?php
/**
 * Déconnexion
 * Système de facturation
 */

session_start();

// Inclure les fonctions d'authentification
require_once __DIR__ . '/../includes/fonctions-auth.php';

// Détruire la session utilisateur
deconnecter_utilisateur();

// Détruire complètement la session
if (isset($_SESSION)) {
    session_unset();
    session_destroy();
}

// Rediriger vers la page de connexion
header('Location: login.php');
exit;