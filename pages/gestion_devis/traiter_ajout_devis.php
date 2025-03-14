<?php
session_start();
include '../includes/config.php';
include '../includes/verifier_acces.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = intval($_POST['client_id']);
    $date_creation = date('Y-m-d');
    
    // 1️⃣ Insérer le devis
    $sql = "INSERT INTO devis (client_id, date_creation, montant_total, statut) VALUES (?, ?, 0, 'En attente')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$client_id, $date_creation]);
    $devis_id = $pdo->lastInsertId();

    // 2️⃣ Ajouter les produits sélectionnés
    $montant_total = 0;
    if (!empty($_POST['produits'])) {
        $sql = "INSERT INTO devis_produits (devis_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($_POST['produits'] as $produit) {
            if (!empty($produit['id']) && !empty($produit['quantite'])) {
                $produit_id = intval($produit['id']);
                $quantite = intval($produit['quantite']);

                // Récupérer le prix du produit
                $sql_prix = "SELECT prix_unitaire FROM produits WHERE id = ?";
                $stmt_prix = $pdo->prepare($sql_prix);
                $stmt_prix->execute([$produit_id]);
                $prix_unitaire = $stmt_prix->fetchColumn();

                if ($prix_unitaire) {
                    $stmt->execute([$devis_id, $produit_id, $quantite, $prix_unitaire]);
                    $montant_total += $quantite * $prix_unitaire;
                }
            }
        }
    }

    // 3️⃣ Mettre à jour le montant total du devis
    $sql = "UPDATE devis SET montant_total = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$montant_total, $devis_id]);

    // Redirection après ajout
    header("Location: ../pages/gestion_devis/liste_devis.php");
    exit();
}
?>
