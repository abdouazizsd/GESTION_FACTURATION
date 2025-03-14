<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../admin_client_dashboard.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix_unitaire = $_POST['prix_unitaire'];
    $stock = $_POST['stock'];

    if (!empty($nom) && !empty($prix_unitaire) && !empty($stock)) {
        $sql = "INSERT INTO produits (nom, description, prix_unitaire, stock) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nom, $description, $prix_unitaire, $stock])) {
            $_SESSION['message'] = "Produit ajouté avec succès.";
            header("Location: admin_client_dashboard.php?page=liste_produits");
            exit();
        } else {
            $error = "Erreur lors de l'ajout du produit.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Ajout de jQuery -->
    <style>
        .scrollable-container {
            max-height: 400px; /* Hauteur max avant l'affichage du scroll */
            overflow-y: auto; /* Ajout de la scrollbar verticale */
            display: block;
        }
    </style>
</head>
<body>
    <br><br><br>
    <div class="card mt-3 scrollable-container">
        <div class="card-header bg-primary text-center text-white">
            <h2>Ajouter un Produit</h2>
        </div>
        <div class="card-body">
            <?php if (isset($error)) : ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        
            <form class="form-group" method="post">
                <label class="form-label" >Nom du produit :</label>
                <input class="form-control" type="text" name="nom" placeholder="Nom du produit _ _ _" required>
        
                <label class="form-label">Description :</label>
                <textarea class="form-control" name="description" placeholder="Description  _ _ _ "></textarea>
        
                <label class="form-label" >Prix (FCFA) :</label>
                <input class="form-control" type="number" name="prix_unitaire" step="0.01" required>
        
                <label class="form-label">Stock :</label>
                <input class="form-control" type="number" name="stock" required>
        
                <button class="btn btn-primary mt-3" type="submit">Ajouter</button>
                <a class="btn btn-secondary mt-3" href="admin_client_dashboard.php?page=liste_produits">Voir la liste des produits</a>
            </form>
        </div>
    </div>

    <br>

