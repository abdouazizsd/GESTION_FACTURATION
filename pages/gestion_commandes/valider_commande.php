<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si un ID de commande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Commande non spécifiée.");
}

$commande_id = intval($_GET['id']);

try {
    // Démarrer une transaction SQL
    $pdo->beginTransaction();

    // Vérifier si la commande existe et est "En cours"
    $sql = "SELECT * FROM commandes WHERE id = ? AND statut = 'En cours'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$commande_id]);
    $commande = $stmt->fetch();

    if (!$commande) {
        throw new Exception("Commande introuvable ou déjà validée.");
    }

    // Mettre à jour le statut de la commande en "Validée"
    $sql = "UPDATE commandes SET statut = 'Validée' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$commande_id]);

    // Vérifier si une facture existe déjà pour cette commande
    $sql = "SELECT * FROM factures WHERE commande_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$commande_id]);
    $facture_existante = $stmt->fetch();

    // Si aucune facture n'existe, en créer une
    if (!$facture_existante) {
        $sql = "INSERT INTO factures (commande_id, montant_total, date_creation, statut, devis_id) VALUES (?, ?, NOW(), 'Facturée', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commande_id, $commande['montant_total'], $commande['devis_id']]);
    }

    // Valider la transaction
    $pdo->commit();

    // Redirection vers la liste des commandes après validation
    header("Location: admin_client_dashboard.php?page=liste_commandes");
    exit;
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    die("Erreur : " . $e->getMessage());
}

ob_end_flush();
?>