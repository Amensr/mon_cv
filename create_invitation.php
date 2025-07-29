<?php


require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Vérifier si l'utilisateur est connecté
// ...

$invitation_types = get_invitation_types($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_id = sanitize_input($_POST['type_id'] ?? '', $conn);
    $title = sanitize_input($_POST['title'] ?? '', $conn);
    
    $content = [
        'event_name'      => $_POST['event_name'] ?? '',
        'date'            => $_POST['date'] ?? '',
        'time'            => $_POST['time'] ?? '',
        'location'        => $_POST['location'] ?? '',
        'rsvp'            => $_POST['rsvp'] ?? '',
        'custom_message'  => $_POST['custom_message'] ?? ''
    ];
    
    $design_settings = [
        'primary_color'   => $_POST['primary_color'] ?? '',
        'secondary_color' => $_POST['secondary_color'] ?? '',
        'font_family'     => $_POST['font_family'] ?? '',
        'background'      => $_POST['background'] ?? ''
    ];
    
    if (save_invitation($conn, $_SESSION['user_id'], $type_id, $title, $content, $design_settings)) {
        header("Location: preview_invitation.php?id=" . $conn->insert_id);
        exit();
    } else {
        $error = "Erreur lors de la sauvegarde de l'invitation";
    }
}
?>

<div class="invitation-creator">
    <div class="design-panel">
        <h2>Créateur de Carte d'Invitation</h2>
        
        <div class="type-selector">
            <h3>Type d'Événement</h3>
            <div class="type-grid">
                <?php foreach ($invitation_types as $type): ?>
                <div class="type-card" data-type-id="<?= $type['id'] ?>">
                    <img src="assets/images/invitations/<?= $type['preview_image'] ?>" 
                         alt="<?= $type['name'] ?>">
                    <h4><?= $type['name'] ?></h4>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <form id="invitation-form" method="POST">
            <input type="hidden" name="type_id" id="selected-type-id">
            
            <div class="form-section">
                <h3>Contenu</h3>
                <!-- Champs pour le contenu de l'invitation -->
            </div>
            
            <div class="form-section">
                <h3>Design</h3>
                <div class="color-picker">
                    <label>Couleur Primaire</label>
                    <input type="color" name="primary_color" value="#3498db" id="primary-color">
                </div>
                <!-- Autres options de design -->
            </div>
            
            <button type="submit" class="save-btn">Générer l'Invitation</button>
        </form>
    </div>
    
    <div class="live-preview">
        <h3>Aperçu en Direct</h3>
        <div id="invitation-preview">
            <!-- Prévisualisation dynamique -->
        </div>
    </div>
</div>

<script src="assets/js/invitation_creator.js"></script>
<?php require_once 'includes/footer.php'; ?>