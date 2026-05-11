<?php
// Script pour ajouter des produits de test
$fichier = __DIR__ . "/../../../data/produits.json";

$produits = [
    [
        "code_barre" => "12345",
        "nom" => "Bouteille d'eau",
        "prix_unitaire_ht" => 1.5,
        "quantite_stock" => 100,
        "date_expiration" => "2026-12-31",
        "date_enregistrement" => "2026-04-26"
    ],
    [
        "code_barre" => "67890",
        "nom" => "Pain",
        "prix_unitaire_ht" => 2.0,
        "quantite_stock" => 50,
        "date_expiration" => "2026-04-28",
        "date_enregistrement" => "2026-04-26"
    ],
    [
        "code_barre" => "8 424730 00",
        "nom" => "Lait",
        "prix_unitaire_ht" => 1.2,
        "quantite_stock" => 80,
        "date_expiration" => "2026-05-01",
        "date_enregistrement" => "2026-04-26"
    ],
    [
        "code_barre" => "22222",
        "nom" => "Fromage",
        "prix_unitaire_ht" => 5.5,
        "quantite_stock" => 30,
        "date_expiration" => "2026-05-15",
        "date_enregistrement" => "2026-04-26"
    ],
    [
        "code_barre" => "33333",
        "nom" => "Yaourt",
        "prix_unitaire_ht" => 0.8,
        "quantite_stock" => 120,
        "date_expiration" => "2026-05-10",
        "date_enregistrement" => "2026-04-26"
    ]
];

$json = json_encode($produits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($fichier, $json);

// Rediriger vers la page de gestion des produits
header('Location: ajouter-produit.php');
exit;