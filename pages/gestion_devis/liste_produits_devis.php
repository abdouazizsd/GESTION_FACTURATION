<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si un devis est spécifié
$devis_id = isset($_GET['devis_id']) ? intval($_GET['devis_id']) : 0;
if ($devis_id <= 0) {
    die("Devis non spécifié ou ID invalide.");
}

// Récupérer les informations du devis
$sql = "SELECT * FROM devis WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$devis = $stmt->fetch();

if (!$devis) {
    die("Devis introuvable.");
}

// Récupérer les produits du devis avec leurs montants
$sql = "SELECT dp.id, p.nom, dp.quantite, dp.prix_unitaire, (dp.quantite * dp.prix_unitaire) AS total 
        FROM devis_produits dp
        JOIN produits p ON dp.produit_id = p.id
        WHERE dp.devis_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$produits = $stmt->fetchAll();

// Calculer le montant total du devis
$total_devis = array_sum(array_column($produits, 'total'));

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits du Devis #<?= htmlspecialchars($devis_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Ajout de jQuery -->
    <style>
        .scrollable-container {
            max-height: 400px; /* Hauteur max avant l'affichage du scroll */
            overflow-y: auto; /* Ajout de la scrollbar verticale */
            display: block;
        }
    </style>

</head>
<body class="bg-light">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title text-center">Produits du Devis #<?= htmlspecialchars($devis_id) ?></h2>
            </div>
            <div class="card-body">
                <table class="table custom-table">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">Nom du Produit</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-center">Prix Unitaire (FCFA)</th>
                            <th class="text-center">Total (FCFA)</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $produit) : ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($produit["nom"]) ?></td>
                                <td class="text-center"><?= htmlspecialchars($produit["quantite"]) ?></td>
                                <td class="text-center"><?= htmlspecialchars($produit["prix_unitaire"]) ?></td>
                                <td class="text-center"><?= htmlspecialchars($produit["total"]) ?></td>
                                <td class="text-center">
                                    <a href="../gestion_produits/modifier_produit_devis.php?id=<?= $produit['id'] ?>&devis_id=<?= $devis_id ?>">Modifier</a> | 
                                    <a href="../gestion_produits/supprimer_produit_devis.php?id=<?= $produit['id'] ?>&devis_id=<?= $devis_id ?>" onclick="return confirm('Supprimer ce produit du devis ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-center"><strong>Total du Devis :</strong></td>
                            <td class="text-center"><strong><?= htmlspecialchars(number_format($total_devis, 2, ',', ' ')) ?> FCFA</strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <br>
            <a href="../gestion_produits/ajouter_produit_devis.php?devis_id=<?= $devis_id ?>">Ajouter un produit</a>
            <br>
            <a href="voir_devis.php?id=<?= $devis_id ?>">Retour au devis</a>
        </div>
    </div>
    <h2>Produits du Devis #<?= htmlspecialchars($devis_id) ?></h2>

    <!-- Scripts pour Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" 
            integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
</body>
</html>
