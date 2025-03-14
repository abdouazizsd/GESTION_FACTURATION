<?php
// Récupérer la liste des clients
$sql = "SELECT id, nom FROM clients";
$stmt = $pdo->query($sql);
$clients = $stmt->fetchAll();

// Récupérer la liste des produits
$sql = "SELECT id, nom, prix_unitaire FROM produits";
$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = intval($_POST['client_id']);
    $date_creation = date('Y-m-d');
    
    // 1️⃣ Insérer le devis
    $sql = "INSERT INTO devis (client_id, date_creation, montant_total, statut) VALUES (?, ?, 0, 'En attente')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$client_id, $date_creation]);
    $devis_id = $pdo->lastInsertId();

    // 2️⃣ Ajouter les produits sélectionnés
    $montant_total = 0;
    if (!empty($_POST['produits'])) {
        $sql = "INSERT INTO devis_produits (devis_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($_POST['produits'] as $produit) {
            if (!empty($produit['id']) && !empty($produit['quantite'])) {
                $produit_id = intval($produit['id']);
                $quantite = intval($produit['quantite']);

                // Récupérer le prix du produit
                $sql_prix = "SELECT prix_unitaire FROM produits WHERE id = ?";
                $stmt_prix = $pdo->prepare($sql_prix);
                $stmt_prix->execute([$produit_id]);
                $prix_unitaire = $stmt_prix->fetchColumn();

                if ($prix_unitaire) {
                    $stmt->execute([$devis_id, $produit_id, $quantite, $prix_unitaire]);
                    $montant_total += $quantite * $prix_unitaire;
                }
            }
        }
    }

    // 3️⃣ Mettre à jour le montant total du devis
    $sql = "UPDATE devis SET montant_total = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$montant_total, $devis_id]);

    // Redirection après ajout
    header("Location: admin_client_dashboard.php?page=liste_devis");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Devis</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
</head>
<body class="bg-light">
    <div class="container mt-5" style="border-radius: 30px;">
        <div class="card" style="border-radius: 30px;">
            <div class="card-header bg-primary text-white text-center" style="height: 60px;">
                <h2>Créer un Devis</h2>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <label  for="client_id"><h5>Client : </h5></label>
                        <select class="form-control" id="client_id" name="client_id" required>
                            <option value="">-- Sélectionner un client --</option>
                            <?php foreach ($clients as $client) : ?>
                                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <h5>Produits :</h5>
                    <div class="row">
                        <?php foreach ($produits as $produit) : ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="produit_<?= $produit['id'] ?>" name="produits[<?= $produit['id'] ?>][id]" value="<?= $produit['id'] ?>" onchange="toggleQuantity(<?= $produit['id'] ?>)">
                                        <label class="custom-control-label" for="produit_<?= $produit['id'] ?>">
                                            <?= htmlspecialchars($produit['nom']) ?> - <?= number_format($produit['prix_unitaire'], 2, ',', ' ') ?> FCFA
                                        </label>
                                    </div>
                                    <input type="number" class="form-control mt-2" id="quantite_<?= $produit['id'] ?>" name="produits[<?= $produit['id'] ?>][quantite]" min="1" placeholder="Quantité" style="display: none;">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Créer le Devis</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleQuantity(produitId) {
            var checkbox = document.getElementById('produit_' + produitId);
            var quantityInput = document.getElementById('quantite_' + produitId);
            if (checkbox.checked) {
                quantityInput.style.display = 'block';
            } else {
                quantityInput.style.display = 'none';
                quantityInput.value = ''; // Réinitialiser la valeur lorsque décoché
            }
        }
    </script>


    <!-- Contenu de la page -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
