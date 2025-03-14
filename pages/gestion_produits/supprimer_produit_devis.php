<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../gestion_devis/maj_montant_devis.php';

mettreAJourMontantDevis($pdo, $devis_id);

// Vérifier si un produit et un devis sont spécifiés
$devis_id = isset($_GET['devis_id']) ? intval($_GET['devis_id']) : 0;
$produit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($devis_id <= 0 || $produit_id <= 0) {
    die("Devis ou produit non spécifié.");
}

// Vérifier si le produit existe bien dans le devis
$sql = "SELECT * FROM devis_produits WHERE id = ? AND devis_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$produit_id, $devis_id]);
$produit = $stmt->fetch();

if (!$produit) {
    die("Produit introuvable dans ce devis.");
}

// Suppression du produit du devis
$sql = "DELETE FROM devis_produits WHERE id = ?";
$stmt = $pdo->prepare($sql);
if ($stmt->execute([$produit_id])) {
    header("Location: ../gestion_devis/liste_produits_devis.php?devis_id=$devis_id");
    exit();
} else {
    echo "Erreur lors de la suppression.";
}

ob_end_flush(); 
?>
