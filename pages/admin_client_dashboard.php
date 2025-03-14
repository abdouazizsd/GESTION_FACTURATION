<?php
ob_start();
session_start();

include '../includes/config.php';
include '../includes/verifier_acces.php';
include 'gestion_factures/fonction_conversion.php'; // Inclure la fonction de conversion
// Inclure FPDF depuis le dossier lib
require(__DIR__ . '/../lib/fpdf/fpdf.php');


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Administrateur</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <!-- Lien vers le fichier CSS externe -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Icônes FontAwesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">


    <style>
        /* === HEADER === */
        .dashboard-header {
            height: 80px;
            background-color: #0056B3;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* === SIDEBAR === */
        .dashboard-sidebar {
            width: 60px; /* Largeur réduite */
            height: 100vh;
            background-color: rgb(40, 135, 231);
            color: white;
            position: fixed;
            top: 80px; /* Sous le header */
            left: 0;    
            overflow-x: hidden;
            transition: 0.3s;
        }

        /* Étendre la sidebar au survol */
        .dashboard-sidebar:hover {
            width: 200px;
        }

        .dashboard-sidebar ul {
            padding: 0;
            list-style: none;
            margin-top: 20px;
        }

        .dashboard-sidebar ul li {
            text-align: center;
            padding: 10px 0;
        }

        .dashboard-sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            font-size: 18px;
        }

        /* Icônes + labels */
        .dashboard-sidebar ul li a i {
            font-size: 20px;
            width: 40px;
        }

        /* Au survol, afficher le texte */
        .dashboard-sidebar:hover ul li a {
            justify-content: start;
            padding-left: 15px;
        }

        /* === CONTENU PRINCIPAL === */
        .dashboard-content {
            margin-left: 60px; /* Décalage par rapport à la sidebar */
            padding: 20px;
            transition: 0.3s;
        }

        /* Quand la sidebar est élargie */
        .dashboard-sidebar:hover ~ .dashboard-content {
            margin-left: 200px;
        }

        .logo {
            height: 60px;
            width: 60px;
            border-radius: 50%;
        }
        .scrollable-table {
            max-height: 400px; /* Hauteur max avant l'affichage du scroll */
            overflow-y: auto; /* Ajout de la scrollbar verticale */
            display: block;
        }

    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="dashboard-header fixed-top">
        <img src="../assets/images/logo2.png" alt="Logo" class="logo">
        <div>
            <a href="../auth/deconnexion.php" class="btn btn-danger">Déconnexion</a>
        </div>
    </header>
    
    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar" id="sidebar">
        <ul>
            <li><a href="admin_client_dashboard.php?page=liste_factures"><i class="fas fa-receipt"></i> <span>Factures</span></a></li>
            <li><a href="admin_client_dashboard.php?page=liste_commandes"><i class="fas fa-shopping-cart"></i> <span>Commandes</span></a></li>
            <li><a href="admin_client_dashboard.php?page=liste_devis"><i class="fas fa-file-alt"></i> <span>Devis</span></a></li>
            <li><a href="admin_client_dashboard.php?page=liste_produits"><i class="fas fa-box"></i> <span>Produits</span></a></li>
            <li><a href="admin_client_dashboard.php?page=liste_clients"><i class="fas fa-user-tie"></i> <span>Clients</span></a></li>
            <li><a href="admin_client_dashboard.php?page=liste_utilisateurs"><i class="fas fa-users"></i> <span>Utilisateurs</span></a></li>
        </ul>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <main class="dashboard-content">
        <?php
            // Liste des pages autorisées
            $pages_autorisees = [
                'dashboard' => '../pages/admin_client_dashboard.php',

                'liste_utilisateurs' => '../pages/gestion_utilisateurs/liste_utilisateurs.php',
                'ajouter_utilisateur' => '../pages/gestion_utilisateurs/ajouter_utilisateur.php',
                'supprimer_utilisateur' => '../pages/gestion_utilisateurs/supprimer_utilisateur.php',
                'modifier_utilisateur' => '../pages/gestion_utilisateurs/modifier_utilisateur.php',

                'liste_devis' => '../pages/gestion_devis/liste_devis.php',
                'ajouter_devis' => '../pages/gestion_devis/ajouter_devis.php',
                'modifier_devis' => '../pages/gestion_devis/modifier_devis.php',
                'modifier_prod_devis' => '../pages/gestion_devis/modifier_prodtuit_devis.php',
                'supprimer_prod_devis' => '../pages/gestion_devis/supprimer_prodtuit_devis.php',
                'supprimer_devis' => '../pages/gestion_devis/supprimer_devis.php',
                'voir_devis' => '../pages/gestion_devis/voir_devis.php',

                'liste_clients' => '../pages/gestion_clients/liste_clients.php',
                'ajouter_client' => '../pages/gestion_clients/ajouter_client.php',
                'modifier_client' => '../pages/gestion_clients/modifier_client.php',
                'supprimer_client' => '../pages/gestion_clients/supprimer_client.php',
                
                'liste_commandes' => '../pages/gestion_commandes/liste_commandes.php',
                'ajouter_commande' => '../pages/gestion_commandes/ajouter_commande.php',
                'modifier_commande' => '../pages/gestion_commandes/modifier_commande.php',
                'supprimer_commande' => '../pages/gestion_commandes/supprimer_commande.php',
                'mod_statut_cde' => '../pages/gestion_commandes/modifier_statut_commande.php',
                'valider_commande' => '../pages/gestion_commandes/valider_commande.php',
                'voir_commande' => '../pages/gestion_commandes/voir_commande.php',

                'liste_produits' => '../pages/gestion_produits/liste_produits.php',
                'ajouter_produit' => '../pages/gestion_produits/ajouter_produit.php',
                'modifier_produit' => '../pages/gestion_produits/modifier_produit.php',
                'supprimer_produit' => '../pages/gestion_produits/supprimer_produit.php',
                'ajout_produit_devis' => '../pages/gestion_produits/ajouter_produit_devis.php',
                
                'liste_factures' => '../pages/gestion_factures/liste_factures.php',
                'ajouter_facture' => '../pages/gestion_factures/ajouter_facture.php',
                'modifier_facture' => '../pages/gestion_factures/modifier_facture.php',
                'supprimer_facture' => '../pages/gestion_factures/supprimer_facture.php',
                'voir_facture' => '../pages/gestion_factures/voir_facture.php',
                'export_facture' => '../pages/gestion_factures/export_facture_pdf.php'
                
            ];

            $page = htmlspecialchars($_GET['page'] ?? 'accueil');

            if (array_key_exists($page, $pages_autorisees)) {
                include $pages_autorisees[$page];
            } else {
                echo "<p class='alert alert-danger'>Page introuvable.</p>";
            }
        ?>
    </main>

    <!-- FOOTER -->
    <footer class="bg-primary text-white text-center py-3 mt-3" style="position: fixed; bottom: 0; width: 100%; height: 40px">
        <p>&copy; <?php echo date("Y"); ?> Gestion Facturation. Tous droits réservés.</p>
    </footer>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
