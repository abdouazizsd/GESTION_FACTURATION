<?php
session_start();
include '../../includes/config.php';
include '../../includes/verifier_acces.php';

// Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../includes/access_denied.php');
    exit();
}

// Vérifier si l'ID du devis est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Aucun devis sélectionné.";
    header("Location: admin_client_dashboard.php?page=liste_devis");
    exit();
}

$devis_id = $_GET['id'];

// Vérifier si le devis existe
$sql = "SELECT * FROM devis WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$devis = $stmt->fetch();

if (!$devis) {
    $_SESSION['message'] = "Devis introuvable.";
    header("Location: admin_client_dashboard.php?page=liste_devis");
    exit();
}

// Supprimer le devis
$sql_delete = "DELETE FROM devis WHERE id = ?";
$stmt_delete = $pdo->prepare($sql_delete);

if ($stmt_delete->execute([$devis_id])) {
    $_SESSION['message'] = "Devis supprimé avec succès.";
    header("Location: admin_client_dashboard.php?page=liste_devis");
} else {
    $_SESSION['message'] = "Erreur lors de la suppression du devis.";
    header("Location: admin_client_dashboard.php?page=liste_devis");
}

header("Location: admin_client_dashboard.php?page=liste_devis");
exit();
?>
