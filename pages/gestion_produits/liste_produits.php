<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ../../auth/connexion.php');
    exit();
}

// Nombre de produits par page
$produitsParPage = 10;

// Page actuelle
$pageActuelle = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calcul de l'offset
$offset = ($pageActuelle - 1) * $produitsParPage;

// Construction de la requête SQL
$sql = "SELECT * FROM produits";
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(nom LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY id DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $produitsParPage, PDO::PARAM_INT);
$stmt->execute();
$produits = $stmt->fetchAll();

// Récupérer le nombre total de produits pour la pagination
$sqlTotal = "SELECT COUNT(*) FROM produits";
if (!empty($conditions)) {
    $sqlTotal .= " WHERE " . implode(" AND ", $conditions);
}
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value, PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalProduits = $stmtTotal->fetchColumn();

// Calcul du nombre total de pages
$totalPages = ceil($totalProduits / $produitsParPage);

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Produits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <div class="container col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary d-flex justify-content-between text-white">
                <h2 class="card-title text-center">Liste des Produits</h2>
                <form method="GET" action="admin_client_dashboard.php" class="mb-3 d-flex col-md-6">
                    <input type="hidden" name="page" value="liste_produits">
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
                                <th class="text-center">Nom</th>
                                <th class="text-center">Description</th>
                                <th class="text-center">Prix (FCFA)</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($produits)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun produit trouvé.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($produits as $produit) : ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($produit['id']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($produit['nom']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($produit['description']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($produit['prix_unitaire']) ?> FCFA</td>
                                        <td class="text-center"><?= htmlspecialchars($produit['stock']) ?></td>
                                        <td class="text-center">
                                            <a href="admin_client_dashboard.php?page=modifier_produit&id=<?= $produit['id'] ?>" class="btn btn-sm btn-warning me-2 text-white">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                            <a href="admin_client_dashboard.php?page=supprimer_produit&id=<?= $produit['id'] ?>" class="btn btn-sm btn-danger text-white" onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?')">
                                                <i class="fas fa-trash"></i> Supprimer
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
                                    <a class="page-link" href="?page=liste_produits&p=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <a href="admin_client_dashboard.php?page=ajouter_produit" class="btn btn-success btn-custom">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts pour Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>