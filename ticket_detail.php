<?php
// ticket_detail.php - Détails d'un ticket
session_start();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: tickets.php');
    exit;
}

$id = $_GET['id'];

// Trouver le ticket
$ticket = null;
if (isset($_SESSION['tickets'])) {
    foreach ($_SESSION['tickets'] as $t) {
        if ($t['id'] === $id) {
            $ticket = $t;
            break;
        }
    }
}

if (!$ticket) {
    header('Location: tickets.php');
    exit;
}

// Fonction pour obtenir le nom du projet
function getProjectName($projectId) {
    $projects = [
        '1' => 'Projet Alpha',
        '2' => 'Projet Beta',
        '3' => 'Migration Cloud',
        '4' => 'Application Mobile'
    ];
    return $projects[$projectId] ?? 'Projet inconnu';
}

// Fonction pour formater la date
function formatDate($timestamp) {
    return date('d/m/Y H:i', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du ticket - <?php echo htmlspecialchars($ticket['title']); ?></title>
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
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="projects.php">Projets</a></li>
                <li><a href="search.php">Rechercher</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Détails du ticket</h2>
            <div class="detail">
                <h3><?php echo htmlspecialchars($ticket['title']); ?></h3>
                <p><strong>Description :</strong> <?php echo htmlspecialchars($ticket['description']); ?></p>
                <p><strong>Statut :</strong> <span class="badge <?php echo $ticket['status']; ?>"><?php echo ucfirst(str_replace('-', ' ', $ticket['status'])); ?></span></p>
                <p><strong>Priorité :</strong> <span class="priority-badge priority-<?php echo $ticket['priority']; ?>"><?php echo $ticket['priority'] === 'high' ? 'Haute' : ($ticket['priority'] === 'medium' ? 'Moyenne' : 'Faible'); ?></span></p>
                <p><strong>Projet :</strong> <a href="project_detail.php?id=<?php echo $ticket['project']; ?>" class="project-name"><?php echo getProjectName($ticket['project']); ?></a></p>
                <?php if ($ticket['billable']): ?>
                <p><strong>Facturable :</strong> <span class="billable-badge badge" style="background-color: #fff9c4; color: #f57f17;">Oui</span></p>
                <?php endif; ?>
                <p><strong>Date de création :</strong> <?php echo formatDate($ticket['createdAt']); ?></p>
                <a href="tickets.php" class="btn-details">Retour à la liste</a>
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