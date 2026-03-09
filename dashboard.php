<?php
// dashboard.php - Dashboard en PHP avec statistiques dynamiques
session_start();

// Initialiser les données si non présentes (simulation de base de données)
if (!isset($_SESSION['tickets'])) {
    $_SESSION['tickets'] = [
        [
            'id' => '1',
            'title' => 'Bug d\'affichage sur mobile',
            'status' => 'ouvert',
            'priority' => 'high',
            'project' => '1',
            'billable' => true,
            'createdAt' => time()
        ],
        [
            'id' => '2',
            'title' => 'Amélioration du dashboard',
            'status' => 'en-cours',
            'priority' => 'medium',
            'project' => '1',
            'billable' => false,
            'createdAt' => time()
        ],
        [
            'id' => '3',
            'title' => 'Correction du formulaire de connexion',
            'status' => 'ferme',
            'priority' => 'high',
            'project' => '2',
            'billable' => true,
            'createdAt' => time()
        ],
        [
            'id' => '4',
            'title' => 'Notification par email',
            'status' => 'ouvert',
            'priority' => 'low',
            'project' => '1',
            'billable' => false,
            'createdAt' => time()
        ]
    ];
}

if (!isset($_SESSION['projects'])) {
    $_SESSION['projects'] = [
        [
            'id' => '1',
            'name' => 'Projet Alpha',
            'status' => 'actif'
        ],
        [
            'id' => '2',
            'name' => 'Projet Beta',
            'status' => 'actif'
        ],
        [
            'id' => '3',
            'name' => 'Migration Cloud',
            'status' => 'en-pause'
        ],
        [
            'id' => '4',
            'name' => 'Application Mobile',
            'status' => 'termine'
        ]
    ];
}

// Calculer les statistiques
$totalTickets = count($_SESSION['tickets']);
$totalProjects = count($_SESSION['projects']);
$openTickets = count(array_filter($_SESSION['tickets'], function($ticket) {
    return $ticket['status'] === 'ouvert';
}));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Site de Ticketing</title>
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
                <li><a href="projects.php">Projets</a></li>
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="settings.php">Paramètres</a></li>
                <li><a href="index.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Tableau de bord</h2>
            <p>Bienvenue ! Voici un aperçu de vos activités.</p>
            <div class="list">
                <div class="item">
                    <h3>Tickets totaux</h3>
                    <p><?php echo $totalTickets; ?> tickets au total.</p>
                    <a href="tickets.php">Voir tous</a>
                </div>
                <div class="item">
                    <h3>Projets totaux</h3>
                    <p><?php echo $totalProjects; ?> projets au total.</p>
                    <a href="projects.php">Voir tous</a>
                </div>
                <div class="item">
                    <h3>Tickets ouverts</h3>
                    <p><?php echo $openTickets; ?> tickets en cours.</p>
                    <a href="tickets.php">Voir tous</a>
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