<header class="dashboard-header">
    <button class="menu-toggle" onclick="toggleSidebar()">&#9776;</button>
    <h1>Tableau de Bord - Administrateur</h1>
    <a><?= htmlspecialchars($_SESSION['nom']) ?></a>
    <a href="../auth/deconnexion.php" class="logout-link">Déconnexion</a>
</header>
