<?php
session_start();
include '../includes/config.php';

if (
    isset($_POST['nom']) &&
    isset($_POST['email']) &&
    isset($_POST['mot_de_passe']) &&
    isset($_POST['role'])
) {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$nom, $email, $mot_de_passe, $role]);
        $_SESSION['message'] = "Inscription réussie. Vous pouvez vous connecter.";
        header('Location: connexion.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur lors de l'inscription: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="card container">
        <div class="card-header mt-3 bg-info">
            <h2 class="card-title">Inscription</h2>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['message'])) : ?>
                <p class="error-message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <form class="form-container" method="post" action="">
                <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>
                <input type="email" id="email" name="email" placeholder="Entrez votre email" required>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
                <select id="role" name="role">
                    <option value="admin">Veuillez selectionner un rôle</option>
                    <option value="admin">Admin</option>
                    <option value="client">Client</option>
                </select>
                
                <button class="btn-custom " type="submit">S'inscrire</button>
            </form>
            <p class="text-center mt-3">Déjà inscrit ? <a href="connexion.php">Connectez-vous ici</a>.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
</body>
</html>