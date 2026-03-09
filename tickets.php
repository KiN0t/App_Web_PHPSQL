<?php
// tickets.php - Gestion des tickets avec formulaires fonctionnels
session_start();

// Initialiser les projets si non présents (pour compatibilité avec projects.php)
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

// Initialiser les tickets si non présents
if (!isset($_SESSION['tickets'])) {
    $_SESSION['tickets'] = [
        [
            'id' => '1',
            'title' => 'Bug d\'affichage sur mobile',
            'description' => 'L\'interface ne s\'affiche pas correctement sur les appareils mobiles de moins de 375px de largeur.',
            'status' => 'ouvert',
            'priority' => 'high',
            'project' => '1',
            'billable' => true,
            'createdAt' => time()
        ],
        [
            'id' => '2',
            'title' => 'Amélioration du dashboard',
            'description' => 'Ajouter des graphiques interactifs pour visualiser les statistiques des tickets et projets.',
            'status' => 'en-cours',
            'priority' => 'medium',
            'project' => '1',
            'billable' => false,
            'createdAt' => time()
        ],
        [
            'id' => '3',
            'title' => 'Correction du formulaire de connexion',
            'description' => 'Le formulaire de connexion ne validait pas correctement les emails. Problème résolu.',
            'status' => 'ferme',
            'priority' => 'high',
            'project' => '2',
            'billable' => true,
            'createdAt' => time()
        ],
        [
            'id' => '4',
            'title' => 'Notification par email',
            'description' => 'Implémenter un système de notification par email lors de la création ou mise à jour d\'un ticket.',
            'status' => 'ouvert',
            'priority' => 'low',
            'project' => '1',
            'billable' => false,
            'createdAt' => time()
        ]
    ];
}

// Gestion du mode admin
$adminPassword = 'admin123';
$isAdminMode = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

// Traiter la soumission du mot de passe admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $adminPassword) {
        $_SESSION['admin'] = true;
        $isAdminMode = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $adminError = 'Mot de passe incorrect';
    }
}

