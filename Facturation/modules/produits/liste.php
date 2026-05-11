<?php
include("../../includes/fonctions-produits.php");

$produits = lire_produits();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des produits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-hover tbody tr:hover { background-color: #f1f5f9; }
        .clickable-row { cursor: pointer; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">📦 Liste des produits</h1>
                <p class="text-muted">Cliquez sur un produit pour voir ses détails.</p>
            </div>
            <div>
                <a href="enregistrer.php" class="btn btn-success me-2">➕ Ajouter produit</a>
                <a href="facturation/nouvelle-facture.php" class="btn btn-primary">🧾 Facturation</a>
            </div>
        </div>

        <?php if (empty($produits)): ?>
            <div class="alert alert-warning">Aucun produit disponible. Ajoutez un produit pour commencer.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Code-barres</th>
                            <th>Nom</th>
                            <th>Prix HT</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $produit): ?>
                            <tr class="clickable-row" onclick="afficherDetails('<?php echo htmlspecialchars($produit['code_barre']); ?>')">
                                <td><?php echo htmlspecialchars($produit['code_barre']); ?></td>
                                <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                                <td><?php echo number_format($produit['prix_unitaire_ht'], 2); ?> $</td>
                                <td><?php echo htmlspecialchars($produit['quantite_stock']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div id="detail-panel" class="card mt-4 d-none">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Détails du produit</h5>
            </div>
            <div class="card-body">
                <p><strong>Code-barres :</strong> <span id="detail-code"></span></p>
                <p><strong>Nom :</strong> <span id="detail-nom"></span></p>
                <p><strong>Prix HT :</strong> <span id="detail-prix"></span> $</p>
                <p><strong>Quantité en stock :</strong> <span id="detail-stock"></span></p>
                <p><strong>Date d'expiration :</strong> <span id="detail-expiration"></span></p>
                <p><strong>Date d'enregistrement :</strong> <span id="detail-enregistrement"></span></p>
                <a href="enregistrer.php" class="btn btn-success">Modifier / Ajouter un produit</a>
                <a href="facturation/nouvelle-facture.php" class="btn btn-primary">Aller à la facturation</a>
            </div>
        </div>
    </div>

    <script>
        const produits = <?php echo json_encode($produits, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS); ?>;

        function afficherDetails(code) {
            const produit = produits.find(p => p.code_barre === code);
            if (!produit) return;

            document.getElementById('detail-code').textContent = produit.code_barre;
            document.getElementById('detail-nom').textContent = produit.nom;
            document.getElementById('detail-prix').textContent = Number(produit.prix_unitaire_ht).toFixed(2);
            document.getElementById('detail-stock').textContent = produit.quantite_stock;
            document.getElementById('detail-expiration').textContent = produit.date_expiration || '-';
            document.getElementById('detail-enregistrement').textContent = produit.date_enregistrement || '-';

            document.getElementById('detail-panel').classList.remove('d-none');
            window.scrollTo({ top: document.getElementById('detail-panel').offsetTop - 20, behavior: 'smooth' });
        }
    </script>
</body>
</html>