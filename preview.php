<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$cv_id = intval($_GET['id']);
$cv = get_cv_by_id($conn, $cv_id);

if (!$cv) {
    die("CV non trouvé");
}

$theme = get_theme_by_id($conn, $cv['theme_id']);
$personal_info = json_decode($cv['personal_info'], true);
?>
<div class="preview-container" data-theme="<?= $theme['name'] ?>">
    <div class="preview-toolbar">
        <button id="rotate-3d" class="tool-btn">3D View</button>
        <button id="change-color" class="tool-btn">Changer Couleur</button>
        <button id="export-pdf" class="tool-btn">Export PDF</button>
        <a href="print.php?id=<?= $cv_id ?>" class="tool-btn">Imprimer</a>
    </div>
    
    <div class="cv-preview-wrapper">
        <div class="cv-preview" id="cv-preview">
            <!-- Le contenu du CV sera généré dynamiquement par JavaScript -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cvData = {
        personal_info: <?= $cv['personal_info'] ?>,
        experiences: <?= $cv['experiences'] ?>,
        education: <?= $cv['education'] ?>,
        skills: <?= $cv['skills'] ?>,
        theme: "<?= $theme['name'] ?>"
    };
    
    // Initialiser la prévisualisation
    renderCVPreview(cvData);
    
    // Effet 3D
    document.getElementById('rotate-3d').addEventListener('click', function() {
        const preview = document.querySelector('.cv-preview-wrapper');
        preview.classList.toggle('3d-mode');
        
        if (preview.classList.contains('3d-mode')) {
            // Animation de rotation 3D
            preview.style.transform = 'perspective(1000px) rotateY(20deg)';
            setTimeout(() => {
                preview.style.transform = 'perspective(1000px) rotateY(0deg)';
                preview.style.transition = 'transform 0.5s ease';
            }, 1500);
        }
    });
    
    // Changer la couleur dynamiquement
    document.getElementById('change-color').addEventListener('click', function() {
        const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6'];
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        document.documentElement.style.setProperty('--primary-color', randomColor);
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>