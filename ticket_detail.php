<?php
// ticket_detail.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: tickets.php');
    exit;
}

require_once 'db.php';

$pdo  = getDB();
$uid  = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$tid  = (int)$_GET['id'];

// Récupère le ticket
$stmt = $pdo->prepare('
    SELECT t.*, p.nom AS projet_nom, p.client_id,
           u.prenom AS createur_prenom, u.nom AS createur_nom
    FROM tickets t
    JOIN projets p ON t.projet_id = p.id
    JOIN users u ON t.created_by = u.id
    WHERE t.id = :id
');
$stmt->execute([':id' => $tid]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header('Location: tickets.php');
    exit;
}

// Contrôle d'accès
if ($role === 'collaborateur') {
    $stmt = $pdo->prepare('SELECT 1 FROM projet_collaborateurs WHERE projet_id = :pid AND user_id = :uid');
    $stmt->execute([':pid' => $ticket['projet_id'], ':uid' => $uid]);
    if (!$stmt->fetch()) { header('Location: tickets.php'); exit; }
} elseif ($role === 'client') {
    $stmt = $pdo->prepare('SELECT client_id FROM users WHERE id = :uid');
    $stmt->execute([':uid' => $uid]);
    $cid = $stmt->fetchColumn();
    if ($ticket['client_id'] != $cid) { header('Location: tickets.php'); exit; }
}

// Récupère le temps passé
$stmt = $pdo->prepare('
    SELECT te.*, u.prenom, u.nom AS user_nom
    FROM temps te
    JOIN users u ON te.user_id = u.id
    WHERE te.ticket_id = :tid
    ORDER BY te.date_saisie DESC
');
$stmt->execute([':tid' => $tid]);
$temps_list = $stmt->fetchAll();
$total_heures = array_sum(array_column($temps_list, 'duree_heures'));

// Action client : valider ou refuser
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'client') {
    $action = $_POST['action'] ?? '';

    if ($action === 'valider' && $ticket['type'] === 'facturable' && $ticket['statut'] === 'en_attente_validation') {
        $stmt = $pdo->prepare("UPDATE tickets SET statut = 'validé', valide_par = :uid, valide_le = NOW() WHERE id = :id");
        $stmt->execute([':uid' => $uid, ':id' => $tid]);

        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, entite, entite_id, detail) VALUES (:uid, 'ticket_validé', 'ticket', :id, NULL)");
        $stmt->execute([':uid' => $uid, ':id' => $tid]);

        header('Location: ticket_detail.php?id=' . $tid);
        exit;
    }

    if ($action === 'refuser' && $ticket['type'] === 'facturable' && $ticket['statut'] === 'en_attente_validation') {
        $commentaire = trim($_POST['commentaire_refus'] ?? '');
        $stmt = $pdo->prepare("UPDATE tickets SET statut = 'refusé', commentaire_refus = :comment WHERE id = :id");
        $stmt->execute([':comment' => $commentaire, ':id' => $tid]);

        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, entite, entite_id, detail) VALUES (:uid, 'ticket_refusé', 'ticket', :id, :detail)");
        $stmt->execute([':uid' => $uid, ':id' => $tid, ':detail' => $commentaire]);

        header('Location: ticket_detail.php?id=' . $tid);
        exit;
    }
}

// Recharge après action
$stmt = $pdo->prepare('SELECT t.*, p.nom AS projet_nom, u.prenom AS createur_prenom, u.nom AS createur_nom FROM tickets t JOIN projets p ON t.projet_id = p.id JOIN users u ON t.created_by = u.id WHERE t.id = :id');
$stmt->execute([':id' => $tid]);
$ticket = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du ticket - <?= htmlspecialchars($ticket['titre']) ?></title>
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
                <li><a href="tickets.php">Tickets</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Détails du ticket</h2>
            <div class="detail">
                <h3><?= htmlspecialchars($ticket['titre']) ?></h3>
                <p><strong>Description :</strong> <?= htmlspecialchars($ticket['description']) ?></p>
                <p><strong>Statut :</strong> <span class="badge <?= $ticket['statut'] ?>"><?= ucfirst(str_replace('_', ' ', $ticket['statut'])) ?></span></p>
                <p><strong>Projet :</strong> <a href="project_detail.php?id=<?= $ticket['projet_id'] ?>"><?= htmlspecialchars($ticket['projet_nom']) ?></a></p>
                <p><strong>Créé par :</strong> <?= htmlspecialchars($ticket['createur_prenom'] . ' ' . $ticket['createur_nom']) ?></p>
                <p><strong>Créé le :</strong> <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></p>

                <?php if ($role === 'admin'): ?>
                    <p><strong>Type :</strong> <?= $ticket['type'] === 'facturable' ? '<span class="billable-badge badge" style="background-color:#fff9c4;color:#f57f17;">Facturable</span>' : 'Inclus' ?></p>
                <?php endif; ?>

                <?php if ($ticket['commentaire_refus']): ?>
                    <p><strong>Commentaire de refus :</strong> <?= htmlspecialchars($ticket['commentaire_refus']) ?></p>
                <?php endif; ?>

                <!-- Temps passé -->
                <h4>Temps passé (total : <?= number_format($total_heures, 2) ?>h)</h4>
                <?php if (empty($temps_list)): ?>
                    <p>Aucun temps enregistré.</p>
                <?php else: ?>
                    <?php foreach ($temps_list as $t): ?>
                        <p><?= htmlspecialchars($t['prenom'] . ' ' . $t['user_nom']) ?> — <?= $t['duree_heures'] ?>h le <?= date('d/m/Y', strtotime($t['date_saisie'])) ?><?= $t['description'] ? ' : ' . htmlspecialchars($t['description']) : '' ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Validation client -->
                <?php if ($role === 'client' && $ticket['type'] === 'facturable' && $ticket['statut'] === 'en_attente_validation'): ?>
                <hr>
                <h4>Validation de facturation</h4>
                <form method="post" action="">
                    <button type="submit" name="action" value="valider">✅ Valider la facturation</button>
                </form>
                <form method="post" action="" style="margin-top:1rem;">
                    <textarea name="commentaire_refus" placeholder="Raison du refus (optionnel)" rows="3"></textarea>
                    <button type="submit" name="action" value="refuser">❌ Refuser</button>
                </form>
                <?php endif; ?>

                <a href="tickets.php" class="btn-details">Retour à la liste</a>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 Site de Ticketing. Tous droits réservés.</p>
    </footer>
    <script>
        function toggleMenu() { document.getElementById('nav-menu').classList.toggle('show'); }
    </script>
</body>
</html>