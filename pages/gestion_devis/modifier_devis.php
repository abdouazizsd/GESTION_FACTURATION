<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../includes/access_denied.php');
    exit();
}

// Vérifier si l'ID du devis est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Aucun devis sélectionné.";
    header("Location: admin_client_dashboard.php?page=liste_devis");
    exit();
}

$devis_id = $_GET['id'];

// Récupérer les informations du devis
$sql = "SELECT * FROM devis WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$devis_id]);
$devis = $stmt->fetch();

// Vérifier si le devis existe
if (!$devis) {
    $_SESSION['message'] = "Devis introuvable.";
    header("Location: admin_client_dashboard.php?page=liste_devis");
    exit();
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'];
    $statut = $_POST['statut'];

    $sql = "UPDATE devis SET client_id = ?, statut = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$client_id, $statut, $devis_id])) {
        $_SESSION['message'] = "Devis mis à jour avec succès.";
        header("Location: admin_client_dashboard.php?page=liste_devis");
        exit();
    } else {
        $_SESSION['message'] = "Erreur lors de la mise à jour.";
    }
}

// Récupérer la liste des clients
$sql_clients = "SELECT * FROM clients";
$stmt_clients = $pdo->query($sql_clients);
$clients = $stmt_clients->fetchAll();

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Devis</title>
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
<body class="bg-light mb-5"> ">
    <div class="card mt-5 mx-auto  " style="width: 100%;">
        <div class="card-header bg-primary text-white text-center">
            <h2>Modifier le Devis #<?= htmlspecialchars($devis["id"]) ?></h2>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['message'])) : ?>
                <p><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <form method="post" class="form-group">
                <label class="form-label">Client :</label>
                <select class="form-control" name="client_id" required>
                    <?php foreach ($clients as $client) : ?>
                        <option value="<?= $client['id'] ?>" <?= ($client['id'] == $devis['client_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label">Statut :</label>
                <select class="form-control mb-3" name="statut" >
                    <option value="En attente" <?= ($devis['statut'] == 'En attente') ? 'selected' : '' ?>>En attente</option>
                    <option value="Validé" <?= ($devis['statut'] == 'Validé') ? 'selected' : '' ?>>Validé</option>
                    <option value="Annulé" <?= ($devis['statut'] == 'Annulé') ? 'selected' : '' ?>>Annulé</option>
                </select>
                
                <button class="btn btn-primary"  type="submit">
                    <i class="fas fa-edit"></i>&nbsp Mettre à jour
                </button>
                <a href="admin_client_dashboard.php?page=liste_devis" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </form>
        </div>
    </div>
</body>
</html>

