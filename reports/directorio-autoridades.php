<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

// Verificación de seguridad: Solo los administradores pueden generar este reporte.
if ($_SESSION['user_role'] !== 'admin') {
    die("Acceso denegado. Esta función está reservada para administradores.");
}

// Clase personalizada para el PDF con cabecera y pie de página
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo de la campaña
        $this->Image(__DIR__ . '/../public/images/logo.png', 10, 8, 20);
        // Fuente Arial Bold 15
        $this->SetFont('Arial', 'B', 15);
        // Mover el título a la derecha
        $this->Cell(80);
        // Título del reporte
        $this->Cell(30, 10, utf8_decode('Directorio de Autoridades'), 0, 0, 'C');
        // Fecha de generación
        $this->SetFont('Arial', '', 10);
        $this->Cell(80, 10, 'Generado el: ' . date('d/m/Y'), 0, 0, 'R');
        // Salto de línea
        $this->Ln(25);
    }

    // Pie de página
    function Footer()
    {
        // Posicionamiento a 1.5 cm del final
        $this->SetY(-15);
        // Fuente Arial Italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// --- Inicio de la generación del PDF ---
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('L', 'A4'); // 'L' para orientación horizontal (Landscape)
$pdf->SetFont('Arial', '', 12);

// Título principal del documento
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Directorio de Administradores y Coordinadores de Campaña'), 0, 1, 'C');
$pdf->Ln(10);

// --- Cabecera de la tabla ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(220, 220, 220); // Un gris claro para el fondo de la cabecera
$pdf->Cell(80, 10, 'Nombre Completo', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Correo Electr_nico', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Rol', 1, 0, 'C', true);
$pdf->Cell(85, 10, 'Zonas Asignadas', 1, 1, 'C', true); // Nueva l_nea al final

// --- Contenido de la tabla ---
$pdf->SetFont('Arial', '', 10);

try {
    // Consulta SQL para obtener administradores y coordinadores
    $sql = "SELECT u.id, u.name, u.email, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name IN ('admin', 'coordinador') AND u.approved = 1
            ORDER BY r.name, u.name ASC";
    
    $result = $conn->query($sql);

    while ($user = $result->fetch_assoc()) {
        $pdf->Cell(80, 10, utf8_decode($user['name']), 1);
        $pdf->Cell(70, 10, utf8_decode($user['email']), 1);
        $pdf->Cell(40, 10, utf8_decode(ucfirst($user['role_name'])), 1);

        // Obtener las zonas asignadas para este usuario (si es coordinador)
        $zonas_asignadas = 'N/A';
        if ($user['role_name'] === 'coordinador') {
            $stmt_zonas = $conn->prepare("SELECT z.nombre FROM zonas z JOIN coordinador_zona cz ON z.id = cz.zona_id WHERE cz.user_id = ?");
            $stmt_zonas->bind_param("i", $user['id']);
            $stmt_zonas->execute();
            $result_zonas = $stmt_zonas->get_result();
            $zonas = [];
            while($row_zona = $result_zonas->fetch_assoc()){
                $zonas[] = $row_zona['nombre'];
            }
            if (!empty($zonas)) {
                $zonas_asignadas = implode(', ', $zonas);
            } else {
                 $zonas_asignadas = 'Ninguna asignada';
            }
        }
        
        $pdf->Cell(85, 10, utf8_decode($zonas_asignadas), 1);
        $pdf->Ln(); // Nueva l_nea
    }

} catch (Exception $e) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(255, 0, 0); // Color rojo para errores
    $pdf->MultiCell(0, 10, utf8_decode("Error al generar el reporte: " . $e->getMessage()));
}


// --- Salida del PDF ---
// 'D' para forzar la descarga, 'I' para mostrar en el navegador.
$pdf->Output('D', 'Directorio_Autoridades_Campa_a.pdf');
?>
