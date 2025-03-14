<?php
ob_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ../../auth/connexion.php');
    exit();
}

// Nombre de commandes par page
$commandesParPage = 10;

// Page actuelle
$pageActuelle = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calcul de l'offset
$offset = ($pageActuelle - 1) * $commandesParPage;

// Construction de la requête SQL
$sql = "SELECT c.id, c.date_creation, c.montant_total, c.statut, cl.nom AS client_nom
        FROM commandes c
        JOIN clients cl ON c.client_id = cl.id";
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(cl.nom LIKE :search OR c.statut LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY c.id DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $commandesParPage, PDO::PARAM_INT);
$stmt->execute();
$commandes = $stmt->fetchAll();

// Récupérer le nombre total de commandes pour la pagination
$sqlTotal = "SELECT COUNT(*) FROM commandes c JOIN clients cl ON c.client_id = cl.id";
if (!empty($conditions)) {
    $sqlTotal .= " WHERE " . implode(" AND ", $conditions);
}
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value, PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalCommandes = $stmtTotal->fetchColumn();

// Calcul du nombre total de pages
$totalPages = ceil($totalCommandes / $commandesParPage);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .scrollable-container {
            max-height: 400px; /* Hauteur max avant l'affichage du scroll */
            overflow-y: auto; /* Ajout de la scrollbar verticale */
            display: block;
        }
    </style>
</head>
<body class="bg-light">
    <br>
    <div class="col-md-12 mt-5">
        <div class="card mt-3 shadow">
            <div class="card-header bg-primary d-flex justify-content-between text-white">
                <h2 class="card-title text-center">Liste des commandes</h2>
                <form method="GET" action="admin_client_dashboard.php" class="mb-3 d-flex col-md-6">
                    <input type="hidden" name="page" value="liste_commandes">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($search ?? '') ?>">
                        <button type="submit" class="btn btn-success">Rechercher</button>
                    </div>
                </form>
            </div>
            <div class="card-body scrollable-container">
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Client</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Montant Total</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($commandes)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune commande trouvée.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($commandes as $commande) : ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($commande['id']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($commande['client_nom']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($commande['date_creation']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($commande['montant_total']) ?> FCFA</td>
                                        <td class="text-center"><?= htmlspecialchars($commande['statut']) ?></td>
                                        <td class="text-center">
                                            <a href="admin_client_dashboard.php?page=voir_commande&id=<?= $commande['id'] ?>" class="btn btn-sm btn-primary me-2">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <a href="admin_client_dashboard.php?page=mod_statut_cde&id=<?= $commande['id'] ?>" class="btn btn-sm btn-warning me-2">
                                                <i class="fas fa-edit"></i> Modifier Statut
                                            </a>
                                            <a href="admin_client_dashboard.php?page=valider_commande&id=<?= $commande['id'] ?>" class="btn btn-sm btn-success me-2">
                                                <i class="fa-solid fa-circle-check"></i> Valider commande
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
                                    <a class="page-link" href="?page=liste_commandes&p=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</body>
</html>