<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$cv_id = intval($_GET['id']);
$cv = get_cv_by_id($conn, $cv_id);
$theme = get_theme_by_id($conn, $cv['theme_id']);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="cv_'.time().'.pdf"');

// Utiliser une librairie comme Dompdf ou TCPDF pour générer le PDF
// Voici une version simplifiée pour l'impression HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>CV - <?= htmlspecialchars($cv['title']) ?></title>
    <link rel="stylesheet" href="assets/css/print.css">
    <link rel="stylesheet" href="assets/css/themes/<?= $theme['name'] ?>.print.css">
    <style>
    @page {
        size: A4;
        margin: 0;
    }
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    </style>
</head>
<body>
    <div class="print-cv">
        <?php include 'templates/cv/'.$theme['name'].'.print.php'; ?>
    </div>
    <script>
    window.onload = function() {
        setTimeout(function() {
            window.print();
            window.close();
        }, 500);
    }
    </script>
</body>
</html>