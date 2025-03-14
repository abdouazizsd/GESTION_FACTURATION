<?php
// Démarre la temporisation de sortie
ob_start();

// Vérifie si une session est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si un ID de facture est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Facture non spécifiée.");
}

$facture_id = intval($_GET['id']);

try {
    // Connexion à la base de données
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupère les informations de la facture et du client
    $sql = "SELECT f.*, d.client_id, c.nom AS client_nom 
            FROM factures f
            LEFT JOIN devis d ON f.devis_id = d.id
            LEFT JOIN clients c ON d.client_id = c.id
            WHERE f.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$facture_id]);
    $facture = $stmt->fetch();

    if (!$facture) {
        die("Facture introuvable.");
    }

    // Récupère les produits associés à la facture
    $sql = "SELECT p.nom, df.quantite, df.prix_unitaire, (df.quantite * df.prix_unitaire) AS total 
            FROM devis_produits df
            JOIN produits p ON df.produit_id = p.id
            WHERE df.devis_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$facture['devis_id']]);
    $produits = $stmt->fetchAll();

    if (empty($produits)) {
        die("Aucun produit trouvé pour cette facture.");
    }

    // Récupère les informations de l'entreprise
    $sql = "SELECT * FROM entreprise WHERE id = 1";
    $stmt = $pdo->query($sql);
    $entreprise = $stmt->fetch();

    // Génère le PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, utf8_decode('Facture #') . $facture_id, 0, 1, 'C');
    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, utf8_decode($entreprise['nom']), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(190, 7, utf8_decode($entreprise['adresse']), 0, 1, 'C');
    $pdf->Cell(190, 7, "Tel: " . $entreprise['telephone'], 0, 1, 'C');
    $pdf->Cell(190, 7, "Email: " . $entreprise['email'], 0, 1, 'C');
    $pdf->Ln(10);

    // Informations du client
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(190, 10, "Client: " . utf8_decode($facture['client_nom']), 0, 1);
    $pdf->Cell(190, 10, "Date: " . htmlspecialchars($facture['date_creation']), 0, 1);
    $pdf->Cell(190, 10, "Statut: " . htmlspecialchars($facture['statut']), 0, 1);
    $pdf->Ln(10);

    // Tableau des produits
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(80, 10, utf8_decode('Produit'), 1);
    $pdf->Cell(30, 10, 'Quantité', 1);
    $pdf->Cell(40, 10, utf8_decode('Prix Unitaire (FCFA)'), 1);
    $pdf->Cell(40, 10, 'Total (FCFA)', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    foreach ($produits as $produit) {
        $pdf->Cell(80, 10, utf8_decode($produit['nom']), 1);
        $pdf->Cell(30, 10, htmlspecialchars($produit['quantite']), 1);
        $pdf->Cell(40, 10, number_format($produit['prix_unitaire'], 2, ',', ' '), 1);
        $pdf->Cell(40, 10, number_format($produit['total'], 2, ',', ' '), 1);
        $pdf->Ln();
    }

    // Total avec montant en chiffres et en lettres
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Montant Total :'), 1);
    $pdf->Cell(60, 10, number_format($facture['montant_total'], 2, ',', ' ') . ' FCFA', 1);
    $pdf->Cell(70, 10, utf8_decode(convertirEnLettres($facture['montant_total']) . " francs CFA"), 1);
    $pdf->Ln();

    // Nettoie la sortie et génère le PDF
    ob_end_clean();
    $pdf->Output('D', 'Facture_' . $facture_id . '.pdf');
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>