<?php 
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nombre de factures par page
$facturesParPage = 10;

// Page actuelle
$pageActuelle = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calcul de l'offset
$offset = ($pageActuelle - 1) * $facturesParPage;

// Construction de la requête SQL avec conditions
$sql = "SELECT f.*, cl.nom AS client_nom 
        FROM factures f
        LEFT JOIN commandes c ON f.commande_id = c.id
        LEFT JOIN clients cl ON c.client_id = cl.id";

$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(cl.nom LIKE :search OR f.date_creation LIKE :search OR f.devis_id LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Ajout de la pagination
$sql .= " LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);

// Liaison des paramètres de recherche
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}

// Liaison des paramètres numériques correctement
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int) $facturesParPage, PDO::PARAM_INT);

try {
    $stmt->execute();
    $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des factures : " . $e->getMessage());
}

// Récupérer le nombre total de factures pour la pagination
$sqlTotal = "SELECT COUNT(*) 
             FROM factures f
             LEFT JOIN commandes c ON f.commande_id = c.id
             LEFT JOIN clients cl ON c.client_id = cl.id";

if (!empty($conditions)) {
    $sqlTotal .= " WHERE " . implode(" AND ", $conditions);
}

$stmtTotal = $pdo->prepare($sqlTotal);

// Liaison des paramètres de recherche pour la requête de comptage
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value, PDO::PARAM_STR);
}

try {
    $stmtTotal->execute();
    $totalFactures = $stmtTotal->fetchColumn();
} catch (PDOException $e) {
    die("Erreur lors du comptage des factures : " . $e->getMessage());
}

// Calcul du nombre total de pages
$totalPages = ceil($totalFactures / $facturesParPage);

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Factures</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .scrollable-container {
            max-height: 400px;
            overflow-y: auto;
            display: block;
        }
    </style>
</head>
<body>
    <br>
    <div class="col-md-12 mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary d-flex justify-content-between text-white">
                <h2 class="card-title text-center">Liste des factures</h2>
                <form method="GET" action="admin_client_dashboard.php" class="d-flex col-md-6">
                    <input type="hidden" name="page" value="liste_factures">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit" class="btn btn-success">Rechercher</button>
                    </div>
                </form>
            </div>
            <div class="card-body scrollable-container">
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead class="bg-info">
                            <tr>
                                <th class="text-center text-white bg-info">ID Facture</th>
                                <th class="text-center text-white bg-info">Client</th>
                                <th class="text-center text-white bg-info">Date</th>
                                <th class="text-center text-white bg-info">Montant Total</th>
                                <th class="text-center text-white bg-info">Statut</th>
                                <th class="text-center text-white bg-info">Voir</th>
                                <th class="text-center text-white bg-info">Modifier</th>
                                <th class="text-center text-white bg-info">Supprimer</th>
                                <th class="text-center text-white bg-info">Imprimer PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($factures as $facture) : ?>
                                <tr>
                                    <td class="text-center"><?= htmlspecialchars($facture['id']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($facture['client_nom']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($facture['date_creation']) ?></td>
                                    <td class="text-center"><?= number_format($facture['montant_total'], 2, ',', ' ') ?> FCFA</td>
                                    <td class="text-center"><?= htmlspecialchars($facture['statut']) ?></td>
                                    <td class="text-center">
                                        <a href="admin_client_dashboard.php?page=voir_facture&id=<?= $facture['id'] ?>" class="btn btn-sm btn-primary">
                                           <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="admin_client_dashboard.php?page=modifier_facture&id=<?= $facture['id'] ?>" class="btn btn-sm btn-warning">
                                           <i class="fas fa-edit"></i>
                                        </a> 
                                    </td> 
                                    <td class="text-center">
                                        <?php if ($facture['statut'] !== 'Payée') : ?>
                                            <a href="admin_client_dashboard.php?page=supprimer_facture&id=<?= $facture['id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Voulez-vous vraiment supprimer cette facture ?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else : ?>
                                            <span class="btn btn-danger disabled"><i class="fa-solid fa-xmark"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="admin_client_dashboard.php?page=export_facture&id=<?= $facture['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $pageActuelle) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=liste_factures&p=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="../../assets/bootstrap/js/bootsrap.min.js"></script>

</body>
</html>
