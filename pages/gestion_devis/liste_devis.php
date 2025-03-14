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

// Nombre de devis par page
$devisParPage = 10;

// Page actuelle
$pageActuelle = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calcul de l'offset
$offset = ($pageActuelle - 1) * $devisParPage;

// Construction de la requête SQL
$sql = "SELECT d.*, c.nom AS client_nom FROM devis d JOIN clients c ON d.client_id = c.id";
$conditions = [];
$params = [];

if ($_SESSION['role'] === 'client') {
    $conditions[] = "d.client_id = :client_id";
    $params[':client_id'] = $_SESSION['utilisateur_id'];
}

if (!empty($search)) {
    $conditions[] = "(c.nom LIKE :search OR d.statut LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY d.id DESC LIMIT :offset, :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $devisParPage, PDO::PARAM_INT);
$stmt->execute();
$devis = $stmt->fetchAll();

// Récupérer le nombre total de devis pour la pagination
$sqlTotal = "SELECT COUNT(*) FROM devis d JOIN clients c ON d.client_id = c.id";
if (!empty($conditions)) {
    $sqlTotal .= " WHERE " . implode(" AND ", $conditions);
}
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalDevis = $stmtTotal->fetchColumn();

// Calcul du nombre total de pages
$totalPages = ceil($totalDevis / $devisParPage);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Devis</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="container">
        <div class="card mt-5 shadow">
            <div class="card-header bg-primary d-flex justify-content-between text-white">
                <h2 class="card-title text-center">Liste des devis</h2>
                <form method="GET" action="admin_client_dashboard.php" class="mb-3 d-flex col-md-6">
                    <input type="hidden" name="page" value="liste_devis">
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
                            <?php if (empty($devis)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun devis trouvé.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($devis as $d) : ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($d['id']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($d['client_nom']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($d['date_creation']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($d['montant_total']) ?> FCFA</td>
                                        <td class="text-center"><?= htmlspecialchars($d['statut']) ?></td>
                                        <td class="text-center">
                                            <a href="admin_client_dashboard.php?page=voir_devis&id=<?= $d['id'] ?>" class="btn btn-sm btn-primary me-2">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <?php if ($d['statut'] === 'Validé') : ?>
                                                <a href="admin_client_dashboard.php?page=ajouter_commande&devis_id=<?= $d['id'] ?>" class="btn btn-sm btn-success me-2">
                                                    <i class="fas fa-plus"></i> Créer commande
                                                </a>
                                            <?php else : ?>
                                                <span class="btn btn-sm btn-secondary me-2">Non validé</span>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['role'] === 'admin') : ?>
                                                <a href="admin_client_dashboard.php?page=ajout_produit_devis&devis_id=<?= $d['id'] ?>" class="btn btn-sm btn-success me-2">
                                                    <i class="fas fa-plus"></i> Ajouter un produit
                                                </a>
                                                <a href="admin_client_dashboard.php?page=modifier_devis&id=<?= $d['id'] ?>" class="btn btn-sm btn-warning me-2">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                                <a href="admin_client_dashboard.php?page=supprimer_devis&id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce devis ?')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </a>
                                            <?php endif; ?>
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
                                    <a class="page-link" href="?page=liste_devis&p=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <a href="admin_client_dashboard.php?page=ajouter_devis" class="btn btn-success">
                    <i class="fas fa-plus"></i> Ajouter un devis
                </a>
            </div>
        </div>
    </div>
</body>
</html>