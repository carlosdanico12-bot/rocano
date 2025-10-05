<?php
$page_title = 'Contacto';
require_once __DIR__ . '/../../includes/config.php';

$errors = [];
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $errors[] = "Nombre, email y mensaje son campos obligatorios.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Por favor, ingresa un correo electrónico válido.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success_message = "¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.";
            } else {
                $errors[] = "Hubo un error al enviar tu mensaje. Por favor, inténtalo de nuevo.";
            }
        } catch (Exception $e) {
            $errors[] = "Error del sistema. Inténtalo más tarde.";
        }
    }
}

require_once __DIR__ . '/../../includes/header_public.php';
?>
<main class="container">
    <section class="contact-section">
        <h1 class="section-title">Ponte en Contacto</h1>
        <p class="section-subtitle">¿Tienes alguna pregunta, sugerencia o quieres sumarte al equipo? ¡Nos encantaría escucharte!</p>
        <div class="contact-wrapper animated-section">
            <div class="contact-form card">
                <h3>Envíanos un Mensaje</h3>
                <?php if ($success_message): ?>
                    <div class="form-message success"><?php echo e($success_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="form-message error"><?php foreach ($errors as $error) echo "<p>" . e($error) . "</p>"; ?></div>
                <?php endif; ?>
                <form id="contactForm" action="" method="POST" novalidate>
                    <div class="form-group-grid">
                        <input type="text" id="name" name="name" placeholder="Tu Nombre Completo" required>
                        <input type="email" id="email" name="email" placeholder="Tu Correo Electrónico" required>
                    </div>
                    <input type="text" id="subject" name="subject" placeholder="Asunto del Mensaje">
                    <textarea id="message" name="message" rows="6" placeholder="Escribe tu mensaje aquí..." required></textarea>
                    <button type="submit" class="cta-button">Enviar Mensaje</button>
                </form>
            </div>
            <div class="contact-info">
                <h3>Nuestra Oficina Central</h3>
                <p><i class="fas fa-map-marker-alt"></i> Av. Tito Jaime 567, Tingo María, Huánuco</p>
                <p><i class="fas fa-phone-alt"></i> (062) 56-2323</p>
                <p><i class="fas fa-envelope"></i> contacto@ingcori.com</p>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15769.73031078736!2d-76.01217845!3d-9.2941566!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91a783d0b9e59891%3A0x6b84515102553c0a!2sTingo%20Mar%C3%ADa!5e0!3m2!1ses-419!2spe!4v1663632420499!5m2!1ses-419!2spe" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/../../includes/footer_public.php'; ?>
