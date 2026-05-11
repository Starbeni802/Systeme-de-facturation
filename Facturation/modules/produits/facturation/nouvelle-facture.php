<?php
/**
 * Nouvelle facture
 * Système de facturation
 */

session_start();

// Inclure les fonctions d'authentification
require_once '../../../includes/fonctions-auth.php';

// Vérifier si l'utilisateur est connecté
if (!est_connecte()) {
    header('Location: ../../../auth/login.php');
    exit;
}

require_once "../../../includes/fonctions-produits.php";
require_once "../../../includes/fonctions-factures.php";

$message = '';
$produit_trouve = null;

// Initialiser le panier
initialiser_panier();

// ============================================
// TRAITEMENT SCAN CAMÉRA (Ajout direct)
// ============================================
if (isset($_GET['scan']) && $_GET['scan'] == 'direct') {
    $code_barre = trim($_GET['code']);
    $produit = trouver_produit($code_barre);
    
    if ($produit) {
        // Ajout direct avec quantité 1
        ajouter_au_panier($produit, 1);
        $message = '<div class="alert alert-success">📷 Produit scanné: ' . htmlspecialchars($produit['nom']) . ' (x1) ajouté au panier!</div>';
    } else {
        $message = '<div class="alert alert-danger">Produit introuvable: ' . htmlspecialchars($code_barre) . '</div>';
    }
}

// ============================================
// TRAITEMENT FORMULAIRE CODE-BARRES (Ajout direct)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'rechercher') {
    $code_barre = trim($_POST['code_barre']);
    $produit = trouver_produit($code_barre);
    
    if ($produit) {
        // Ajout direct au panier avec quantité 1
        ajouter_au_panier($produit, 1);
        $message = '<div class="alert alert-success">✅ Produit "' . htmlspecialchars($produit['nom']) . '" ajouté au panier!</div>';
    } else {
        $message = '<div class="alert alert-danger">Produit introuvable avec le code-barres: ' . htmlspecialchars($code_barre) . '</div>';
    }
}

// ============================================
// TRAITEMENT AJOUT AU PANIER
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $code_barre = $_POST['code_barre'];
    $quantite = (int)$_POST['quantite'];
    
    $produit = trouver_produit($code_barre);
    
    if ($produit) {
        // Vérifier le stock
        if ($quantite > $produit['quantite_stock']) {
            $message = '<div class="alert alert-danger">Stock insuffisant! Stock disponible: ' . $produit['quantite_stock'] . '</div>';
        } elseif ($quantite <= 0) {
            $message = '<div class="alert alert-danger">La quantité doit être supérieure à 0</div>';
        } else {
            // Ajouter au panier
            ajouter_au_panier($produit, $quantite);
            $message = '<div class="alert alert-success">Produit ajouté au panier!</div>';
        }
    }
}

// ============================================
// TRAITEMENT SUPPRESSION
// ============================================
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['code'])) {
    retirer_du_panier($_GET['code']);
    header('Location: nouvelle-facture.php');
    exit;
}

// ============================================
// TRAITEMENT VIDER PANIER
// ============================================
if (isset($_GET['action']) && $_GET['action'] == 'vider') {
    vider_panier();
    header('Location: nouvelle-facture.php');
    exit;
}

// ============================================
// TRAITEMENT ENREGISTREMENT FACTURE
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'enregistrer') {
    if (empty($_SESSION['panier'])) {
        $message = '<div class="alert alert-danger">Le panier est vide!</div>';
    } else {
        $facture = enregistrer_facture();
        $message = '<div class="alert alert-success">🧾 Facture ' . $facture['numero'] . ' enregistrée avec succès! Total: ' . number_format($facture['total_ttc'], 2) . ' $</div>';
    }
}

