<?php
/**
 * Fonctions d'authentification
 * Système de facturation - Gestion des utilisateurs
 */

// Chemin vers le fichier utilisateurs
define('FICHIER_UTILISATEURS', __DIR__ . '/../data/utilisateurs.json');

/**
 * Lire tous les utilisateurs depuis le fichier JSON
 */
function lire_utilisateurs() {
    $fichier = FICHIER_UTILISATEURS;
    
    if (!file_exists($fichier)) {
        return [];
    }
    
    $contenu = file_get_contents($fichier);
    $utilisateurs = json_decode($contenu, true);
    return $utilisateurs ?: [];
}

/**
 * Sauvegarder les utilisateurs dans le fichier JSON
 */
function sauvegarder_utilisateurs($utilisateurs) {
    $fichier = FICHIER_UTILISATEURS;
    $json = json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($fichier, $json);
}

/**
 * Trouver un utilisateur par son identifiant
 */
function trouver_utilisateur_par_identifiant($identifiant) {
    $utilisateurs = lire_utilisateurs();
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['identifiant'] === $identifiant) {
            return $utilisateur;
        }
    }
    
    return null;
}

/**
 * Trouver un utilisateur par son ID
 */
function trouver_utilisateur_par_id($id) {
    $utilisateurs = lire_utilisateurs();
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['id'] == $id) {
            return $utilisateur;
        }
    }
    
    return null;
}

/**
 * Vérifier les identifiants de connexion
 */
function verifier_connexion($identifiant, $mot_de_passe) {
    $utilisateur = trouver_utilisateur_par_identifiant($identifiant);
    
    if (!$utilisateur) {
        return false;
    }
    
    // Vérifier si le compte est actif
    if (isset($utilisateur['statut']) && $utilisateur['statut'] !== 'actif') {
        return false;
    }
    
    if (!password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        // Vérifier aussi si le mot de passe est en texte brut (pour compatibilité)
        if ($mot_de_passe !== $utilisateur['mot_de_passe']) {
            return false;
        }
    }
    
    return $utilisateur;
}

/**
 * Créer un nouvel utilisateur
 */
function creer_utilisateur($identifiant, $mot_de_passe, $nom, $prenom, $role) {
    $utilisateurs = lire_utilisateurs();
    
    // Vérifier si l'identifiant existe déjà
    foreach ($utilisateurs as $u) {
        if ($u['identifiant'] === $identifiant) {
            return ['success' => false, 'message' => 'Cet identifiant existe déjà'];
        }
    }
    
    // Générer un nouvel ID
    $nouvel_id = 1;
    if (!empty($utilisateurs)) {
        $ids = array_column($utilisateurs, 'id');
        $nouvel_id = max($ids) + 1;
    }
    
    // Hasher le mot de passe
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    $nouvel_utilisateur = [
        'id' => $nouvel_id,
        'identifiant' => $identifiant,
        'mot_de_passe' => $mot_de_passe_hash,
        'nom' => $nom,
        'prenom' => $prenom,
        'role' => $role,
        'statut' => 'actif',
        'date_creation' => date('Y-m-d')
    ];
    
    $utilisateurs[] = $nouvel_utilisateur;
    sauvegarder_utilisateurs($utilisateurs);
    
    return ['success' => true, 'message' => 'Utilisateur créé avec succès'];
}

/**
 * Supprimer un utilisateur par son ID
 */
function supprimer_utilisateur($id) {
    $utilisateurs = lire_utilisateurs();
    
    $nouveaux_utilisateurs = [];
    $supprime = false;
    
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['id'] != $id) {
            $nouveaux_utilisateurs[] = $utilisateur;
        } else {
            $supprime = true;
        }
    }
    
    if ($supprime) {
        sauvegarder_utilisateurs($nouveaux_utilisateurs);
        return ['success' => true, 'message' => 'Utilisateur supprimé avec succès'];
    }
    
    return ['success' => false, 'message' => 'Utilisateur introuvable'];
}

/**
 * Mettre à jour un utilisateur
 */
function mettre_a_jour_utilisateur($id, $donnees) {
    $utilisateurs = lire_utilisateurs();
    
    foreach ($utilisateurs as &$utilisateur) {
        if ($utilisateur['id'] == $id) {
            // Mettre à jour uniquement les champs fournis
            if (isset($donnees['nom'])) $utilisateur['nom'] = $donnees['nom'];
            if (isset($donnees['prenom'])) $utilisateur['prenom'] = $donnees['prenom'];
            if (isset($donnees['role'])) $utilisateur['role'] = $donnees['role'];
            if (isset($donnees['statut'])) $utilisateur['statut'] = $donnees['statut'];
            if (isset($donnees['mot_de_passe']) && !empty($donnees['mot_de_passe'])) {
                $utilisateur['mot_de_passe'] = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);
            }
            
            sauvegarder_utilisateurs($utilisateurs);
            return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès'];
        }
    }
    
    return ['success' => false, 'message' => 'Utilisateur introuvable'];
}

/**
 * Activer ou désactiver un utilisateur
 */
function changer_statut($id, $statut) {
    return mettre_a_jour_utilisateur($id, ['statut' => $statut]);
}

/**
 * Vérifier si un utilisateur est connecté
 */
function est_connecte() {
    return isset($_SESSION['utilisateur']) && !empty($_SESSION['utilisateur']);
}

/**
 * Obtenir l'utilisateur connecté
 */
function get_utilisateur_connecte() {
    if (est_connecte()) {
        return $_SESSION['utilisateur'];
    }
    return null;
}

/**
 * Vérifier le rôle de l'utilisateur
 */
function verifier_role($role_requis) {
    if (!est_connecte()) {
        return false;
    }
    
    $utilisateur = get_utilisateur_connecte();
    $roles_permis = ['admin', 'manager', 'caissier'];
    
    // Si le rôle requis est dans la liste des rôles permise
    if (in_array($role_requis, $roles_permis)) {
        return $utilisateur['role'] === $role_requis;
    }
    
    return false;
}

/**
 * Vérifier si l'utilisateur a accès (admin ou manager)
 */
function verifier_acces_management() {
    if (!est_connecte()) {
        return false;
    }
    
    $utilisateur = get_utilisateur_connecte();
    return in_array($utilisateur['role'], ['admin', 'manager']);
}

/**
 * Créer la session utilisateur après connexion
 */
function connecter_utilisateur($utilisateur) {
    $_SESSION['utilisateur'] = [
        'id' => $utilisateur['id'],
        'identifiant' => $utilisateur['identifiant'],
        'nom' => $utilisateur['nom'],
        'prenom' => $utilisateur['prenom'],
        'role' => $utilisateur['role']
    ];
}

/**
 * Détruire la session utilisateur
 */
function deconnecter_utilisateur() {
    unset($_SESSION['utilisateur']);
}