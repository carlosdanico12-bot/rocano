<?php
$page_title = 'Conoce al Equipo';
require_once __DIR__ . '/../../includes/config.php';

$settings = [];
$team_members = [];
try {
    $result_settings = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key = 'candidate_full_name'");
    $settings = $result_settings->fetch_assoc();

    $result_team = $conn->query("SELECT name, position, image_url, bio FROM team_members ORDER BY display_order ASC");
    while($row = $result_team->fetch_assoc()){
        $team_members[] = $row;
    }
} catch (Exception $e) {}

require_once __DIR__ . '/../../includes/header_public.php';
?>
<main class="container">
    <section class="about-hero animated-section">
        <div class="hero-text">
            <h2><?php echo e($settings['setting_value'] ?? 'Nuestro Candidato'); ?></h2>
            <p>Un profesional comprometido con el progreso de Tingo María. Con experiencia en gestión y un profundo conocimiento de las necesidades de nuestro pueblo, lidera un equipo preparado para trabajar por un futuro mejor para todos.</p>
        </div>
        <div class="hero-image">
             <img src="<?php echo BASE_URL; ?>public/images/candidate-photo.jpg" alt="Foto del candidato">
        </div>
    </section>

    <section class="team-section">
        <h2 class="section-title">Un Equipo para Gobernar</h2>
        <p class="section-subtitle">Conoce a los profesionales que acompañan al Ing. Cori en la lista de regidores.</p>
        <div class="team-grid">
            <?php foreach($team_members as $member): ?>
            <div class="card team-card animated-section">
                <img src="<?php echo BASE_URL . e($member['image_url']); ?>" alt="Foto de <?php echo e($member['name']); ?>">
                <h3><?php echo e($member['name']); ?></h3>
                <p class="position"><?php echo e($member['position']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer_public.php'; ?>

