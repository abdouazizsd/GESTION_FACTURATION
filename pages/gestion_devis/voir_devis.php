<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    die("Devis non spécifié.");
}

$devis_id = $_GET['id'];

$sql = "SELECT d.*, c.nom AS client_nom FROM devis d JOIN clients c ON d.client_id = c.id WHERE d.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$devis = $stmt->fetch();

if (!$devis) {
    die("Devis introuvable.");
}

$sql = "SELECT dp.*, p.nom FROM devis_produits dp JOIN produits p ON dp.produit_id = p.id WHERE dp.devis_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$produits = $stmt->fetchAll();

$montant_total = 0;
foreach ($produits as $produit) {
    $montant_total += $produit['quantite'] * $produit['prix_unitaire'];
}

$sql = "UPDATE devis SET montant_total = ? WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$montant_total, $devis_id]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir Devis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .scrollable-container {
            max-height: 400px; /* Hauteur max avant l'affichage du scroll */
            overflow-y: auto; /* Ajout de la scrollbar verticale */
            display: block;
        }

    </style>
</head>
<body class="bg-light">
    <br>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Devis #<?= htmlspecialchars($devis_id) ?></h3>
            </div>
            <div class="card-body scrollable-container">
                <p><strong>Client :</strong> <?= htmlspecialchars($devis['client_nom']) ?></p>
                <p><strong>Date :</strong> <?= htmlspecialchars($devis['date_creation']) ?></p>
                <p><strong>Montant Total :</strong> <span class="text-success"><?= number_format($montant_total, 2, ',', ' ') ?> FCFA</span></p>

                <h4 class="mt-4">Produits</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Prix Unitaire</th>
                                <th>Total</th>
                                <th>MODIFIFIER</th>
                                <th>SUPPRIMER</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produits as $produit) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($produit["nom"]) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($produit["quantite"]) ?></td>
                                    <td class="text-center"><?= number_format($produit["prix_unitaire"], 2, ',', ' ') ?> FCFA</td>
                                    <td class="text-center"><?= number_format($produit["quantite"] * $produit["prix_unitaire"], 2, ',', ' ') ?> FCFA</td>
                                    <td class="text-center">
                                        <a href="admin_client_dashboard.php?page=modifier_prod_devis&id=<?= $produit['id'] ?>&devis_id=<?= $devis_id ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>&nbsp
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="admin_client_dashboard.php?page=supprimer_prod_devis&id=<?= $produit['id'] ?>&devis_id=<?= $devis_id ?>" 
                                        onclick="return confirm('Voulez-vous vraiment supprimer ce produit du devis ?')"
                                        class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>&nbsp
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="">
                <a href="admin_client_dashboard.php?page=ajout_produit_devis&id=<?= $devis_id ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
                <a href="admin_client_dashboard.php?page=&idliste_devis" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour aux devis
                </a>
            </div>
        </div>
    </div>
</div>x²
</body>
</html>
