<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Vérifier si un ID de commande est fourni
if (!isset($_GET['id'])) {
    die("Commande non spécifiée.");
}

$commande_id = $_GET['id'];

// Récupérer les infos de la commande
$sql = "SELECT * FROM commandes WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$commande_id]);
$commande = $stmt->fetch();

if (!$commande) {
    die("Commande introuvable.");
}

// Mise à jour de la commande
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $statut = $_POST['statut'];
    $montant_total = $_POST['montant_total'];

    $sql = "UPDATE commandes SET statut = ?, montant_total = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$statut, $montant_total, $commande_id])) {
        $_SESSION['message'] = "Commande mise à jour avec succès.";
        header("Location: admin_client_dashboard.php?page=liste_commandes");
        exit();
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Commande</title>
</head>
<body>
    <h2>Modifier la Commande #<?= htmlspecialchars($commande_id) ?></h2>
    <form method="post">
        <label>Montant Total (FCFA):</label>
        <input type="number" name="montant_total" value="<?= htmlspecialchars($commande['montant_total']) ?>" required>

        <label>Statut:</label>
        <select name="statut">
            <option value="En cours" <?= $commande['statut'] == 'En cours' ? 'selected' : '' ?>>En cours</option>
            <option value="Validée" <?= $commande['statut'] == 'Validée' ? 'selected' : '' ?>>Validée</option>
            <option value="Livrée" <?= $commande['statut'] == 'Livrée' ? 'selected' : '' ?>>Livrée</option>
        </select>

        <button type="submit">Enregistrer</button>
    </form>
    <a href="admin_client_dashboard.php?page=liste_commandes">Retour</a>
</body>
</html>
