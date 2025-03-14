<?php
session_start();
include '../includes/config.php';

if (isset($_POST['nom']) && isset($_POST['mot_de_passe'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $mot_de_passe = $_POST['mot_de_passe'];

    $sql = "SELECT * FROM utilisateurs WHERE nom = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['utilisateur_id'] = $user['id'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];
        
        // Redirection en fonction du rôle
        ob_start();
        if ($user['role'] == 'admin') {
            header('Location: ../pages/admin_client_dashboard.php?page=liste_factures');
        } else {
            header('Location: ../pages/client_dashboard.php');
        }
        ob_end_flush();
        exit();
    } else {
        $_SESSION['message'] = "Nom ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="card container mt-5">
        <div class="card-header mt-3 bg-info">   
            <h2 class="card-title  text-center text-white">Authentification</h2>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['message'])) : ?>
                <p class="error-message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <form class="form-container" method="post" action="">
                <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
                
                <button class="btn-custom" type="submit">Se connecter</button>
            </form>
            
            <p class="text-center mt-3">Pas encore inscrit ? <a href="inscription.php">Inscrivez-vous ici</a>.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous">
    </script>
</body>
</html>