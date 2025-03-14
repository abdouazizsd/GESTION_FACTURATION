<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si un ID de facture est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Facture non spécifiée.");
}

$facture_id = intval($_GET['id']);

// Récupérer les informations de la facture
$sql = "SELECT f.*, c.client_id, cl.nom AS client_nom, d.id AS devis_id 
        FROM factures f
        JOIN commandes c ON f.commande_id = c.id
        JOIN devis d ON c.devis_id = d.id
        JOIN clients cl ON c.client_id = cl.id
        WHERE f.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$facture_id]);
$facture = $stmt->fetch();

if (!$facture) {
    die("Facture introuvable.");
}

// Récupérer les produits du devis
$sql = "SELECT p.nom, dp.quantite, dp.prix_unitaire, (dp.quantite * dp.prix_unitaire) AS total 
        FROM devis_produits dp
        JOIN produits p ON dp.produit_id = p.id
        WHERE dp.devis_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$facture['devis_id']]);
$produits = $stmt->fetchAll();



$montant_total = $facture["montant_total"];
$montant_total_lettres = convertirEnLettres($montant_total);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?= htmlspecialchars($facture_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .table-primary {
            background-color: #007bff;
            color: white;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }
        .scrollable-container {
            max-height: 400px;
            overflow-y: auto;
            display: block;
        }
    </style>
</head>
<body class="bg-light">
<br><br>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow scrollable-container">
                <div class="card-header text-center">
                    Facture #<?= htmlspecialchars($facture_id) ?>
                </div>
                <div class="card-body">
                    <p><strong>Client :</strong> <?= htmlspecialchars($facture["client_nom"]) ?></p>
                    <p><strong>Date :</strong> <?= htmlspecialchars($facture["date_creation"]) ?></p>
                    <p><strong>Montant Total :</strong> <?= htmlspecialchars(number_format($montant_total, 2, ',', ' ')) ?> FCFA</p>
                    <p><strong>Montant en lettres :</strong> <?= htmlspecialchars($montant_total_lettres) ?> FCFA</p>
                    <p><strong>Statut :</strong> <?= htmlspecialchars($facture["statut"]) ?></p>

                    <h4 class="mt-4">Produits</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom du Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix Unitaire (FCFA)</th>
                                    <th>Total (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produits as $produit) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($produit["nom"]) ?></td>
                                        <td><?= htmlspecialchars($produit["quantite"]) ?></td>
                                        <td><?= htmlspecialchars(number_format($produit["prix_unitaire"], 2, ',', ' ')) ?></td>
                                        <td><?= htmlspecialchars(number_format($produit["total"], 2, ',', ' ')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary">
                                    <td colspan="3"><strong>Total :   <?= htmlspecialchars($montant_total_lettres) ?></strong></td>
                                    <td><strong><?= htmlspecialchars(number_format($montant_total, 2, ',', ' ')) ?> FCFA</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="admin_client_dashboard.php?page=ajout_produit_devis" class="btn btn-success">
                            <i class="fas fa-file-o"></i>&nbsp Imprimer  
                        </a>
                        <a href="admin_client_dashboard.php?page=ajout_produit_devis" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour 
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
