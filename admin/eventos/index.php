<?php
$page_title = 'Gestión de Eventos';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';

// --- Lógica para Cargar/Editar/Eliminar/Crear Eventos ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lógica para manejar la subida de la imagen
    $imagen_url = $_POST['existing_image_url'] ?? '';
    if (isset($_FILES['imagen_flyer']) && $_FILES['imagen_flyer']['error'] == 0) {
        $target_dir = __DIR__ . "/../../public/images/flyers/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $file_name = uniqid() . '-' . basename($_FILES["imagen_flyer"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["imagen_flyer"]["tmp_name"], $target_file)) {
            $imagen_url = 'public/images/flyers/' . $file_name;
        } else {
            $errors[] = "Hubo un error al subir la imagen.";
        }
    }

    $action = $_POST['action'] ?? 'create';
    $event_id = $_POST['event_id'] ?? null;
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $lugar = trim($_POST['lugar'] ?? '');
    $fecha_hora = $_POST['fecha_hora'] ?? '';
    $tipo_evento = $_POST['tipo_evento'] ?? '';
    
    // --- CORRECCIÓN CLAVE ---
    // Si 'zona_id' está vacío (se eligió "General"), lo convertimos a NULL para la BD.
    $zona_id = empty($_POST['zona_id']) ? null : (int)$_POST['zona_id'];

    if (empty($titulo) || empty($fecha_hora) || empty($tipo_evento)) {
        $errors[] = "Título, fecha/hora y tipo de evento son obligatorios.";
    }

    if (empty($errors)) {
        if ($action === 'update' && $event_id) {
            $stmt = $conn->prepare("UPDATE events SET titulo = ?, descripcion = ?, lugar = ?, fecha_hora = ?, tipo_evento = ?, imagen_url = ?, zona_id = ? WHERE id = ?");
            $stmt->bind_param("ssssssii", $titulo, $descripcion, $lugar, $fecha_hora, $tipo_evento, $imagen_url, $zona_id, $event_id);
            $success_message = "Evento actualizado con éxito.";
        } else {
            $stmt = $conn->prepare("INSERT INTO events (titulo, descripcion, lugar, fecha_hora, tipo_evento, imagen_url, zona_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssii", $titulo, $descripcion, $lugar, $fecha_hora, $tipo_evento, $imagen_url, $zona_id, $_SESSION['user_id']);
            $success_message = "Evento creado con éxito.";
        }

        if (!$stmt->execute()) {
            $errors[] = "Error al guardar el evento: " . $stmt->error;
            $success_message = '';
        }
        $stmt->close();
    }
}


// --- Obtener datos para la página ---
$events = [];
$zones = [];

