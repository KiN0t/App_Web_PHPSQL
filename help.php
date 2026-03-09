<?php
//Je crois 1er degré j'utilise jamais cette page????
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - Site de Ticketing</title>
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
                <li><a href="home.php">Accueil</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Aide et FAQ</h2>
            <div class="list">
                <div class="item">
                    <h3>Comment créer un ticket ?</h3>
                    <p>Allez dans "Créer un ticket" et remplissez le formulaire.</p>
                </div>
                <div class="item">
                    <h3>Comment réinitialiser mon mot de passe ?</h3>
                    <p>Utilisez le lien "Mot de passe perdu" sur la page de connexion.</p>
                </div>
            </div>
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

---