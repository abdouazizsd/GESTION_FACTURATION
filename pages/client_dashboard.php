<?php
session_start();
include '../includes/config.php';
include '../includes/verifier_acces.php';

// Vérifier si l'utilisateur est un client
if ($_SESSION['role'] !== 'client') {
    header('Location: ../includes/access_denied.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Client</title>
</head>
<body>
    <h2>Bienvenue, <?= htmlspecialchars($_SESSION['nom']) ?> !</h2>
    <p>Ce tableau de bord vous permet de gérer vos devis, commandes et factures.</p>
    
    <ul>
        <li><a href="gestion_devis/liste_devis.php">Voir mes devis</a></li>
        <li><a href="gestion_commandes/liste_commandes.php">Voir mes commandes</a></li>
        <li><a href="gestion_factures/liste_factures.php">Voir mes factures</a></li>
    </ul>
    
    <br>
    <a href="../auth/deconnexion.php">Déconnexion</a>
</body>
</html>
