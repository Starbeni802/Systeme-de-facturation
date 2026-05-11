<?php
// Définir le chemin de base
$chemin_base = dirname(__DIR__) . '/';

// Lire les produits depuis le fichier JSON
function lire_produits() {
    global $chemin_base;
    $fichier = $chemin_base . "data/produits.json";

    if (!file_exists($fichier)) {
        return [];
    }

    $contenu = file_get_contents($fichier);
    return json_decode($contenu, true);
}

// Sauvegarder les produits dans le fichier JSON
function sauvegarder_produits($produits) {
    global $chemin_base;
    $fichier = $chemin_base . "data/produits.json";
    $json = json_encode($produits, JSON_PRETTY_PRINT);
    file_put_contents($fichier, $json);
}

// Rechercher un produit par code-barres
function trouver_produit($code_barre) {
    $produits = lire_produits();

    foreach ($produits as $produit) {
        if ($produit['code_barre'] == $code_barre) {
            return $produit;
        }

    }

    return null;
}

// Ajouter un produit
function ajouter_produit($nouveau_produit) {
    $produits = lire_produits();
    $produits[] = $nouveau_produit;
    sauvegarder_produits($produits);
}
?>