<?php 
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['utilisateur_id'])) {
        header('Location: ../auth/connexion.php');
        exit();
    }

    // Vérifier les rôles et accès aux pages
    $role = $_SESSION['role'] ?? '';
    $page = basename($_SERVER['PHP_SELF']);

    $restrictions = [
        'admin' => [ // Accès total pour admin
            'admin_client_dashboard.php', 'liste_utilisateurs.php', 'ajouter_utilisateur.php', 
            'modifier_utilisateur.php', 'supprimer_utilisateur.php', 'liste_clients.php', 'ajouter_client.php',
            'voir_client.php', 'modifier_client.php', 'supprimer_client.php', 'liste_devis.php',
            'ajouter_devis.php','voir_devis.php', 'modifier_devis.php', 'supprimer_devis.php', 'liste_commandes.php',
            'ajouter_commande.php','voir_commande.php', 'modifier_commande.php', 'supprimer_commande.php', 
            'liste_factures.php', 'ajouter_facture.php', 'modifier_facture.php', 'supprimer_facture.php',
            'ajouter_produit.php', 'modifier_produit.php', 'supprimer_produit.php', 'liste_produits.php', 'ajouter_produit_devis.php',
            'liste_produits_devis.php', 'liste_produits_commande.php', 'modifier_produit_devis.php', 'modifier_produit_commande.php',
            'supprimer_produit_devis.php', 'supprimer_produit_commande.php', 'modifier_statut_commande.php', 'traiter_ajout_devis.php',
            'valider_commande.php', 'voir_facture.php', 'export_facture_pdf.php', 'exporter_commandes.php', 'exporter_clients.php',
        ],  
        'client' => [ // Accès restreint pour client
            'client_dashboard.php', 'liste_devis.php', 'voir_devis.php', 'liste_commandes.php', 'voir_commande.php'
        ],
    ];

    // Vérifier si l'utilisateur a le droit d'accéder à la page
    $access_granted = false;
    if (isset($restrictions[$role])) {
        if (in_array($page, $restrictions[$role])) {
            $access_granted = true;
        }
    }

    // Redirection si accès refusé
    if (!$access_granted) {
        header('Location: access_denied.php');
        exit();
    }
?>
