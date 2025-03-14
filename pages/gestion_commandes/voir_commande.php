<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'extension intl est activée
if (!class_exists('NumberFormatter')) {
    die("L'extension PHP 'intl' est requise pour convertir les nombres en lettres.");
}

// Fonction pour convertir les nombres en lettres
function nombreEnLettres($nombre) {
    $formatter = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
    return ucfirst($formatter->format($nombre));
}

// Vérifier si un ID de commande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Commande non spécifiée.");
}

$commande_id = intval($_GET['id']);

// Récupérer les informations de la commande et du devis associé
$sql = "SELECT c.*, d.id AS devis_id, d.montant_total, cl.nom AS client_nom 
        FROM commandes c
        JOIN devis d ON c.devis_id = d.id
        JOIN clients cl ON c.client_id = cl.id
        WHERE c.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$commande_id]);
$commande = $stmt->fetch();

if (!$commande) {
    die("Commande introuvable.");
}

$devis_id = $commande['devis_id'];

// Récupérer les produits du devis associé
$sql = "SELECT p.nom, dp.quantite, dp.prix_unitaire, (dp.quantite * dp.prix_unitaire) AS total 
        FROM devis_produits dp
        JOIN produits p ON dp.produit_id = p.id
        WHERE dp.devis_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$produits = $stmt->fetchAll();

ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Commande #<?= htmlspecialchars($commande_id) ?></title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body class="bg-light">
    <div class="container">
        <h2>Détails de la Commande #<?= htmlspecialchars($commande_id) ?></h2>

        <p><strong>Client :</strong> <?= htmlspecialchars($commande['client_nom']) ?></p>
        <p><strong>Montant Total :</strong> <?= number_format($commande['montant_total'], 2, ',', ' ') ?> FCFA</p>
        <p><strong>Statut :</strong> <?= htmlspecialchars($commande['statut']) ?></p>
        <p><strong>Devis associé :</strong> <?= "Devis #" . htmlspecialchars($devis_id) ?></p>

        <h3>Produits du Devis (#<?= htmlspecialchars($devis_id) ?>)</h3>
        <table border="1">
            <tr>
                <th>Nom du Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire (FCFA)</th>
                <th>Total (FCFA)</th>
            </tr>
            <?php foreach ($produits as $produit) : ?>
                <tr>
                    <td><?= htmlspecialchars($produit["nom"]) ?></td>
                    <td><?= htmlspecialchars($produit["quantite"]) ?></td>
                    <td><?= number_format($produit["prix_unitaire"], 2, ',', ' ') ?></td>
                    <td><?= number_format($produit["total"], 2, ',', ' ') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total du Devis :</strong></td>
                <td><strong><?= number_format($commande['montant_total'], 2, ',', ' ') ?> FCFA</strong></td>
            </tr>
            <tr>
                <td colspan="4"><strong>Montant en lettres :</strong> <?= nombreEnLettres($commande['montant_total']) ?> FCFA</td>
            </tr>
        </table>

        <br>
        <a href="admin_client_dashboard.php?page=liste_commandes">Retour à la liste des commandes</a>
    </div>
</body>
</html>
