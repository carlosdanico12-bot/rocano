<?php
$page_title = 'Nuestras Propuestas';
require_once __DIR__ . '/../../includes/config.php';

$proposals = [];
try {
    $result = $conn->query("SELECT title, icon, summary, details FROM proposals ORDER BY display_order ASC");
    while($row = $result->fetch_assoc()) {
        $proposals[] = $row;
    }
} catch (Exception $e) {}

require_once __DIR__ . '/../../includes/header_public.php';
?>
<main class="container">
    <section class="proposals-section">
        <h1 class="section-title">Nuestros Pilares de Gestión</h1>
        <p class="section-subtitle">Conoce las propuestas clave que transformarán nuestro distrito. Soluciones reales para problemas reales.</p>
        <div class="proposals-grid">
            <?php foreach ($proposals as $proposal): ?>
                <div class="card proposal-card animated-section">
                    <div class="card-icon"><i class="fas <?php echo e($proposal['icon']); ?>"></i></div>
                    <h3><?php echo e($proposal['title']); ?></h3>
                    <p class="summary"><?php echo e($proposal['summary']); ?></p>
                    <ul class="details-list">
                        <?php foreach (explode(';', $proposal['details']) as $detail): ?>
                            <li><i class="fas fa-check-circle"></i> <?php echo e($detail); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer_public.php'; ?>

