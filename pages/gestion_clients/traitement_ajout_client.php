<?php
session_start();
include '../../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['message'] = "Accès non autorisé.";
        header('Location: ajouter_client.php');
        exit();
    }

    $utilisateur_id = $_POST['utilisateur_id'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';

    // Vérifier que tous les champs sont remplis
    if (empty($utilisateur_id) || empty($telephone) || empty($adresse)) {
        $_SESSION['message'] = "Tous les champs sont requis.";
        header('Location: ajouter_client.php');
        exit();
    }

    // Vérifier si l'utilisateur est déjà client
    $sql_check = "SELECT id FROM clients WHERE utilisateur_id = :utilisateur_id";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindParam(':utilisateur_id', $utilisateur_id);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        $_SESSION['message'] = "Cet utilisateur est déjà un client.";
        header('Location: ajouter_client.php');
        exit();
    }

    try {
        $sql = "INSERT INTO clients (utilisateur_id, telephone, adresse ,nom, email) 
                VALUES (:utilisateur_id, :telephone, :adresse, :nom, :email)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':utilisateur_id', $utilisateur_id);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Client ajouté avec succès.";
            header('Location: liste_clients.php');
            exit();
        } else {
            $_SESSION['message'] = "Erreur lors de l'ajout du client.";
            header('Location: ajouter_client.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur SQL : " . $e->getMessage();
        header('Location: ajouter_client.php');
        exit();
    }
}
?>
