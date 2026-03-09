<?php
// 404.php - Page d'erreur 404 en PHP
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - Site de Ticketing</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Site de Ticketing</h1>
        <nav>
            <div class="menu-toggle" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul id="nav-menu">
                <li><a href="home.php">Accueil</a></li>
                <li><a href="dashboard.php">Tableau de bord</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Erreur 404 - Page non trouvée</h2>
            <p>Désolé, la page que vous cherchez n'existe pas.</p>
            <a href="home.php"><button>Retour à l'accueil</button></a>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script>
        function toggleMenu() {
            document.getElementById('nav-menu').classList.toggle('show');
        }
    </script>
</body>
</html>

