<?php
//profile.php - profile en php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil utilisateur - Site de Ticketing</title>
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
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="settings.php">Paramètres</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Profil utilisateur</h2>
            <form action="#" method="post">
                <input type="text" placeholder="Nom" value="Utilisateur Exemple" required>
                <input type="email" placeholder="Email" value="user@example.com" required>
                <input type="password" placeholder="Nouveau mot de passe (optionnel)">
                <button type="submit">Mettre à jour</button>
            </form>
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