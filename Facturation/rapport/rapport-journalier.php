<?php
session_start();
require_once "../includes/fonctions-factures.php";

// Traitement du changement de date
$date_selectionnee = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Obtenir les ventes du jour
$ventes = obtenir_ventes_journalieres($date_selectionnee);

// Obtenir toutes les factures pour afficher le détail
$factures = lire_factures();
$factures_jour = [];
foreach ($factures as $facture) {
    $facture_date = substr($facture['date'], 0, 10);
    if ($facture_date === $date_selectionnee) {
        $factures_jour[] = $facture;
    }
}

// Nom du jour
$jour_nom = date('l', strtotime($date_selectionnee));
$jours_fr = [
    'Monday' => 'Lundi',
    'Tuesday' => 'Mardi',
    'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi',
    'Friday' => 'Vendredi',
    'Saturday' => 'Samedi',
    'Sunday' => 'Dimanche'
];
$jour_fr = $jours_fr[$jour_nom] ?? $jour_nom;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Journalier</title>
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
        .badge-produit { 
            background: #e3f2fd;
            color: #1565c0;
            padding: 5px 10px;
            border-radius: 20px;
            margin: 2px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h1><i class="fas fa-calendar-day"></i> Rapport Journalier</h1>
                <p class="text-muted"><?php echo $jour_fr . ' ' . date('d/m/Y', strtotime($date_selectionnee)); ?></p>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($ventes['chiffre_affaires'], 2); ?> $</div>
                    <div class="stat-label"><i class="fas fa-money-bill-wave"></i> Chiffre d'affaires</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card green">
                    <div class="stat-value"><?php echo $ventes['nombre_factures']; ?></div>
                    <div class="stat-label"><i class="fas fa-receipt"></i> Nombre de factures</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card orange">
                    <div class="stat-value"><?php echo count($ventes['produits_vendus']); ?></div>
                    <div class="stat-label"><i class="fas fa-box"></i> Types de produits vendus</div>
                </div>
            </div>
        </div>

        <!-- Produits vendus -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-boxes"></i> Produits Vendus</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ventes['produits_vendus'])): ?>
                            <p class="text-muted text-center">Aucune vente ce jour</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code-barres</th>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ventes['produits_vendus'] as $code => $produit): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($code); ?></code></td>
                                            <td><strong><?php echo htmlspecialchars($produit['nom']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $produit['quantite']; ?></span>
                                            </td>
                                            <td><?php echo number_format($produit['montant'] ?? 0, 2); ?> $</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <th colspan="2">TOTAL</th>
                                            <th><?php 
                                                $total_qte = 0;
                                                foreach ($ventes['produits_vendus'] as $p) $total_qte += $p['quantite'];
                                                echo $total_qte;
                                            ?></th>
                                            <th><?php echo number_format($ventes['chiffre_affaires'], 2); ?> $</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détail des factures -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-file-invoice"></i> Détail des Factures</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($factures_jour)): ?>
                            <p class="text-muted text-center">Aucune facture ce jour</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>N° Facture</th>
                                            <th>Heure</th>
                                            <th>Client</th>
                                            <th>Articles</th>
                                            <th>Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($factures_jour as $facture): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($facture['numero']); ?></strong></td>
                                            <td><?php echo date('H:i', strtotime($facture['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($facture['client']['nom']); ?></td>
                                            <td>
                                                <?php 
                                                $articles_list = [];
                                                foreach ($facture['articles'] as $art) {
                                                    $articles_list[] = $art['nom'] . ' (x' . $art['quantite'] . ')';
                                                }
                                                echo implode(', ', $articles_list);
                                                ?>
                                            </td>
                                            <td><strong><?php echo number_format($facture['total_ttc'], 2); ?> $</strong></td>
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
                <a href="rapport-mensuel.php" class="btn btn-outline-success">
                    <i class="fas fa-calendar-alt"></i> Rapport Mensuel
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