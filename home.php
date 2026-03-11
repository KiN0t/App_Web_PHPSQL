<?php
// home.php - Page d'accueil en PHP  (sert à rien jcrois?)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Site de Ticketing</title>
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
                <li><a href="index.php">Connexion</a></li>
                <li><a href="signup.php">Inscription</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Bienvenue sur notre site de ticketing</h2>
            <p>Gérez vos projets et tickets efficacement. Connectez-vous pour commencer.</p>
            <a href="index.php"><button>Se connecter</button></a>
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