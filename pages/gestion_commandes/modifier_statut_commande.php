<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['role'] !== 'admin') {
    die("Accès refusé.");
}

if (!isset($_GET['id'])) {
    die("Commande non spécifiée.");
}

$commande_id = intval($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nouveau_statut = $_POST['statut'];

    $sql = "UPDATE commandes SET statut = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nouveau_statut, $commande_id])) {
        $_SESSION['message'] = "Statut mis à jour.";
        header("Location: admin_client_dashboard.php?page=liste_commandes");
        exit();
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}

// Récupérer le statut actuel
$sql = "SELECT statut FROM commandes WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$commande_id]);
$commande = $stmt->fetch();

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Statut de la Commande</title>
</head>
<body>
    <h2>Modifier le Statut de la Commande #<?= htmlspecialchars($commande_id) ?></h2>
    <form method="post">
        <label>Statut :</label>
        <select name="statut">
            <option value="En cours" <?= $commande['statut'] === 'En cours' ? 'selected' : '' ?>>En cours</option>
            <option value="Validée" <?= $commande['statut'] === 'Validée' ? 'selected' : '' ?>>Validée</option>
            <option value="Livrée" <?= $commande['statut'] === 'Livrée' ? 'selected' : '' ?>>Livrée</option>
        </select>
        <button type="submit">Mettre à jour</button>
    </form>
    <a href="admin_client_dashboard.php?page=liste_commandes">Retour</a>
</body>
</html>
