<?php
include("../../../includes/fonctions-produits.php");

$message = "";
$produits = lire_produits();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_barre = $_POST["code_barre"];
    $nom = $_POST["nom"];
    $prix = $_POST["prix"];
    $quantite = $_POST["quantite"];
    $date_expiration = $_POST["date_expiration"];

    if (empty($code_barre) || empty($nom) || empty($prix) || empty($quantite)) {
        $message = "Tous les champs sont obligatoires";
    } elseif (!is_numeric($prix) || $prix <= 0) {
        $message = "Prix invalide";
    } elseif (!is_numeric($quantite) || $quantite < 0) {
        $message = "Quantité invalide";
    } else {
        $produit_existant = trouver_produit($code_barre);

        if ($produit_existant) {
            $message = "Produit déjà existant";
        } else {
            $nouveau_produit = [
                "code_barre" => $code_barre,
                "nom" => $nom,
                "prix_unitaire_ht" => (float)$prix,
                "date_expiration" => $date_expiration,
                "quantite_stock" => (int)$quantite,
                "date_enregistrement" => date("Y-m-d")
            ];

            ajouter_produit($nouveau_produit);
            $message = "Produit ajouté avec succès";
            $produits = lire_produits();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .clickable-row { cursor: pointer; }
        .card { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3">➕ Ajouter un produit</h1>
                <p class="text-muted">La facturation est disponible avant l’enregistrement du produit.</p>
            </div>
            <div>
                <a href="../liste.php" class="btn btn-secondary me-2">📋 Liste des produits</a>
                <a href="../facturation/nouvelle-facture.php" class="btn btn-primary">🧾 Facturation</a>
            </div>
        </div>

        <?php if ($message !== ""): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Formulaire d'ajout</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Code-barres</label>
                                <input type="text" name="code_barre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prix HT</label>
                                <input type="number" name="prix" step="0.01" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="quantite" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date d'expiration</label>
                                <input type="date" name="date_expiration" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success">Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Produits existants</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($produits)): ?>
                            <p class="text-muted">Aucun produit trouvé.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Nom</th>
                                            <th>Prix</th>
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
                    </div>
                </div>

                <div id="detail-panel" class="card shadow-sm d-none">
                    <div class="card-header bg-secondary text-white">
                        Détails du produit sélectionné
                    </div>
                    <div class="card-body">
                        <p><strong>Code-barres :</strong> <span id="detail-code"></span></p>
                        <p><strong>Nom :</strong> <span id="detail-nom"></span></p>
                        <p><strong>Prix HT :</strong> <span id="detail-prix"></span> $</p>
                        <p><strong>Quantité :</strong> <span id="detail-stock"></span></p>
                        <p><strong>Date expiration :</strong> <span id="detail-expiration"></span></p>
                        <p><strong>Date enregistrement :</strong> <span id="detail-enregistrement"></span></p>
                    </div>
                </div>
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
            document.getElementById('detail-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
</body>
</html>