<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'identifiant du produit est fourni
if (!isset($_GET['id'])) {
    die("Produit non spécifié.");
}

$id = $_GET['id'];

// Récupérer les informations du produit
$sql = "SELECT * FROM produits WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$produit = $stmt->fetch();

if (!$produit) {
    die("Produit introuvable.");
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix_unitaire = $_POST['prix_unitaire'];
    $stock = $_POST['stock'];

    $sql = "UPDATE produits SET nom = ?, description = ?, prix_unitaire = ?, stock = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$nom, $description, $prix_unitaire, $stock, $id])) {
        $_SESSION['message'] = "Produit modifié avec succès.";
        header("Location: admin_client_dashboard.php?page=liste_produits");
        exit();
    } else {
        echo "Erreur lors de la modification du produit.";
    }
}

ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Produit</title>
</head>
<body>
    <h2>Modifier Produit</h2>
    <form method="post">
        <label>Nom:</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" required><br>

        <label>Description:</label>
        <textarea name="description" required><?= htmlspecialchars($produit['description']) ?></textarea><br>

        <label>Prix unitaire (FCFA):</label>
        <input type="number" name="prix_unitaire" value="<?= htmlspecialchars($produit['prix_unitaire']) ?>" required><br>

        <label>Stock:</label>
        <input type="number" name="stock" value="<?= htmlspecialchars($produit['stock']) ?>" required><br>

        <button type="submit">Modifier</button>
    </form>
    <a href="admin_client_dashboard.php?page=liste_produits">Retour à la liste des produits</a>
</body>
</html>
