<?php
ob_start();

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_client_dashboard.php');
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer et nettoyer les données du formulaire
    $utilisateur_id = $_POST['utilisateur_id'] ?? null;
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    // Vérification des champs obligatoires
    if (empty($utilisateur_id) || empty($telephone) || empty($adresse)) {
        $_SESSION['message'] = "Tous les champs sont obligatoires.";
        header("Location: admin_client_dashboard.php?page=ajouter_client");
        exit();
    }

    try {
        // Vérifier si l'utilisateur sélectionné existe et récupérer ses informations
        $stmt = $pdo->prepare("SELECT nom, email FROM utilisateurs WHERE id = :id AND role = 'client'");
        $stmt->bindParam(':id', $utilisateur_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['message'] = "Utilisateur invalide ou non client.";
            header("Location: admin_client_dashboard.php?page=ajouter_client");
            exit();
        }

        $nom = $user['nom'];
        $email = $user['email'];

        // Requête d'insertion sécurisée
        $sql = "INSERT INTO clients (utilisateur_id, nom, email, telephone, adresse) 
                VALUES (:utilisateur_id, :nom, :email, :telephone, :adresse)";
        $stmt = $pdo->prepare($sql);

        // Liaison des paramètres
        $stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':telephone', $telephone, PDO::PARAM_STR);
        $stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);

        // Exécution de la requête
        if ($stmt->execute()) {
            $_SESSION['message'] = "Client ajouté avec succès.";
            header("Location: admin_client_dashboard.php?page=liste_clients");
            exit();
        } else {
            $_SESSION['message'] = "Erreur lors de l'ajout du client.";
            header("Location: admin_client_dashboard.php?page=ajouter_client");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur SQL : " . $e->getMessage();
        header("Location: admin_client_dashboard.php?page=ajouter_client");
        exit();
    }
}

// Récupérer la liste des utilisateurs avec rôle "client"
try {
    $sql = "SELECT id, nom, email FROM utilisateurs WHERE role = 'client'";
    $stmt = $pdo->query($sql);
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
    $utilisateurs = []; // Initialiser un tableau vide en cas d'erreur
}

ob_end_flush();
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="card-title text-center">Ajouter un Client</h2>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="utilisateur_id" class="form-label">Utilisateur (Client) :</label>
                    <select name="utilisateur_id" id="utilisateur_id" class="form-control" required>
                        <option value="">Sélectionner un utilisateur</option>
                        <?php foreach ($utilisateurs as $user): ?>
                            <option value="<?= htmlspecialchars($user['id']) ?>">
                                <?= htmlspecialchars($user['nom']) ?> (<?= htmlspecialchars($user['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="telephone" class="form-label">Téléphone :</label>
                    <input type="text" name="telephone" id="telephone" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse :</label>
                    <input type="text" name="adresse" id="adresse" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">Ajouter</button>
                <a href="admin_client_dashboard.php?page=liste_client" class="btn btn-secondary">Retour</a>
            </form>
        </div>
    </div>
</div>

<script>
    // Fermer automatiquement l'alerte après 5 secondes
    setTimeout(() => {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
</script>
