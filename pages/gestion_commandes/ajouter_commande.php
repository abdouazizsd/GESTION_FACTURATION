<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si un devis est spécifié
if (!isset($_GET['devis_id'])) {
    die("Devis non spécifié.");
}

$devis_id = $_GET['devis_id'];

// Récupérer les informations du devis
$sql = "SELECT * FROM devis WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$devis = $stmt->fetch();

if (!$devis) {
    die("Devis introuvable.");
}

// Vérifier si le devis est bien "Validé"
if ($devis['statut'] !== 'Validé') {
    die("Ce devis n'est pas validé. Vous ne pouvez pas créer de commande.");
}

// Liste des statuts possibles pour sécuriser l'entrée utilisateur
$statuts_possibles = ['En attente', 'En cours', 'Livrée'];

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $devis['client_id'];
    $montant_total = $devis['montant_total'];
    $statut_commande = $_POST['statut_commande'];

    // Vérifier si le statut sélectionné est valide
    if (!in_array($statut_commande, $statuts_possibles)) {
        die("Statut de commande invalide.");
    }

    // Insérer la commande avec le statut choisi
    $sql = "INSERT INTO commandes (client_id, devis_id, montant_total, statut) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$client_id, $devis_id, $montant_total, $statut_commande])) {
        $_SESSION['message'] = "Commande créée avec succès.";
        header("Location: admin_client_dashboard.php?page=liste_commandes");
        exit();
    } else {
        echo "Erreur lors de l'ajout de la commande.";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer une Commande</title>
</head>
<body class="bg-light">
    <h2>Créer une Commande à partir du Devis #<?= htmlspecialchars($devis_id) ?></h2>
    <form method="post">
        <p><strong>Client ID:</strong> <?= htmlspecialchars($devis['client_id']) ?></p>
        <p><strong>Montant Total:</strong> <?= htmlspecialchars($devis['montant_total']) ?> FCFA</p>

        <label for="statut_commande"><strong>Statut de la Commande :</strong></label>
        <select name="statut_commande" id="statut_commande" required>
            <option value="En cours">En cours</option>
            <option value="Validée">Validée</option>
            <option value="Livrée">Livrée</option>
        </select>

        <br><br>
        <button type="submit">Créer la Commande</button>
    </form>
    <br>
    <a href="../gestion_devis/liste_devis.php">Retour aux Devis</a>
</body>
</html>
