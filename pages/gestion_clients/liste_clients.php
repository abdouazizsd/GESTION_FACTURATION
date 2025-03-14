<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nombre de clients par page
$clientsParPage = 5;
$pageActuelle = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($pageActuelle - 1) * $clientsParPage;

// Récupérer les paramètres de recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search) && !preg_match('/^[a-zA-Z0-9\s\-@\.]+$/', $search)) {
    die("La recherche contient des caractères non autorisés.");
}

// Construire la requête SQL
$sql = "SELECT id, nom, adresse, email, telephone, date_creation FROM clients";
$conditions = [];
$params = [];

// Recherche par nom, email, téléphone ou adresse
if (!empty($search)) {
    $conditions[] = "(nom LIKE :search OR email LIKE :search OR telephone LIKE :search OR adresse LIKE :search)";
    $params[':search'] = "%$search%";
}

// Ajouter les conditions à la requête
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Ajouter la pagination à la requête
$sql .= " LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);

// Liaison des paramètres de recherche
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}

// Liaison correcte des paramètres de pagination
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $clientsParPage, PDO::PARAM_INT);

// Exécution de la requête
try {
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des clients : " . $e->getMessage());
}

// Récupérer le nombre total de clients pour la pagination
$sqlTotal = "SELECT COUNT(*) FROM clients";
if (!empty($conditions)) {
    $sqlTotal .= " WHERE " . implode(" AND ", $conditions);
}

$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value, PDO::PARAM_STR);
}

try {
    $stmtTotal->execute();
    $totalClients = $stmtTotal->fetchColumn();
} catch (PDOException $e) {
    die("Erreur lors du comptage des clients : " . $e->getMessage());
}

$totalPages = ceil($totalClients / $clientsParPage);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Clients</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .scrollable-container {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light mt-3">
    <div class="container">
        <div class="card mt-5 shadow bg-white scrollable-container">
            <div class="card-header bg-primary d-flex justify-content-between text-white">
                <h2 class="card-title text-center">Liste des clients</h2>
                <form method="GET" action="admin_client_dashboard.php" class="d-flex col-md-6">
                    <input type="hidden" name="page" value="liste_clients">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                        <button type="submit" class="btn btn-success">Rechercher</button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Nom</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Téléphone</th>
                                <th class="text-center">Adresse</th>
                                <th class="text-center">Date de Création</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clients)) : ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucun client trouvé.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($clients as $client) : ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($client['id']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($client['nom']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($client['email']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($client['telephone']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($client['adresse']) ?></td>
                                        <td class="text-center"><?= date("d/m/Y H:i", strtotime($client['date_creation'])) ?></td>
                                        <td class="text-center">
                                            <a href="admin_client_dashboard.php?page=modifier_client&id=<?= $client['id'] ?>" class="btn btn-sm btn-warning text-white">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                            <a href="admin_client_dashboard.php?page=supprimer_client&id=<?= htmlspecialchars($client['id']) ?>&csrf_token=<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" 
                                               onclick="return confirm('Êtes-vous sûr ?');" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
                                    <a class="page-link" href="?page=liste_clients&p=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <!-- Bouton Ajouter un client -->
                <a href="admin_client_dashboard.php?page=ajouter_client" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Ajouter un client
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>