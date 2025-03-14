<?php
ob_start();

// Vérifier si l'identifiant du produit est fourni
if (!isset($_GET['id'])) {
    die("Produit non spécifié.");
}

$id = $_GET['id'];

// Supprimer le produit
$sql = "DELETE FROM produits WHERE id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$id])) {
    $_SESSION['message'] = "Produit supprimé avec succès.";
} else {
    $_SESSION['message'] = "Erreur lors de la suppression.";
}

header("Location: admin_client_dashboard.php?page=liste_produits");
exit();

ob_end_flush(); 
?>
