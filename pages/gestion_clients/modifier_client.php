<?php
ob_start();
// Vérifier si une session n'est pas déjà active avant de l'initialiser
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../admin_client_dashboard.php');
    exit();
}

// Vérifier si un ID est passé en paramètre
if (!isset($_GET['id'])) {
    die("Client non spécifié.");
}

$client_id = $_GET['id'];

// Récupérer les infos du client et de l'utilisateur associé
$sql = "SELECT c.*, u.nom, u.email FROM clients c 
        JOIN utilisateurs u ON c.utilisateur_id = u.id WHERE c.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    die("Client introuvable.");
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];

    $sql = "UPDATE clients SET telephone = ?, adresse = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$telephone, $adresse, $client_id])) {
        $_SESSION['message'] = "Client modifié avec succès.";
        header("Location: admin_client_dashboard.php?page=liste_clients");
        exit();
    } else {
        echo "Erreur lors de la modification du client.";
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Client</title>
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
<body class="bg-light">
    <br><br><br>
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title text-center">Modifier le Client</h2>
            </div>
            <div class="card-body scrollable-container">
                <form class="form-group" method="post">
                    <p><strong>Nom :</strong> <?= htmlspecialchars($client['nom']) ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($client['email']) ?></p>

                    <label class="form-label">Téléphone :</label>
                    <input class="form-control" type="text" name="telephone" placeholder="Entrez votre téléphone" value="<?= htmlspecialchars($client['telephone']) ?>" required>

                    <label class="form-label">Adresse :</label>
                    <input class="form-control" type="text" name="adresse" placeholder="Entrez votre adresse" value="<?= htmlspecialchars($client['adresse']) ?>" required>

                    <button class="btn btn-primary mt-3" type="submit">Modifier</button>
                    <a href="admin_client_dashboard.php?page=liste_clients" class="btn btn-secondary mt-3">Retour à la liste des clients</a>
                </form>
                <br>
            </div>
        </div>
    </div>
    
    
</body>
</html>