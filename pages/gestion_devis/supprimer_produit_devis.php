<?php
session_start();
include '../../includes/config.php';
include '../../includes/verifier_acces.php';

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
    die("Vous ne pouvez pas supprimer un produit d'un devis validé.");
}

// Supprimer le produit du devis
$sql = "DELETE FROM devis_produits WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$produit_devis_id]);

// Recalculer le montant total du devis
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
?>
