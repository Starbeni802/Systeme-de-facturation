<?php
/**
 * Vérification de session
 * Ce fichier doit être inclus au début de chaque page protégée
 */

session_start();

// Inclure les fonctions d'authentification
require_once __DIR__ . '/../includes/fonctions-auth.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    // Rediriger vers la page de connexion
    header('Location: ../auth/login.php');
    exit;
}

/**
 * Fonction pour vérifier un rôle spécifique
 * Utilisation: verifier_acces('admin');
 */
function verifier_acces($role_requis) {
    if (!verifier_role($role_requis)) {
        // Afficher un message d'erreur et rediriger
        $_SESSION['erreur'] = 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.';
        header('Location: ../index.php');
        exit;
    }
}

/**
 * Fonction pour vérifier que l'utilisateur est admin
 */
function verifier_admin() {
    $utilisateur = get_utilisateur_connecte();
    if (!in_array($utilisateur['role'], ['admin', 'manager'])) {
        $_SESSION['erreur'] = 'Accès réservé aux administrateurs et managers.';
        header('Location: ../index.php');
        exit;
    }
}

/**
 * Fonction pour vérifier que l'utilisateur est admin ou manager
 */
function verifier_manager() {
    $utilisateur = get_utilisateur_connecte();
    if (!in_array($utilisateur['role'], ['admin', 'manager'])) {
        $_SESSION['erreur'] = 'Accès réservé aux administrateurs et managers.';
        header('Location: ../index.php');
        exit;
    }
}