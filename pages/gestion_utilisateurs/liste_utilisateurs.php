<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vérification du rôle admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/access_denied.php');
    exit();
}

// Nombre d'utilisateurs par page
$utilisateursParPage = 10;

// Page actuelle
$pageActuelle = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calcul de l'offset
$offset = ($pageActuelle - 1) * $utilisateursParPage;

// Construction de la requête SQL
$sql = "SELECT id, nom, email, role FROM utilisateurs";
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(nom LIKE :search OR email LIKE :search OR role LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $utilisateursParPage, PDO::PARAM_INT);
$stmt->execute();
$utilisateurs = $stmt->fetchAll();

// Récupérer le nombre total d'utilisateurs pour la pagination
$sqlTotal = "SELECT COUNT(*) FROM utilisateurs";
if (!empty($conditions)) {
    $sqlTotal .= " WHERE " . implode(" AND ", $conditions);
}
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value, PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalUtilisateurs = $stmtTotal->fetchColumn();

// Calcul du nombre total de pages
$totalPages = ceil($totalUtilisateurs / $utilisateursParPage);

ob_end_flush(); // Vide et envoie le buffer

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
    <div class="container scrollable-container">
        <div class="card shadow">
            <div class="card-header bg-primary d-flex justify-content-between text-white">
                <h2 class="card-title text-center">Liste des Utilisateurs</h2>
                <form method="GET" action="admin_client_dashboard.php" class="mb-3 d-flex col-md-6">
                    <input type="hidden" name="page" value="liste_utilisateurs">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit" class="btn btn-success">Rechercher</button>
                    </div>
                </form>
            </div>

            <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Nom</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Rôle</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($utilisateurs) > 0): ?>
                            <?php foreach ($utilisateurs as $user): ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($user['id']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['nom']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($user['role']) ?></td>
                                    <td class="text-center">
                                        <a href="admin_client_dashboard.php?page=modifier_utilisateur&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="admin_client_dashboard.php?page=supprimer_utilisateur&id=<?= $user['id'] ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-danger">Aucun utilisateur trouvé.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>


                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $pageActuelle) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=liste_utilisateurs&p=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <a href="admin_client_dashboard.php?page=ajouter_utilisateur" class="btn btn-success">
                    <i class="fas fa-plus"></i> Ajouter un Utilisateur
                </a>
            </div>
        </div>
    </div>
</body>
</html>