<?php
$page_title = 'Agenda de Eventos';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$coordinator_id = $_SESSION['user_id'];
$events = [];
$coordinator_zones = [];

try {
    // 1. Obtener las zonas asignadas a este coordinador
    $stmt_zones = $conn->prepare("SELECT z.id, z.nombre FROM zonas z JOIN coordinador_zona cz ON z.id = cz.zona_id WHERE cz.user_id = ?");
    $stmt_zones->bind_param("i", $coordinator_id);
    $stmt_zones->execute();
    $result_zones = $stmt_zones->get_result();
    while ($row = $result_zones->fetch_assoc()) {
        $coordinator_zones[] = $row;
    }
    
    // 2. Obtener los eventos de esas zonas Y los eventos generales (sin zona asignada)
    $zone_ids = array_column($coordinator_zones, 'id');
    if (!empty($zone_ids)) {
        $placeholders = implode(',', array_fill(0, count($zone_ids), '?'));
        $sql_events = "SELECT e.*, z.nombre as zona_nombre 
                       FROM events e 
                       LEFT JOIN zonas z ON e.zona_id = z.id 
                       WHERE e.zona_id IN ($placeholders) OR e.zona_id IS NULL
                       ORDER BY e.fecha_hora DESC";
        $stmt_events = $conn->prepare($sql_events);
        $stmt_events->bind_param(str_repeat('i', count($zone_ids)), ...$zone_ids);
        $stmt_events->execute();
        $result_events = $stmt_events->get_result();
        while($row = $result_events->fetch_assoc()) {
            $events[] = $row;
        }
    } else {
        // Si el coordinador no tiene zonas, solo mostrar eventos generales
        $sql_events = "SELECT e.*, z.nombre as zona_nombre FROM events e LEFT JOIN zonas z ON e.zona_id = z.id WHERE e.zona_id IS NULL ORDER BY e.fecha_hora DESC";
        $result = $conn->query($sql_events);
        while($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar los eventos: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Librería de FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<div class="page-header">
    <h1>Agenda de Eventos en mis Zonas</h1>
    <div class="view-switcher">
        <button id="cardViewBtn" class="switcher-btn active" title="Vista de Tarjetas"><i class="fas fa-th-large"></i></button>
        <button id="calendarViewBtn" class="switcher-btn" title="Vista de Calendario"><i class="fas fa-calendar-alt"></i></button>
    </div>
</div>

<!-- Vista de Tarjetas -->
<div id="cardView">
    <div class="events-grid">
         <?php if (empty($events)): ?>
            <div class="card card-empty"><p>No hay eventos programados en tus zonas asignadas.</p></div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="card event-card">
                    <div class="event-image" style="background-image: url('<?php echo BASE_URL . e($event['imagen_url'] ?: 'public/images/flyer-default.jpg'); ?>')"></div>
                    <div class="event-content">
                        <div class="event-header">
                            <span class="event-type event-type-<?php echo e(strtolower(str_replace(' ', '-', $event['tipo_evento']))); ?>"><?php echo e($event['tipo_evento']); ?></span>
                            <span class="event-date"><?php echo date('d M, Y H:i', strtotime($event['fecha_hora'])); ?>h</span>
                        </div>
                        <h3 class="event-title"><?php echo e($event['titulo']); ?></h3>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo e($event['lugar'] ?: 'Lugar no especificado'); ?></p>
                        <p class="event-zone"><i class="fas fa-map-marked-alt"></i> Zona: <?php echo e($event['zona_nombre'] ?: 'General'); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Vista de Calendario -->
<div id="calendarView" style="display: none;">
    <div class="card">
        <div id="calendar"></div>
    </div>
</div>

<!-- Pasamos los datos de PHP a JavaScript para el calendario -->
<script>
    const allEventsData = <?php echo json_encode($events); ?>;
</script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
