<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé</title>
</head>
<body>
    <h2>Accès Refusé</h2>
    <p>Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
    <br>
    <a href="javascript:history.back()">Retour</a>
    <br>
    <a href="../auth/deconnexion.php">Déconnexion</a>
</body>
</html>