// Toggle admin mode (désactivation)
if (isset($_GET['toggle_admin']) && $isAdminMode) {
    $_SESSION['admin'] = false;
    $isAdminMode = false;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Traiter la soumission du formulaire de ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $project = $_POST['project'];
    $priority = $_POST['priority'];
    $billable = isset($_POST['billable']) ? true : false;

    if (!empty($title) && !empty($description) && !empty($project) && !empty($priority)) {
        $newTicket = [
            'id' => uniqid(),
            'title' => $title,
            'description' => $description,
            'status' => 'ouvert',
            'priority' => $priority,
            'project' => $project,
            'billable' => $billable,
            'createdAt' => time()
        ];
        array_unshift($_SESSION['tickets'], $newTicket);
        // Redirection pour éviter re-soumission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fonction pour obtenir le nom du projet
function getProjectName($projectId) {
    if (!isset($_SESSION['projects'])) {
        return 'Projet inconnu';
    }
    foreach ($_SESSION['projects'] as $project) {
        if ($project['id'] == $projectId) {
            return $project['name'];
        }
    }
    return 'Projet inconnu';
}

// Fonction pour supprimer un ticket
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    $_SESSION['tickets'] = array_filter($_SESSION['tickets'], function($ticket) use ($id) {
        return $ticket['id'] !== $id;
    });
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Récupérer les paramètres de filtrage
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$project_filter = $_GET['project'] ?? '';
$billable_filter = $_GET['billable'] ?? '';

// Filtrer les tickets
$filtered_tickets = array_filter($_SESSION['tickets'], function($ticket) use ($search, $status_filter, $priority_filter, $project_filter, $billable_filter) {
    if ($search && !stripos($ticket['title'], $search) && !stripos($ticket['description'], $search)) {
        return false;
    }
    if ($status_filter && $ticket['status'] !== $status_filter) {
        return false;
    }
    if ($priority_filter && $ticket['priority'] !== $priority_filter) {
        return false;
    }
    if ($project_filter && $ticket['project'] !== $project_filter) {
        return false;
    }
    if ($billable_filter !== '' && ($ticket['billable'] ? '1' : '0') !== $billable_filter) {
        return false;
    }
    return true;
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets - Site de Ticketing</title>
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

    <!-- Bouton mode admin -->
    <div class="admin-toggle">
        <?php if ($isAdminMode): ?>
            <a href="?toggle_admin=1" class="admin-btn admin-active">🔓 Mode Admin</a>
        <?php else: ?>
            <a href="?admin=1" class="admin-btn">🔒 Mode Admin</a>
        <?php endif; ?>
    </div>

    <!-- Modal mot de passe admin -->
    <?php if (isset($_GET['admin']) && !$isAdminMode): ?>
    <div class="admin-modal show">
        <div class="admin-modal-content">
            <h3>Authentifiez vous</h3>
            <?php if (isset($adminError)): ?>
                <div class="error-message"><?php echo $adminError; ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="password" name="admin_password" id="admin_password" placeholder="Mot de passe" required autofocus>
                <div class="modal-buttons">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-cancel" style="text-align: center; padding: 0.8rem; text-decoration: none; display: block;">Annuler</a>
                    <button type="submit">Valider</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <main>
        <div class="tickets-container">
            <!-- Liste des tickets à gauche -->
            <section class="tickets-list">
                <h2>Liste des tickets</h2>
                <!-- Contrôles de recherche et filtres -->
                <button class="filters-toggle" onclick="toggleFilters()">
                    Filtres & Recherche
                </button>

                <div class="controls-bar collapsed" id="filtersPanel">
                    <form method="get" action="">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Rechercher dans les tickets..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filters-row">
                            <select name="status">
                                <option value="">Tous les statuts</option>
                                <option value="ouvert" <?php if ($status_filter == 'ouvert') echo 'selected'; ?>>Ouvert</option>
                                <option value="en-cours" <?php if ($status_filter == 'en-cours') echo 'selected'; ?>>En cours</option>
                                <option value="ferme" <?php if ($status_filter == 'ferme') echo 'selected'; ?>>Fermé</option>
                            </select>
                            <select name="priority">
                                <option value="">Toutes priorités</option>
                                <option value="low" <?php if ($priority_filter == 'low') echo 'selected'; ?>>Faible</option>
                                <option value="medium" <?php if ($priority_filter == 'medium') echo 'selected'; ?>>Moyenne</option>
                                <option value="high" <?php if ($priority_filter == 'high') echo 'selected'; ?>>Haute</option>
                            </select>
                            <select name="project">
                                <option value="">Tous projets</option>
                                <?php foreach ($_SESSION['projects'] as $proj): ?>
                                    <option value="<?php echo $proj['id']; ?>" <?php if ($project_filter == $proj['id']) echo 'selected'; ?>><?php echo htmlspecialchars($proj['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isAdminMode): ?>
                            <select name="billable">
                                <option value="">Facturable ?</option>
                                <option value="1" <?php if ($billable_filter == '1') echo 'selected'; ?>>Oui</option>
                                <option value="0" <?php if ($billable_filter == '0') echo 'selected'; ?>>Non</option>
                            </select>
                            <?php endif; ?>
                        </div>
                        <div class="sort-buttons">
                            <button type="submit" class="sort-btn">Filtrer</button>
                            <a href="tickets.php" class="sort-btn" id="reset-filters">Réinitialiser</a>
                        </div>
                    </form>
                </div>
                <!-- Liste des tickets -->
                <div id="tickets-list">
                    <?php if (empty($filtered_tickets)): ?>
                        <div class="no-results">
                            <p>Aucun ticket trouvé avec ces critères.</p>
                            <button onclick="window.location.href='tickets.php'">Réinitialiser les filtres</button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filtered_tickets as $ticket): ?>
                        <div class="ticket-item" data-status="<?php echo $ticket['status']; ?>" data-priority="<?php echo $ticket['priority']; ?>" data-project="<?php echo $ticket['project']; ?>" data-billable="<?php echo $ticket['billable'] ? 'true' : 'false'; ?>">
                            <div class="ticket-header">
                                <h3 class="ticket-title"><?php echo htmlspecialchars($ticket['title']); ?></h3>
                                <div>
                                    <span class="badge <?php echo $ticket['status']; ?>"><?php echo ucfirst(str_replace('-', ' ', $ticket['status'])); ?></span>
                                    <?php if ($ticket['billable'] && $isAdminMode): ?>
                                    <span class="billable-badge badge" style="background-color: #fff9c4; color: #f57f17;">Facturable</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="ticket-description"><?php echo htmlspecialchars($ticket['description']); ?></p>
                            <div class="ticket-meta">
                                <span class="priority-badge priority-<?php echo $ticket['priority']; ?>"><?php echo $ticket['priority'] === 'high' ? 'Haute' : ($ticket['priority'] === 'medium' ? 'Moyenne' : 'Faible'); ?></span>
                                <a href="project_detail.php?id=<?php echo $ticket['project']; ?>" class="project-name"><?php echo getProjectName($ticket['project']); ?></a>
                            </div>
                            <div class="ticket-actions">
                                <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="btn-details">Voir détails</a>
                                <a href="?delete=<?php echo $ticket['id']; ?>" class="btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer ce ticket ?')">Supprimer</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Formulaire à droite -->
            <section class="ticket-form">
                <h2>Créer un ticket</h2>
                <form method="post" action="">
                    <input type="text" name="title" placeholder="Titre du ticket" required>
                    <textarea name="description" placeholder="Description" rows="4" required></textarea>
                    <select name="project" required>
                        <option value="">Sélectionner un projet</option>
                        <?php foreach ($_SESSION['projects'] as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="priority" required>
                        <option value="">Priorité</option>
                        <option value="low">Faible</option>
                        <option value="medium">Moyenne</option>
                        <option value="high">Haute</option>
                    </select>
                    
                    <!-- Champ facturable (visible uniquement en mode admin) -->
                    <?php if ($isAdminMode): ?>
                    <div class="checkbox-field">
                        <input type="checkbox" name="billable" id="billable-checkbox">
                        <label for="billable-checkbox">Ticket facturable</label>
                    </div>
                    <?php endif; ?>

                    <button type="submit">Créer le ticket</button>
                </form>
            </section>
        </div>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script>
        function toggleFilters() {
            const panel = document.getElementById("filtersPanel");
            panel.classList.toggle("collapsed");
        }
    </script>

</body>
</html>