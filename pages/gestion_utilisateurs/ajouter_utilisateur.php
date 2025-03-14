<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
   // Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../admin_client_dashboard.php');
    exit();
}

// Récupérer la liste des utilisateurs
$sql = "SELECT id, nom, email, role FROM utilisateurs";
$stmt = $pdo->query($sql);
$utilisateurs = $stmt->fetchAll();

// Récupérer la liste des utilisateurs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nom, $email, $mot_de_passe, $role])) {
        $_SESSION['message'] = "Utilisateur ajouté avec succès.";
        header('Location: admin_client_dashboard.php?page=liste_utilisateurs');
        exit();
    } else {
        $_SESSION['message'] = "Erreur lors de l'ajout de l'utilisateur.";
    }
}
// Modifier un utilisateur
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
    <title>Ajouter un Utilisateur</title>
</head>
<body>
    <h2>Ajouter un Utilisateur</h2>
    <?php if (isset($_SESSION['message'])) : ?>
        <p><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Nom :</label>    
        <input type="text" name="nom" required><br>
        <label>Email :</label>
        <input type="email" name="email" required><br>
        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required><br>
        <label>Rôle :</label>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="client">Client</option>
        </select><br>
        <button type="submit">Ajouter</button>
    </form>
    <br>
    <a href="admin_client_dashboard.php?page=liste_utilisateurs">Retour à la liste des utilisateurs</a>
</body>
</html>
