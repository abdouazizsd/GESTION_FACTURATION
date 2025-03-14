<?php
session_start();
include '../../includes/config.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Accès non autorisé.";
    header('Location: admin_client_dashboard.php?page=liste_clients');
    exit();
}

// Vérifier si l'ID est passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID client invalide.";
    header('Location: ladmin_client_dashboard.php?page=liste_clients');
    exit();
}

$client_id = (int)$_GET['id'];

try {
    // Supprimer le client
    $sql = "DELETE FROM clients WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $client_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Client supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression du client.";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur SQL : " . $e->getMessage();
}

// Redirection vers la liste des clients
header('Location: admin_client_dashboard.php?page=liste_clients');
exit();
?>
