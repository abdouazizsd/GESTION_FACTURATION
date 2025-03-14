<?php
ob_start();

ini_set('display_errors', 0); // Désactive l'affichage des erreurs
error_reporting(E_ALL & ~E_NOTICE); // Masque les notices

// Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../admin_client_dashboard.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM utilisateurs WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "Utilisateur supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression de l'utilisateur.";
    }
    header('Location: admin_client_dashboard.php?page=liste_utilisateurs');
    exit();
}

ob_end_flush(); // Vide et envoie le buffer

?>
