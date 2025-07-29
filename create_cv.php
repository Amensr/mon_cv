<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
// ...
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$themes = get_cv_themes($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Traitement du formulaire
    $theme_id = sanitize_input($_POST['theme_id'], $conn);
    $title = sanitize_input($_POST['title'], $conn);
    
    $data = [
        'personal_info' => $_POST['personal_info'],
        'experiences' => $_POST['experiences'],
        'education' => $_POST['education'],
        'skills' => $_POST['skills'],
        'languages' => $_POST['languages'],
        'hobbies' => $_POST['hobbies']
    ];
    
    if (save_cv($conn, $_SESSION['user_id'], $theme_id, $title, $data)) {
        header("Location: preview.php?id=" . $conn->insert_id);
        exit();
    } else {
        $error = "Erreur lors de la sauvegarde du CV";
    }
}
?>

<div class="theme-selector">
    <h2>Choisissez votre thème</h2>
    <div class="themes-grid">
        <?php foreach ($themes as $theme): ?>
        <div class="theme-card" data-theme-id="<?= $theme['id'] ?>">
            <img src="assets/images/themes/<?= $theme['preview_image'] ?>" alt="<?= $theme['name'] ?>">
            <h3><?= $theme['name'] ?></h3>
            <button class="select-theme" data-theme-id="<?= $theme['id'] ?>">Sélectionner</button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="cv-form-container" style="display:none;">
    <form id="cv-form" method="POST">
        <input type="hidden" name="theme_id" id="selected-theme-id">
        
        <div class="form-section">
            <h2>Informations Personnelles</h2>
            <!-- Champs du formulaire -->
        </div>
        
        <div class="form-section">
            <h2>Expériences Professionnelles</h2>
            <!-- Champs dynamiques pour les expériences -->
        </div>
        
        <!-- Autres sections du CV -->
        
        <button type="submit" class="save-cv">Générer mon CV</button>
    </form>
</div>

<script src="assets/js/theme_switcher.js"></script>
<?php require_once 'includes/footer.php'; ?>