try {
    // Obtener eventos
    $sql_events = "SELECT e.*, z.nombre as zona_nombre FROM events e LEFT JOIN zonas z ON e.zona_id = z.id ORDER BY e.fecha_hora DESC";
    $result_events = $conn->query($sql_events);
    while($row = $result_events->fetch_assoc()) {
        $events[] = $row;
    }
    // Obtener zonas
    $result_zones = $conn->query("SELECT id, nombre FROM zonas ORDER BY nombre ASC");
    while($row = $result_zones->fetch_assoc()) {
        $zones[] = $row;
    }
} catch (Exception $e) {
    $errors[] = "Error al cargar datos: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<div class="page-header">
    <h1>Agenda de Eventos</h1>
    <div class="header-actions">
        <div class="view-switcher">
            <button id="cardViewBtn" class="switcher-btn active" title="Vista de Tarjetas"><i class="fas fa-th-large"></i></button>
            <button id="calendarViewBtn" class="switcher-btn" title="Vista de Calendario"><i class="fas fa-calendar-alt"></i></button>
        </div>
        <button id="addEventBtn" class="cta-button"><i class="fas fa-plus"></i> Crear Evento</button>
    </div>
</div>

<!-- Vista de Tarjetas -->
<div id="cardView">
    <div class="card filter-card">
        <input type="text" id="searchEvent" placeholder="Buscar evento por título...">
        <select id="filterZone">
            <option value="all">Todas las Zonas</option>
            <?php foreach($zones as $zone): ?>
                <option value="<?php echo e($zone['nombre']); ?>"><?php echo e($zone['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="events-grid">
         <?php if (empty($events)): ?>
            <p>No hay eventos programados. ¡Crea el primero!</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="card event-card" data-title="<?php echo e(strtolower($event['titulo'])); ?>" data-zone="<?php echo e($event['zona_nombre']); ?>">
                    <div class="event-image" style="background-image: url('<?php echo BASE_URL . e($event['imagen_url'] ?: 'public/images/flyer-default.jpg'); ?>')"></div>
                    <div class="event-content">
                        <div class="event-header">
                            <span class="event-type event-type-<?php echo e(strtolower(str_replace(' ', '-', $event['tipo_evento']))); ?>"><?php echo e($event['tipo_evento']); ?></span>
                            <span class="event-date"><?php echo date('d M, Y H:i', strtotime($event['fecha_hora'])); ?>h</span>
                        </div>
                        <h3 class="event-title"><?php echo e($event['titulo']); ?></h3>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo e($event['lugar'] ?: 'Lugar no especificado'); ?></p>
                        <p class="event-zone"><i class="fas fa-map-marked-alt"></i> Zona: <?php echo e($event['zona_nombre'] ?: 'General'); ?></p>
                        <div class="event-actions">
                             <button class="action-btn edit-btn"
                                data-id="<?php echo e($event['id']); ?>"
                                data-titulo="<?php echo e($event['titulo']); ?>"
                                data-descripcion="<?php echo e($event['descripcion']); ?>"
                                data-lugar="<?php echo e($event['lugar']); ?>"
                                data-fecha_hora="<?php echo date('Y-m-d\TH:i', strtotime($event['fecha_hora'])); ?>"
                                data-tipo_evento="<?php echo e($event['tipo_evento']); ?>"
                                data-zona_id="<?php echo e($event['zona_id']); ?>"
                                data-imagen_url="<?php echo e($event['imagen_url']); ?>"
                                title="Editar"><i class="fas fa-edit"></i></button>
                            <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este evento?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="event_id" value="<?php echo e($event['id']); ?>">
                                <button type="submit" class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </div>
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


<!-- Modal para Crear/Editar Evento -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Crear Nuevo Evento</h2>
        <form id="eventForm" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="event_id" id="event_id">
            <input type="hidden" name="existing_image_url" id="existing_image_url">

            <div class="form-group">
                <label for="titulo">Título del Evento</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>
             <div class="form-group">
                <label for="tipo_evento">Tipo de Evento</label>
                <select id="tipo_evento" name="tipo_evento" required>
                    <option value="Mitin">Mitin</option>
                    <option value="Caminata">Caminata</option>
                    <option value="Reunion">Reunión</option>
                    <option value="Caravana">Caravana</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="form-group-grid">
                <div class="form-group">
                    <label for="fecha_hora">Fecha y Hora</label>
                    <input type="datetime-local" id="fecha_hora" name="fecha_hora" required>
                </div>
                <div class="form-group">
                    <label for="lugar">Lugar</label>
                    <input type="text" id="lugar" name="lugar">
                </div>
            </div>
             <div class="form-group">
                <label for="zona_id">Zona (Opcional)</label>
                <select id="zona_id" name="zona_id">
                    <option value="">-- General / Todas --</option>
                     <?php foreach($zones as $zone): ?>
                        <option value="<?php echo e($zone['id']); ?>"><?php echo e($zone['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="imagen_flyer">Imagen o Flyer</label>
                <input type="file" id="imagen_flyer" name="imagen_flyer" accept="image/*">
                <small>Sube una imagen para el evento. Si editas, solo sube una nueva para reemplazar la actual.</small>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" class="cta-button" id="modalSubmitBtn">Crear Evento</button>
            </div>
        </form>
    </div>
</div>


<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

