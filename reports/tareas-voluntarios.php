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
        $this->Image(__DIR__ . '/../public/images/logo.png', 10, 8, 20);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, utf8_decode('Reporte de Tareas por Voluntario'), 0, 0, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(80, 10, 'Generado el: ' . date('d/m/Y'), 0, 0, 'R');
        $this->Ln(25);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// --- Inicio de la generación del PDF ---
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('P', 'A4'); // 'P' para orientación vertical (Portrait)
$pdf->SetFont('Arial', '', 12);

// Título principal del documento
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Listado de Tareas Asignadas a Voluntarios'), 0, 1, 'C');
$pdf->Ln(10);

try {
    // Consulta SQL para obtener todas las tareas, agrupadas por voluntario
    $sql = "SELECT t.titulo, t.descripcion, t.fecha_limite, t.estado, u.name as voluntario_nombre
            FROM tasks t
            JOIN users u ON t.asignado_a = u.id
            WHERE u.role_id = 3 -- Asegurarnos que solo sean voluntarios
            ORDER BY u.name ASC, t.fecha_limite ASC";
    
    $result = $conn->query($sql);

    $current_volunteer = null;

    if ($result->num_rows > 0) {
        while ($task = $result->fetch_assoc()) {
            // Si el voluntario cambia, imprimir un nuevo encabezado de grupo
            if ($task['voluntario_nombre'] !== $current_volunteer) {
                $current_volunteer = $task['voluntario_nombre'];
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->Cell(0, 10, utf8_decode('Voluntario: ' . $current_volunteer), 1, 1, 'L', true);
                
                // Cabecera de la tabla para las tareas de este voluntario
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(90, 7, 'Titulo de la Tarea', 1, 0, 'C');
                $pdf->Cell(40, 7, 'Estado', 1, 0, 'C');
                $pdf->Cell(60, 7, 'Fecha Limite', 1, 1, 'C');
            }

            // Contenido de la tabla (las tareas)
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(90, 7, utf8_decode($task['titulo']), 1);
            $pdf->Cell(40, 7, utf8_decode($task['estado']), 1);
            $pdf->Cell(60, 7, date('d/m/Y', strtotime($task['fecha_limite'])), 1);
            $pdf->Ln();
        }
    } else {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'No se encontraron tareas asignadas a voluntarios en el sistema.', 0, 1);
    }

} catch (Exception $e) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(255, 0, 0); // Color rojo para errores
    $pdf->MultiCell(0, 10, utf8_decode("Error al generar el reporte: " . $e->getMessage()));
}


// --- Salida del PDF ---
// 'D' para forzar la descarga, 'I' para mostrar en el navegador.
$pdf->Output('D', 'Reporte_Tareas_por_Voluntario.pdf');
?>
