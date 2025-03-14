<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si un devis est spécifié
$devis_id = isset($_GET['devis_id']) ? intval($_GET['devis_id']) : 0;
if ($devis_id <= 0) {
    die("Devis non spécifié ou ID invalide.");
}

// Récupérer la liste des produits
$sql = "SELECT id, nom, prix_unitaire FROM produits";
$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produit_id = intval($_POST['produit_id']);
    $quantite = intval($_POST['quantite']);

    // Récupérer le prix du produit
    $sql = "SELECT prix_unitaire FROM produits WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$produit_id]);
    $produit = $stmt->fetch();

    if (!$produit) {
        die("Produit introuvable.");
    }

    $prix_unitaire = $produit['prix_unitaire'];

    // Insérer le produit dans le devis
    $sql = "INSERT INTO devis_produits (devis_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$devis_id, $produit_id, $quantite, $prix_unitaire])) {
        header("Location: admin_client_dashboard.php?page=liste_devis&devis_id=" . $devis_id);
        exit();
    } else {
        echo "Erreur lors de l'ajout du produit au devis.";
    }
}

ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit au devis</title>
</head>
<body>
    <br><br><br>
    <div class="card">
        <div class="card-header bg-primary text-center text-white">
            <h2>Ajouter un produit au devis <?= htmlspecialchars($devis_id) ?></h2>
        </div>
       <div class="card-body">
           <form class="form-group" method="post">
               <label  class="form-label" for="produit_id">Produit :</label>
               <select class="form-control" name="produit_id" id="produit_id" required>
                   <option value="">Sélectionner un produit</option>
                   <?php foreach ($produits as $produit) : ?>
                       <option value="<?= $produit['id'] ?>" data-prix="<?= $produit['prix_unitaire'] ?>">
                           <?= htmlspecialchars($produit['nom']) ?> - <?= htmlspecialchars($produit['prix_unitaire']) ?> FCFA
                       </option>
                   <?php endforeach; ?>
               </select>
       
               <label class="form-label" for="quantite">Quantité :</label>
               <input class="form-control" type="number" name="quantite" id="quantite" min="1" required>
       
               <button class="btn btn-primary mt-3" type="submit">Ajouter</button>
               <a class="btn btn-secondary mt-3" href="admin_client_dashboard.php?page=liste_devis&devis_id=<?= $devis_id ?>">Retour à la liste des produits du devis</a>
           </form>
       </div>
    </div>
    

</body>
</html>
