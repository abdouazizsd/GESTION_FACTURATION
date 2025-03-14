<?php
include '../../includes/config.php';

function mettreAJourMontantDevis($pdo, $devis_id) {
    // Calculer le nouveau total à partir des produits du devis
    $sql = "SELECT SUM(quantite * prix_unitaire) AS total FROM devis_produits WHERE devis_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$devis_id]);
    $result = $stmt->fetch();

    $nouveau_total = $result['total'] ?? 0;

    // Mettre à jour le montant total dans la table devis
    $sql = "UPDATE devis SET montant_total = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nouveau_total, $devis_id]);
}
?>
