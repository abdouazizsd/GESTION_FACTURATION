<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../includes/access_denied.php');
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Aucune commande sélectionnée.";
    header("Location: admin_client_dashboard.php?page=liste_commandes");
    exit();
}

$commande_id = $_GET['id'];

$sql_delete = "DELETE FROM commandes WHERE id = ?";
$stmt_delete = $pdo->prepare($sql_delete);

if ($stmt_delete->execute([$commande_id])) {
    $_SESSION['message'] = "Commande supprimée avec succès.";
} else {
    $_SESSION['message'] = "Erreur lors de la suppression.";
}

header("Location: admin_client_dashboard.php?page=liste_commandes");
exit();

ob_end_flush();
?>
