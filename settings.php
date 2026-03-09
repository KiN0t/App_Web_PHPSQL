<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Site de Ticketing</title>
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
                <li><a href="profile.php">Profil</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Paramètres</h2>
            <form action="#" method="post">
                <input type="email" placeholder="Email de notification" required>
                <select required>
                    <option value="">Langue</option>
                    <option value="fr">Français</option>
                    <option value="en">Anglais</option>
                </select>
                <button type="submit">Sauvegarder</button>
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