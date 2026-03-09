<?php
// projects.php - Gestion des projets avec formulaires fonctionnels
session_start();
// En cas de reset manuel, décommenter cette ligne:
// unset($_SESSION['projects']);
// Initialiser les projets si non présents
if (!isset($_SESSION['projects'])) {
    $_SESSION['projects'] = [
        [
            'id' => '1',
            'name' => 'Projet Alpha',
            'description' => 'Développement d\'une nouvelle plateforme de gestion de tickets avec interface moderne et fonctionnalités avancées.',
            'status' => 'actif'
        ],
        [
            'id' => '2',
            'name' => 'Projet Beta',
            'description' => 'Refonte complète du système d\'authentification et gestion des permissions utilisateurs.',
            'status' => 'actif'
        ],
        [
            'id' => '3',
            'name' => 'Migration Cloud',
            'description' => 'Migration de l\'infrastructure vers une solution cloud pour améliorer la scalabilité et les performances.',
            'status' => 'en-pause'
        ],
        [
            'id' => '4',
            'name' => 'Application Mobile',
            'description' => 'Création d\'une application mobile native pour iOS et Android permettant la gestion des tickets en mobilité.',
            'status' => 'termine'
        ]
    ];
}

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $status = $_POST['status'];

    if (!empty($name) && !empty($description) && !empty($status)) {
        $newProject = [
            'id' => uniqid(),
            'name' => $name,
            'description' => $description,
            'status' => $status
        ];
        array_unshift($_SESSION['projects'], $newProject);
        // Redirection pour éviter re-soumission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fonction pour supprimer un projet
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    $_SESSION['projects'] = array_filter($_SESSION['projects'], function($project) use ($id) {
        return $project['id'] !== $id;
    });
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projets - Site de Ticketing</title>
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
        <div class="projects-container">
            <!-- Liste des projets à gauche -->
            <section class="projects-list">
                <h2>Liste des projets</h2>
                
                <div id="projects-list">
                    <?php foreach ($_SESSION['projects'] as $project): ?>
                    <div class="project-item" data-status="<?php echo $project['status']; ?>">
                        <div class="project-header">
                            <h3 class="project-title"><?php echo htmlspecialchars($project['name']); ?></h3>
                            <span class="badge <?php echo $project['status']; ?>"><?php echo ucfirst(str_replace('-', ' ', $project['status'])); ?></span>
                        </div>
                        <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
                        <div class="project-actions">
                            <a href="project_detail.php?id=<?php echo $project['id']; ?>" class="btn-details">Voir détails</a>
                            <a href="?delete=<?php echo $project['id']; ?>" class="btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer ce projet ?')">Supprimer</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Formulaire à droite -->
            <section class="project-form">
                <h2>Créer un projet</h2>
                <form method="post" action="">
                    <input type="text" name="name" placeholder="Nom du projet" required>
                    <textarea name="description" placeholder="Description" rows="4" required></textarea>
                    <select name="status" required>
                        <option value="">Statut</option>
                        <option value="actif">Actif</option>
                        <option value="en-pause">En pause</option>
                        <option value="termine">Terminé</option>
                    </select>
                    <button type="submit">Créer le projet</button>
                </form>
            </section>
        </div>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>