<?php
// project_detail.php - Détails d'un projet
session_start();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: projects.php');
    exit;
}

$id = $_GET['id'];

// Trouver le projet
$project = null;
if (isset($_SESSION['projects'])) {
    foreach ($_SESSION['projects'] as $p) {
        if ($p['id'] === $id) {
            $project = $p;
            break;
        }
    }
}

if (!$project) {
    header('Location: projects.php');
    exit;
}

// Fonction pour formater la date (si ajoutée plus tard)
function formatDate($timestamp) {
    return date('d/m/Y H:i', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du projet - <?php echo htmlspecialchars($project['name']); ?></title>
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
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="tickets.php">Tickets</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Détails du projet</h2>
            <div class="detail">
                <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                <p><strong>Description :</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                <p><strong>Statut :</strong> <span class="badge <?php echo $project['status']; ?>"><?php echo ucfirst(str_replace('-', ' ', $project['status'])); ?></span></p>
                <a href="projects.php" class="btn-details">Retour à la liste</a>
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