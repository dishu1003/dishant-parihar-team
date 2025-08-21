</main> <!-- Closes the main tag from header.php -->

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; <?php echo date('Y'); ?> <?php echo e(SITE_NAME); ?>. All Rights Reserved.</p>
                <p>Developed for Asclepius Wellness Pvt. Ltd. by Dishant Parihar's Team.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a> |
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!--
    JavaScript files are loaded at the end of the body for better performance.
    `main.js` is the entry point and is loaded as a module.
    The `defer` attribute ensures it's executed after the document is parsed.
    -->
    <script src="/assets/js/main.js" type="module" defer></script>

</body>
</html>
