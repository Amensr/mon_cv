<?php
require_once 'includes/header.php';

// Récupérer les derniers CV et invitations si l'utilisateur est connecté
$recent_cvs = [];
$recent_invitations = [];

if (isset($_SESSION['user_id'])) {
    require_once 'includes/db.php';
    require_once 'includes/functions.php';
    
    $recent_cvs = get_user_cvs($conn, $_SESSION['user_id'], 3);
    $recent_invitations = get_user_invitations($conn, $_SESSION['user_id'], 3);
}
?>
<br><br><br>
<br>


<div class="hero-section">
    <div class="hero-content">
        <h1>Créez des CV et invitations professionnels</h1>
        <p>Notre outil facile à utiliser vous permet de créer des documents impressionnants en quelques minutes</p>
        <div class="hero-buttons">
            <a href="create_cv.php" class="btn btn-primary">Créer un CV</a>
            <a href="create_invitation.php" class="btn btn-secondary">Créer une carte d'invitation</a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="recent-items">
    <section class="recent-cvs">
        <h2>Vos CV récents</h2>
        <?php if (!empty($recent_cvs)): ?>
        <div class="items-grid">
            <?php foreach ($recent_cvs as $cv): ?>
            <div class="item-card">
                <h3><?= htmlspecialchars($cv['title']) ?></h3>
                <p>Thème: <?= htmlspecialchars($cv['theme_name']) ?></p>
                <p>Créé le: <?= format_date($cv['created_at']) ?></p>
                <div class="card-actions">
                    <a href="preview.php?id=<?= $cv['id'] ?>" class="btn">Voir</a>
                    <a href="edit_cv.php?id=<?= $cv['id'] ?>" class="btn">Modifier</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="my_works.php" class="see-all">Voir tous vos CV →</a>
        <?php else: ?>
        <p>Vous n'avez pas encore créé de CV.</p>
        <a href="create_cv.php" class="btn">Créer votre premier CV</a>
        <?php endif; ?>
    </section>
    
    <section class="recent-invitations">
        <h2>Vos invitations récentes</h2>
        <?php if (!empty($recent_invitations)): ?>
        <div class="items-grid">
            <?php foreach ($recent_invitations as $inv): ?>
            <div class="item-card">
                <h3><?= htmlspecialchars($inv['title']) ?></h3>
                <p>Type: <?= htmlspecialchars($inv['type_name']) ?></p>
                <p>Créé le: <?= format_date($inv['created_at']) ?></p>
                <div class="card-actions">
                    <a href="preview_invitation.php?id=<?= $inv['id'] ?>" class="btn">Voir</a>
                    <a href="edit_invitation.php?id=<?= $inv['id'] ?>" class="btn">Modifier</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="my_works.php" class="see-all">Voir toutes vos invitations →</a>
        <?php else: ?>
        <p>Vous n'avez pas encore créé d'invitation.</p>
        <a href="create_invitation.php" class="btn">Créer votre première invitation</a>
        <?php endif; ?>
    </section>
</div>
<?php endif; ?>

<style>
.hero-section {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 4rem 2rem;
    text-align: center;
    margin-bottom: 3rem;
    border-radius: 8px;
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
}

.hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.hero-content p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.recent-items {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-top: 3rem;
}

.recent-items h2 {
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.item-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.item-card h3 {
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.item-card p {
    margin-bottom: 0.3rem;
    font-size: 0.9rem;
    color: #666;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.card-actions .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.see-all {
    display: inline-block;
    margin-top: 1rem;
    color: var(--primary-color);
    text-decoration: none;
}

.see-all:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .recent-items {
        grid-template-columns: 1fr;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>