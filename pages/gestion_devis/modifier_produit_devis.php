<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si les paramètres sont présents
if (!isset($_GET['id']) || !isset($_GET['devis_id'])) {
    die("Produit ou devis non spécifié.");
}

$produit_devis_id = intval($_GET['id']);
$devis_id = intval($_GET['devis_id']);

// Vérifier que le devis existe et récupérer son statut
$sql = "SELECT statut FROM devis WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$devis = $stmt->fetch();

if (!$devis) {
    die("Devis introuvable.");
}

// Vérifier si le devis est validé
if ($devis['statut'] === 'Validé') {
    die("Vous ne pouvez pas modifier un produit d'un devis validé.");
}

// Récupérer les infos du produit
$sql = "SELECT * FROM devis_produits WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$produit_devis_id]);
$produit_devis = $stmt->fetch();

if (!$produit_devis) {
    die("Produit introuvable.");
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantite = intval($_POST['quantite']);
    $prix_unitaire = floatval($_POST['prix_unitaire']);

    // Mise à jour du produit dans le devis
    $sql = "UPDATE devis_produits SET quantite = ?, prix_unitaire = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$quantite, $prix_unitaire, $produit_devis_id]);

    // Recalcul du montant total du devis
    $sql = "SELECT SUM(quantite * prix_unitaire) AS total FROM devis_produits WHERE devis_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$devis_id]);
    $total = $stmt->fetchColumn() ?: 0; // Si aucun produit restant, mettre 0

    // Mettre à jour le montant total dans la table devis
    $sql = "UPDATE devis SET montant_total = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$total, $devis_id]);

    // Redirection vers la page du devis
    header("Location: voir_devis.php?id=" . $devis_id);
    exit;
}
 ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Produit</title>
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
    <div class="card">
        <div class="card-header">
            <h2>Modifier Produit du Devis #<?= htmlspecialchars($devis_id) ?></h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <label>Quantité :</label>
                <input type="number" name="quantite" value="<?= htmlspecialchars($produit_devis['quantite']) ?>" required>
                
                <label>Prix Unitaire (FCFA) :</label>
                <input type="text" name="prix_unitaire" value="<?= htmlspecialchars($produit_devis['prix_unitaire']) ?>" required>
    
                <button type="submit">Modifier</button>
                <a href="voir_devis.php?id=<?= $devis_id ?>">Retour au devis</a>
            </form>
    </div>

</body>
</html>
