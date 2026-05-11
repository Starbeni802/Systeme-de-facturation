<?php
// Définir le chemin de base
$chemin_base = dirname(__DIR__) . '/';

// Lire les factures depuis le fichier JSON
function lire_factures() {
    global $chemin_base;
    $fichier = $chemin_base . "data/factures.json";

    if (!file_exists($fichier)) {
        return [];
    }

    $contenu = file_get_contents($fichier);
    $factures = json_decode($contenu, true);
    return $factures ?: [];
}

// Sauvegarder les factures dans le fichier JSON
function sauvegarder_factures($factures) {
    global $chemin_base;
    $fichier = $chemin_base . "data/factures.json";
    $json = json_encode($factures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($fichier, $json);
}

// Ajouter une nouvelle facture
function ajouter_facture($facture) {
    $factures = lire_factures();
    $factures[] = $facture;
    sauvegarder_factures($factures);
}

// Générer un numéro de facture unique
function generer_numero_facture() {
    $date = date('Ymd');
    $factures = lire_factures();
    $numero = count($factures) + 1;
    return 'FAC-' . $date . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
}

// Initialiser le panier en session
function initialiser_panier() {
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
}

// Ajouter un produit au panier
function ajouter_au_panier($produit, $quantite) {
    initialiser_panier();
    
    $code_barre = $produit['code_barre'];
    
    if (isset($_SESSION['panier'][$code_barre])) {
        $_SESSION['panier'][$code_barre]['quantite'] += $quantite;
    } else {
        $_SESSION['panier'][$code_barre] = [
            'nom' => $produit['nom'],
            'prix_unitaire_ht' => $produit['prix_unitaire_ht'],
            'quantite' => $quantite
        ];
    }
}

// Retirer un produit du panier
function retirer_du_panier($code_barre) {
    if (isset($_SESSION['panier'][$code_barre])) {
        unset($_SESSION['panier'][$code_barre]);
    }
}

// Vider le panier
function vider_panier() {
    $_SESSION['panier'] = [];
}

// Calculer les totaux de la facture
function calculer_totaux() {
    initialiser_panier();
    
    $total_ht = 0;
    
    foreach ($_SESSION['panier'] as $produit) {
        $total_ht += $produit['prix_unitaire_ht'] * $produit['quantite'];
    }
    
    $tva = $total_ht * 0.18;
    $total_ttc = $total_ht + $tva;
    
    return [
        'total_ht' => $total_ht,
        'tva' => $tva,
        'total_ttc' => $total_ttc
    ];
}

// Enregistrer la facture
function enregistrer_facture() {
    $totaux = calculer_totaux();
    
    $facture = [
        'numero' => generer_numero_facture(),
        'date' => date('Y-m-d H:i:s'),
        'client' => [
            'nom' => $_POST['client_nom'] ?? 'Client default',
            'adresse' => $_POST['client_adresse'] ?? ''
        ],
        'articles' => $_SESSION['panier'],
        'total_ht' => $totaux['total_ht'],
        'tva' => $totaux['tva'],
        'total_ttc' => $totaux['total_ttc']
    ];
    
    ajouter_facture($facture);
    vider_panier();
    
    return $facture;
}

// Obtenir les ventes journalières
function obtenir_ventes_journalieres($date = null) {
    $date = $date ?? date('Y-m-d');
    $factures = lire_factures();
    
    $produits_vendus = [];
    
    $ventes_jour = [
        'date' => $date,
        'nombre_factures' => 0,
        'total_ht' => 0,
        'tva' => 0,
        'total_ttc' => 0,
        'chiffre_affaires' => 0,
        'articles_vendus' => 0,
        'produits_vendus' => []
    ];
    
    foreach ($factures as $facture) {
        $facture_date = substr($facture['date'], 0, 10);
        if ($facture_date === $date) {
            $ventes_jour['nombre_factures']++;
            $ventes_jour['total_ht'] += $facture['total_ht'];
            $ventes_jour['tva'] += $facture['tva'];
            $ventes_jour['total_ttc'] += $facture['total_ttc'];
            $ventes_jour['chiffre_affaires'] += $facture['total_ttc'];
            
            // Compter les articles vendus
            if (isset($facture['articles'])) {
                foreach ($facture['articles'] as $code_barre => $article) {
                    $ventes_jour['articles_vendus'] += $article['quantite'];
                    
                    // Produits vendus
                    if (!isset($produits_vendus[$code_barre])) {
                        $produits_vendus[$code_barre] = [
                            'nom' => $article['nom'],
                            'quantite' => 0,
                            'montant' => 0
                        ];
                    }
                    $produits_vendus[$code_barre]['quantite'] += $article['quantite'];
                    $produits_vendus[$code_barre]['montant'] += $article['prix_unitaire_ht'] * $article['quantite'];
                }
            }
        }
    }
    
    $ventes_jour['produits_vendus'] = $produits_vendus;
    
    return $ventes_jour;
}

// Obtenir les ventes mensuelles
function obtenir_ventes_mensuelles($annee = null, $mois = null) {
    $annee = $annee ?? date('Y');
    $mois = $mois ?? date('m');
    $factures = lire_factures();
    
    $ventes_par_jour = [];
    $produits_vendus = [];
    
    $ventes_mois = [
        'annee' => $annee,
        'mois' => $mois,
        'nombre_factures' => 0,
        'total_ht' => 0,
        'tva' => 0,
        'total_ttc' => 0,
        'chiffre_affaires' => 0,
        'articles_vendus' => 0,
        'ventes_par_jour' => [],
        'produits_vendus' => []
    ];
    
    foreach ($factures as $facture) {
        $facture_date = substr($facture['date'], 0, 7);
        $date_attendue = "$annee-$mois";
        if ($facture_date === $date_attendue) {
            $ventes_mois['nombre_factures']++;
            $ventes_mois['total_ht'] += $facture['total_ht'];
            $ventes_mois['tva'] += $facture['tva'];
            $ventes_mois['total_ttc'] += $facture['total_ttc'];
            $ventes_mois['chiffre_affaires'] += $facture['total_ttc'];
            
            // Jour de la vente
            $jour = substr($facture['date'], 8, 2);
            if (!isset($ventes_par_jour[$jour])) {
                $ventes_par_jour[$jour] = [
                    'nombre_factures' => 0,
                    'chiffre_affaires' => 0
                ];
            }
            $ventes_par_jour[$jour]['nombre_factures']++;
            $ventes_par_jour[$jour]['chiffre_affaires'] += $facture['total_ttc'];
            
            // Compter les articles vendus
            if (isset($facture['articles'])) {
                foreach ($facture['articles'] as $code_barre => $article) {
                    $ventes_mois['articles_vendus'] += $article['quantite'];
                    
                    // Produits vendus
                    if (!isset($produits_vendus[$code_barre])) {
                        $produits_vendus[$code_barre] = [
                            'nom' => $article['nom'],
                            'quantite' => 0,
                            'total' => 0
                        ];
                    }
                    $produits_vendus[$code_barre]['quantite'] += $article['quantite'];
                    $produits_vendus[$code_barre]['total'] += $article['prix_unitaire_ht'] * $article['quantite'];
                }
            }
        }
    }
    
    $ventes_mois['ventes_par_jour'] = $ventes_par_jour;
    $ventes_mois['produits_vendus'] = $produits_vendus;
    
    return $ventes_mois;
}

// Obtenir le top des produits
function obtenir_top_produits($limite = 10, $periode = 'mois') {
    $factures = lire_factures();
    $produits = [];
    
    $aujourdhui = date('Y-m-d');
    $ce_mois = date('Y-m');
    
    foreach ($factures as $facture) {
        $facture_date = substr($facture['date'], 0, 10);
        $facture_mois = substr($facture['date'], 0, 7);
        
        $inclure = false;
        if ($periode === 'jour' && $facture_date === $aujourdhui) {
            $inclure = true;
        } elseif ($periode === 'mois' && $facture_mois === $ce_mois) {
            $inclure = true;
        } elseif ($periode === 'tous') {
            $inclure = true;
        }
        
        if ($inclure && isset($facture['articles'])) {
            foreach ($facture['articles'] as $code_barre => $article) {
                if (!isset($produits[$code_barre])) {
                    $produits[$code_barre] = [
                        'code_barre' => $code_barre,
                        'nom' => $article['nom'],
                        'quantite' => 0,
                        'total' => 0
                    ];
                }
                $produits[$code_barre]['quantite'] += $article['quantite'];
                $produits[$code_barre]['total'] += $article['prix_unitaire_ht'] * $article['quantite'];
            }
        }
    }
    
    // Trier par quantité
    usort($produits, function($a, $b) {
        return $b['quantite'] - $a['quantite'];
    });
    
    return array_slice($produits, 0, $limite);
}
?>