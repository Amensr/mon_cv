        </main><br><br><br><br>
    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> CVCreator. Tous droits réservés.</p>
            <nav class="footer-nav">
                <ul>
                    <li><a href="terms.php">Conditions d'utilisation</a></li>
                    <li><a href="privacy.php">Confidentialité</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
    <?php if(isset($additional_js)): ?>
        <script src="assets/js/<?= $additional_js ?>"></script>
    <?php endif; ?>
</body>
</html>