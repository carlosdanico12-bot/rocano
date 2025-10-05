            </main> <!-- Cierre de .content-area -->
        </div> <!-- Cierre de .main-content -->
    </div> <!-- Cierre de .admin-wrapper -->

    <div class="overlay"></div>

    <!-- Carga el script global para todo el panel -->
    <script src="<?php echo BASE_URL; ?>assets/js/admin_script.js"></script>

    <!-- Carga un script específico para la página, si existe -->
    <?php
    $page_specific_js = 'script.js';
    if (file_exists($page_specific_js)) {
        echo '<script src="' . $page_specific_js . '"></script>';
    }
    ?>
</body>
</html>

