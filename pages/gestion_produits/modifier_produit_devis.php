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

// Récupérer les informations du produit dans le devis
$sql = "SELECT dp.*, p.nom FROM devis_produits dp 
        JOIN produits p ON dp.produit_id = p.id 
        WHERE dp.id = ? AND dp.devis_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$produit_id, $devis_id]);
$produit = $stmt->fetch();

if (!$produit) {
    die("Produit introuvable dans ce devis.");
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantite = intval($_POST['quantite']);
    $prix_unitaire = floatval($_POST['prix_unitaire']);

    if ($quantite <= 0 || $prix_unitaire <= 0) {
        echo "Quantité et prix unitaire doivent être supérieurs à zéro.";
    } else {
        // Mise à jour du produit dans le devis
        $sql = "UPDATE devis_produits SET quantite = ?, prix_unitaire = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$quantite, $prix_unitaire, $produit_id])) {
            header("Location: ../gestion_devis/liste_produits_devis.php?devis_id=$devis_id");
            exit();
        } else {
            echo "Erreur lors de la modification.";
        }
    }
}

ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Produit du Devis</title>
</head>
<body>
    <h2>Modifier Produit dans le Devis #<?= htmlspecialchars($devis_id) ?></h2>
    
    <form method="post">
        <p><strong>Produit :</strong> <?= htmlspecialchars($produit['nom']) ?></p>
        <label>Quantité :</label>
        <input type="number" name="quantite" value="<?= htmlspecialchars($produit['quantite']) ?>" required>
        
        <label>Prix Unitaire (FCFA) :</label>
        <input type="number" step="0.01" name="prix_unitaire" value="<?= htmlspecialchars($produit['prix_unitaire']) ?>" required>
        
        <br><br>
        <button type="submit">Modifier</button>
    </form>

    <br>
    <a href="../gestion_devis/liste_produits_devis.php?devis_id=<?= $devis_id ?>">Retour à la liste</a>c
</body>
</html>
