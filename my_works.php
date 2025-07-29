<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
// Vérifier si l'utilisateur est connecté
// ...
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Récupérer les travaux de l'utilisateur

check_auth();

// Récupérer les CV et les invitations de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); 
$cvs = get_user_cvs($conn, $_SESSION['user_id']);
$invitations = get_user_invitations($conn, $_SESSION['user_id']);
?>

<div class="works-container">
    <h1>Mes Travaux</h1>
    
    <div class="tabs">
        <button class="tab-btn active" data-tab="cv">CV</button>
        <button class="tab-btn" data-tab="invitations">Cartes d'Invitation</button>
    </div>
    
    <div class="tab-content active" id="cv-tab">
        <div class="works-grid">
            <?php foreach ($cvs as $cv): ?>
            <div class="work-card">
                <div class="work-preview" style="background-image: url('assets/images/themes/<?= $cv['theme_name'] ?>.jpg')"></div>
                <h3><?= htmlspecialchars($cv['title']) ?></h3>
                <p>Thème: <?= htmlspecialchars($cv['theme_name']) ?></p>
                <div class="work-actions">
                    <a href="preview.php?id=<?= $cv['id'] ?>" class="btn-consult">Consulter</a>
                    <a href="edit_cv.php?id=<?= $cv['id'] ?>" class="btn-edit">Modifier</a>
                    <a href="#" class="btn-delete" data-id="<?= $cv['id'] ?>" data-type="cv">Supprimer</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="tab-content" id="invitations-tab">
        <!-- Liste des cartes d'invitation -->
    </div>
</div>

<script src="assets/js/my_works.js"></script>
<?php require_once 'includes/footer.php'; ?>