<?php
$page_title = 'Noticias y Novedades';
require_once __DIR__ . '/../../includes/config.php';

$news_articles = [];
try {
    $sql = "SELECT n.title, n.content, n.image_url, n.created_at, u.name as author_name
            FROM news n
            LEFT JOIN users u ON n.author_id = u.id
            ORDER BY n.created_at DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $news_articles[] = $row;
    }
} catch (Exception $e) {}

require_once __DIR__ . '/../../includes/header_public.php';
?>
<main class="container">
    <section class="news-section">
        <h1 class="section-title">Noticias y Novedades</h1>
        <p class="section-subtitle">Mantente al día con las últimas actividades y comunicados de nuestra campaña.</p>
        <div class="news-grid">
            <?php if (empty($news_articles)): ?>
                <div class="card empty-news"><p>Pronto compartiremos las últimas novedades.</p></div>
            <?php else: ?>
                <?php foreach ($news_articles as $article): ?>
                    <article class="card news-card animated-section">
                        <div class="news-image"><img src="<?php echo BASE_URL . e($article['image_url'] ?: 'public/images/flyer-default.jpg'); ?>" alt=""></div>
                        <div class="news-content">
                            <div class="news-meta"><span><i class="fas fa-calendar-alt"></i> <?php echo date('d M, Y', strtotime($article['created_at'])); ?></span></div>
                            <h2 class="news-title"><?php echo e($article['title']); ?></h2>
                            <p class="news-excerpt"><?php echo e(substr($article['content'], 0, 120)); ?>...</p>
                            <a href="#" class="cta-button secondary-button">Leer Más</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer_public.php'; ?>

