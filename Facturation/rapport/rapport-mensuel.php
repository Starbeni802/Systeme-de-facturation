<?php
session_start();
require_once "../includes/fonctions-factures.php";

// Traitement du changement de mois/année
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');
$mois = isset($_GET['mois']) ? (int)$_GET['mois'] : (int)date('m');

// Obtenir les ventes du mois
$ventes = obtenir_ventes_mensuelles($annee, str_pad($mois, 2, '0', STR_PAD_LEFT));

// Nom du mois
$mois_noms = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];
$nom_mois = $mois_noms[$mois] ?? '';

// Calculer la moyenne quotidienne
$jours_with_sales = count($ventes['ventes_par_jour']);
$moyenne_jour = $jours_with_sales > 0 ? $ventes['chiffre_affaires'] / $jours_with_sales : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Mensuel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }
        .stat-card.green { 
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.orange { 
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card.yellow { 
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-value { font-size: 2em; font-weight: bold; }
        .stat-label { font-size: 0.9em; opacity: 0.9; }
        .table-produits { margin-top: 20px; }
        .progress-bar { transition: width 0.5s ease; }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h1><i class="fas fa-calendar-alt"></i> Rapport Mensuel</h1>
                <p class="text-muted"><?php echo $nom_mois . ' ' . $annee; ?></p>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($ventes['chiffre_affaires'], 2); ?> $</div>
                    <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Chiffre d'affaires</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card green">
                    <div class="stat-value"><?php echo $ventes['nombre_factures']; ?></div>
                    <div class="stat-label"><i class="fas fa-receipt"></i> Nombre de factures</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card orange">
                    <div class="stat-value"><?php echo number_format($moyenne_jour, 2); ?> $</div>
                    <div class="stat-label"><i class="fas fa-chart-line"></i> Moyenne / jour</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card yellow">
                    <div class="stat-value"><?php echo count($ventes['produits_vendus']); ?></div>
                    <div class="stat-label"><i class="fas fa-box"></i> Types de produits</div>
                </div>
            </div>
        </div>

        <!-- Ventes par jour -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Ventes par Jour</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ventes['ventes_par_jour'])): ?>
                            <p class="text-muted text-center">Aucune vente ce mois</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Jour</th>
                                            <th>Nombre de factures</th>
                                            <th>Chiffre d'affaires</th>
                                            <th>Graphique</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        ksort($ventes['ventes_par_jour']);
                                        $max_ca = max(array_column($ventes['ventes_par_jour'], 'chiffre_affaires'));
                                        foreach ($ventes['ventes_par_jour'] as $jour => $v): 
                                            $pourcentage = $max_ca > 0 ? ($v['chiffre_affaires'] / $max_ca) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $jour; ?></strong></td>
                                            <td><?php echo $v['nombre_factures']; ?></td>
                                            <td><?php echo number_format($v['chiffre_affaires'], 2); ?> $</td>
                                            <td style="width: 40%;">
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                            style="width: <?php echo $pourcentage; ?>%;">
                                                        <?php echo number_format($pourcentage, 0); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Produits -->
        <div class="row mt-4">
            <!-- Tous les produits vendus -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-boxes"></i> Produits Vendus</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ventes['produits_vendus'])): ?>
                            <p class="text-muted text-center">Aucune vente ce mois</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Trier par quantité
                                        uasort($ventes['produits_vendus'], function($a, $b) {
                                            return $b['quantite'] - $a['quantite'];
                                        });
                                        foreach ($ventes['produits_vendus'] as $code => $produit): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                                            <td><span class="badge bg-success"><?php echo $produit['quantite']; ?></span></td>
                                            <td><?php echo number_format($produit['total'] ?? 0, 2); ?> $</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons de navigation -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <a href="../produits/facturation/nouvelle-facture.php" class="btn btn-outline-primary">
                    <i class="fas fa-plus-circle"></i> Nouvelle Facture
                </a>
                <a href="rapport-journalier.php" class="btn btn-outline-success">
                    <i class="fas fa-calendar-day"></i> Rapport Journalier
                </a>
                <a href="../index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Accueil
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>