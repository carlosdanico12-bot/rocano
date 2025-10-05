<?php
$page_title = 'Inicio';
require_once __DIR__ . '/../../includes/config.php';

// Obtener datos de configuración para el banner
$settings = [];
try {
    $result = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('candidate_short_name', 'political_party')");
    while($row = $result->fetch_assoc()){
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {} // Silenciar errores aquí

require_once __DIR__ . '/../../includes/header_public.php';
?>
<main>
    <!-- Banner Principal -->
    <section class="hero">
        <div class="hero-image" style="background-image: url('<?php echo BASE_URL; ?>public/images/banner.jpg');"></div>
        <div class="hero-content animated-section">
            <h1><?php echo e($settings['candidate_short_name'] ?? 'Candidato'); ?></h1>
            <p class="subtitle">Un nuevo futuro para Tingo María con experiencia y compromiso.</p>
            <a href="<?php echo BASE_URL; ?>public/propuestas/" class="cta-button">Conoce las Propuestas</a>
        </div>
    </section>

    <!-- Sección Pilares -->
    <section class="container pillars-section">
        <h2 class="section-title">Nuestros Pilares</h2>
        <div class="card-container">
            <!-- Los pilares se podrían cargar desde la BD, por ahora están aquí como ejemplo -->
            <div class="card icon-card animated-section">
                <div class="card-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Seguridad Ciudadana</h3>
                <p>Más serenos, mejor equipados y en constante coordinación con la policía y juntas vecinales.</p>
            </div>
            <div class="card icon-card animated-section">
                <div class="card-icon"><i class="fas fa-heartbeat"></i></div>
                <h3>Salud para Todos</h3>
                <p>Implementación de postas médicas móviles para llegar a cada caserío y anexo.</p>
            </div>
            <div class="card icon-card animated-section">
                <div class="card-icon"><i class="fas fa-briefcase"></i></div>
                <h3>Oportunidades y Empleo</h3>
                <p>Programas de apoyo a emprendedores locales y facilidades para la inversión privada responsable.</p>
            </div>
        </div>
    </section>

     <!-- Sección CTA (Llamado a la Acción) -->
    <div class="container animated-section">
        <section class="cta-section">
            <div class="cta-content">
                <h2>Únete a Nuestro Equipo</h2>
                <p>Tu participación es clave para lograr el cambio. Conviértete en un voluntario y sé parte de la historia.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>login/register/" class="cta-button">¡Quiero ser voluntario!</a>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/../../includes/footer_public.php'; ?>