// Calculer les totaux
$totaux = calculer_totaux();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Facture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .table { margin-bottom: 0; }
        .total-row { font-weight: bold; font-size: 1.1em; }
        .scanner-section { background: white; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🧾 Nouvelle Facture</h1>
        
        <?php echo $message; ?>
        
        <div class="row">
            <!-- Section Scanner Code-barres -->
            <div class="col-md-4">
                <div class="card scanner-section">
                    <h4>📷 Scanner Code-barres</h4>
                    <p class="text-muted">Sélectionnez la caméra et activez le scanner</p>
                    
                    <!-- Sélection de la caméra -->
                    <div class="mb-3">
                        <label for="camera-select" class="form-label">📹 Choisir la caméra</label>
                        <select class="form-select" id="camera-select">
                            <option value="">Chargement des caméras...</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" id="btn-toggle-scanner" class="btn btn-primary btn-lg" onclick="toggleScanner()">📷 Activer la caméra</button>
                    </div>
                    
                    <!-- Conteneur du scanner caméra -->
                    <div id="scanner-container" style="display: none; margin-top: 15px;">
                        <div id="qr-reader" style="width: 100%; min-height: 300px; background: #000; border-radius: 8px;"></div>
                        <p class="text-muted small mt-2">📷 Pointez la caméra vers le code-barres</p>
                    </div>
                    
                    <hr>
                    
                    <!-- Formulaire de saisie manuelle -->
                    <h5>⌨️ Saisie manuelle</h5>
                    <form method="POST" id="scan-form">
                        <input type="hidden" name="action" value="rechercher">
                        <div class="mb-2">
                            <label for="code_barre" class="form-label">Code-barres</label>
                            <input type="text" class="form-control" id="code_barre" name="code_barre" 
                                    placeholder="Entrez le code-barres" maxlength="13" required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">🔍 Rechercher</button>
                    </form>
                    
                    <hr>
                    
                    <!-- Bouton pour simuler un scan -->
                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#simulerModal">
                        🧪 Simuler un scan
                    </button>
                </div>
            </div>
            
            <!-- Section Panier/Facture -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🛒 Articles de la facture</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($_SESSION['panier'])): ?>
                            <p class="text-muted text-center">Aucun article dans le panier</p>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix Unit.</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['panier'] as $code => $article): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($article['nom']); ?></td>
                                        <td><?php echo number_format($article['prix_unitaire_ht'], 2); ?> $</td>
                                        <td><?php echo $article['quantite']; ?></td>
                                        <td><?php echo number_format($article['prix_unitaire_ht'] * $article['quantite'], 2); ?> $</td>
                                        <td>
                                            <a href="?action=supprimer&code=<?php echo urlencode($code); ?>" 
                                                class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet article?')">🗑️</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Informations Client</h5>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="enregistrer">
                                        <div class="mb-2">
                                            <label class="form-label">Nom du client</label>
                                            <input type="text" class="form-control" name="client_nom" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Adresse</label>
                                            <textarea class="form-control" name="client_adresse" rows="2"></textarea>
                                        </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Totaux</h5>
                                    <table class="table">
                                        <tr>
                                            <td>Total HT:</td>
                                            <td class="text-end"><strong><?php echo number_format($totaux['total_ht'], 2); ?> $</strong></td>
                                        </tr>
                                        <tr>
                                            <td>TVA (18%):</td>
                                            <td class="text-end"><strong><?php echo number_format($totaux['tva'], 2); ?> $</strong></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td>Total TTC:</td>
                                            <td class="text-end"><strong><?php echo number_format($totaux['total_ttc'], 2); ?> $</strong></td>
                                        </tr>
                                    </table>
                                    <button type="submit" class="btn btn-success w-100">💾 Enregistrer la facture</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Bouton vider le panier -->
                <?php if (!empty($_SESSION['panier'])): ?>
                <a href="?action=vider" class="btn btn-warning">🗑️ Vider le panier</a>
                <?php endif; ?>
                <a href="../../../index.php" class="btn btn-outline-secondary">🏠 Retour à l'accueil</a>
            </div>
        </div>
    </div>
    
    <!-- Modal Simulation de scan -->
    <div class="modal fade" id="simulerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🧪 Simuler un scan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Cliquez sur un code-barres pour simuler un scan:</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="simulerScan('12345')" data-bs-dismiss="modal">12345 - Bouteille d'eau (1.50$)</button>
                        <button type="button" class="btn btn-outline-primary" onclick="simulerScan('67890')" data-bs-dismiss="modal">67890 - Pain (2.00$)</button>
                        <button type="button" class="btn btn-outline-primary" onclick="simulerScan('842473000')" data-bs-dismiss="modal">842473000 - Lait (1.20$)</button>
                        <button type="button" class="btn btn-outline-primary" onclick="simulerScan('22222')" data-bs-dismiss="modal">22222 - Fromage (5.50$)</button>
                        <button type="button" class="btn btn-outline-primary" onclick="simulerScan('33333')" data-bs-dismiss="modal">33333 - Yaourt (0.80$)</button>
                        <button type="button" class="btn btn-outline-primary" onclick="simulerScan('9501101378339')" data-bs-dismiss="modal">9501101378339 - BONBON (0.10$)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- HTML5-QRCODE pour le scanner caméra -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        // Focus automatique sur le champ code-barres au chargement
        window.addEventListener('load', function() {
            const input = document.getElementById('code_barre');
            if (input) input.focus();
        });
        
        // ============================================
        // LISTE DES CAMÉRAS DISPONIBLES
        // ============================================
        async function listerCameras() {
            const select = document.getElementById('camera-select');
            
            try {
                // Obtenir la liste des dispositifs vidéo
                const appareils = await navigator.mediaDevices.enumerateDevices();
                const cameras = appareils.filter(appareil => appareil.kind === 'videoinput');
                
                select.innerHTML = '';
                
                if (cameras.length === 0) {
                    select.innerHTML = '<option value="">Aucune caméra trouvée</option>';
                    return;
                }
                
                // Ajouter chaque caméra à la liste
                cameras.forEach((camera, index) => {
                    const option = document.createElement('option');
                    option.value = camera.deviceId;
                    
                    // Nom plus convivial
                    let label = camera.label || 'Camera ' + (index + 1);
                    if (label.toLowerCase().includes('back') || label.toLowerCase().includes('rear')) {
                        option.text = '📱 Caméra arrière (téléphone)';
                    } else if (label.toLowerCase().includes('front')) {
                        option.text = '💻 Caméra avant (ordinateur)';
                    } else if (label.toLowerCase().includes('iriun') || label.toLowerCase().includes('webcam')) {
                        option.text = '📱 IRIUN Webcam (Téléphone)';
                    } else {
                        option.text = '📹 ' + label;
                    }
                    
                    select.appendChild(option);
                });
                
            } catch (err) {
                console.error('Erreur:', err);
                select.innerHTML = '<option value="">Erreur: ' + err.message + '</option>';
            }
        }
        
        // Fonction pour rafraichir la liste des caméras
        async function rafraichirCameras() {
            const select = document.getElementById('camera-select');
            select.innerHTML = '<option value="">🔄 Recherche en cours...</option>';
            
            try {
                // Demander la permission pour accéder aux caméras
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                stream.getTracks().forEach(track => track.stop());
                
                // Recharger la liste
                await listerCameras();
            } catch (err) {
                console.error('Erreur:', err);
                select.innerHTML = '<option value="">Erreur: ' + err.message + '</option>';
            }
        }
        
        // Charger les caméras au démarrage
        setTimeout(listerCameras, 1000);
        
        // ============================================
        // SCANNER CODE-BARRES VIA CAMÉRA
        // ============================================
        let html5QrcodeScanner = null;
        
        function toggleScanner() {
            const scannerDiv = document.getElementById('scanner-container');
            const btnScanner = document.getElementById('btn-toggle-scanner');
            const qrReader = document.getElementById('qr-reader');
            const cameraSelect = document.getElementById('camera-select');
            
            if (html5QrcodeScanner) {
                // Arrêter le scanner
                try {
                    html5QrcodeScanner.clear();
                } catch(e) {}
                html5QrcodeScanner = null;
                scannerDiv.style.display = 'none';
                btnScanner.textContent = '📷 Activer la caméra';
                btnScanner.classList.remove('btn-danger');
                btnScanner.classList.add('btn-primary');
                return;
            }
            
            // Vérifier qu'une caméra est sélectionnée
            if (!cameraSelect.value) {
                alert('Veuillez sélectionner une caméra d\'abord!');
                return;
            }
            
            // Afficher le conteneur
            scannerDiv.style.display = 'block';
            btnScanner.textContent = '⏹ Arrêter le scanner';
            btnScanner.classList.remove('btn-primary');
            btnScanner.classList.add('btn-danger');
            
            // Message de statut
            qrReader.innerHTML = '<div class="text-center text-white p-3" id="scan-status">📷 Initialisation du scanner...</div>';
            
            // Utiliser Html5QrcodeScanner qui crée automatiquement l'interface vidéo
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader",
                { 
                    fps: 8,
                    qrbox: { width: 250, height: 120 },
                    aspectRatio: 1.5
                },
                false
            );
            
            // Render le scanner avec les callbacks
            html5QrcodeScanner.render(
                function(decodedText, decodedResult) {
                    // Code détecté!
                    console.log('Code détecté:', decodedText);
                    
                    // Arrêter le scanner
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                    
                    // Rediriger vers la page avec le code scanné
                    window.location.href = 'nouvelle-facture.php?scan=direct&code=' + encodeURIComponent(decodedText);
                },
                function(errorMessage) {
                    // Erreur de scan - ignorée
                    const status = document.getElementById('scan-status');
                    if (status) {
                        status.textContent = '🔍 Recherche de code-barres...';
                    }
                }
            );
            
            // Confirmer que le scanner est actif
            qrReader.innerHTML = '<div class="text-center text-white p-3" id="scan-status">✅ Scanner actif - Pointez vers le code-barres</div>' + qrReader.innerHTML;
        }
        
        // ============================================
        // SIMULATION DE SCAN (Ajout direct au panier)
        // ============================================
        function simulerScan(code) {
            window.location.href = 'nouvelle-facture.php?scan=direct&code=' + encodeURIComponent(code);
        }
        
        // ============================================
        // VALIDATION DU FORMULAIRE
        // ============================================
        document.getElementById('scan-form').addEventListener('submit', function(e) {
            const code = document.getElementById('code_barre').value;
            if (code.length < 8) {
                e.preventDefault();
                alert('Le code-barres doit contenir au moins 8 chiffres');
            }
        });
    </script>
</body>
</html>