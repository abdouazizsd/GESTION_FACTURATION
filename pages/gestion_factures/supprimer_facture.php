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

// Vérifier si la facture existe et son statut
$sql = "SELECT statut FROM factures WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$facture_id]);
$facture = $stmt->fetch();

if (!$facture) {
    die("Facture introuvable.");
}

// Vérifier que la facture n'est pas déjà payée
if ($facture['statut'] === 'Payée') {
    die("Impossible de supprimer une facture déjà payée.");
}

// Supprimer la facture
$sql = "DELETE FROM factures WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$facture_id]);

// Redirection après suppression
header("Location: admin_client_dashboard.php?page=liste_factures");
exit();

ob_end_flush();
?>
