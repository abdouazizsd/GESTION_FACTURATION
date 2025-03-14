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
$sql = "SELECT * FROM factures WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$facture_id]);
$facture = $stmt->fetch();

if (!$facture) {
    die("Facture introuvable.");
}

// Vérifier si la facture est déjà payée
if ($facture['statut'] === 'Payée') {
    die("Impossible de modifier une facture déjà payée.");
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelle_date = $_POST['date_creation'];
    $nouveau_statut = $_POST['statut'];

    // Correction de la requête SQL
    $sql = "UPDATE factures SET date_creation = ?, statut = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nouvelle_date, $nouveau_statut, $facture_id]);

    header("Location:admin_client_dashboard.php?page=liste_factures");
    exit();
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la Facture #<?= htmlspecialchars($facture_id) ?></title>
</head>
<body>
    <h2>Modifier la Facture #<?= htmlspecialchars($facture_id) ?></h2>
    <form method="post">
        <label>Date de Facture :</label>
        <input type="date" name="date_creation" value="<?= htmlspecialchars($facture['date_creation']) ?>" required>
        <br>
        <label>Statut :</label>
        <select name="statut">
            <option value="Facturée" <?= $facture['statut'] === 'Facturée' ? 'selected' : '' ?>>Facturée</option>
            <option value="Payée" <?= $facture['statut'] === 'Payée' ? 'selected' : '' ?>>Payée</option>
        </select>
        <br>
        <button type="submit">Enregistrer</button>
    </form>
    <br>
    <a href="admin_client_dashboard.php?page=liste_factures">Retour à la liste des factures</a>
</body>
</html>
