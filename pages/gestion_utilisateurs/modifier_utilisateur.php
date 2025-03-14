<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0); // Désactive l'affichage des erreurs
error_reporting(E_ALL & ~E_NOTICE); // Masque les notices

// Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../admin_client_dashboard.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM utilisateurs WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $utilisateur = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $role = $_POST['role'];

    $sql = "UPDATE utilisateurs SET nom = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nom, $email, $role, $id])) {
        $_SESSION['message'] = "Utilisateur modifié avec succès.";
        header('Location: admin_client_dashboard.php?page=liste_utilisateurs');
        exit();
    } else {
        $_SESSION['message'] = "Erreur lors de la modification de l'utilisateur.";
    }
}

ob_end_flush(); // Vide et envoie le buffer

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Utilisateur</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">

</head>
<body>
    <br>
    <div class="col-md-12 mt-5">
    <div class="card mt-5">
        <div class="card-header bg-primary text-white text-center">
            <h2>Modifier un Utilisateur</h2>
        </div>
        <div class="card-body">
            <form class="form-group" method="post" action="">
                <input class="form-control" type="hidden" name="id" value="<?= $utilisateur['id'] ?>">
                <label class="form-label">Nom :</label>
                <input class="form-control" type="text" name="nom" value="<?= $utilisateur['nom'] ?>" required><br>
                <label class="form-label">Email :</label>
                <input class="form-control" type="email" name="email" value="<?= $utilisateur['email'] ?>" required><br>
                <label>Rôle :</label>
                <select class="form-control" name="role" required>
                    <option value="admin" <?= $utilisateur['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="client" <?= $utilisateur['role'] == 'client' ? 'selected' : '' ?>>Client</option>
                </select><br>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-edit "></i>&nbsp Modifier
                </button>
                <a class="btn btn-secondary" href="admin_client_dashboard.php?page=liste_utilisateurs">
                 <i class="fas fa-arrow-left"></i>&nbsp Retour
                </a>
            </form>

        </div>
    </div>
</div>
    
</body>
</html>
