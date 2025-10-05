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
        $this->Cell(30, 10, utf8_decode('Padrón General de Votantes'), 0, 0, 'C');
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
$pdf->AddPage('L', 'A4'); // 'L' para orientación horizontal (Landscape)
$pdf->SetFont('Arial', '', 12);

// Título principal del documento
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Listado General de Votantes Registrados por Zona'), 0, 1, 'C');
$pdf->Ln(10);

try {
    // Consulta SQL para obtener todos los votantes, agrupados por zona
    $sql = "SELECT v.nombre, v.dni, v.telefono, v.direccion, v.estado, z.nombre as zona_nombre 
            FROM votantes v 
            LEFT JOIN zonas z ON v.zona_id = z.id 
            ORDER BY z.nombre ASC, v.nombre ASC";
    
    $result = $conn->query($sql);

    $current_zone = null;

    if ($result->num_rows > 0) {
        while ($voter = $result->fetch_assoc()) {
            // Si la zona cambia, imprimir un nuevo encabezado de zona
            if ($voter['zona_nombre'] !== $current_zone) {
                $current_zone = $voter['zona_nombre'];
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->Cell(0, 10, utf8_decode('Zona: ' . ($current_zone ?: 'Sin Asignar')), 1, 1, 'L', true);
                
                // Cabecera de la tabla para cada grupo
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(75, 7, 'Nombre Completo', 1, 0, 'C');
                $pdf->Cell(25, 7, 'DNI', 1, 0, 'C');
                $pdf->Cell(30, 7, utf8_decode('Teléfono'), 1, 0, 'C');
                $pdf->Cell(115, 7, utf8_decode('Dirección'), 1, 0, 'C');
                $pdf->Cell(30, 7, 'Estado', 1, 1, 'C');
            }

            // Contenido de la tabla
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(75, 7, utf8_decode($voter['nombre']), 1);
            $pdf->Cell(25, 7, utf8_decode($voter['dni']), 1);
            $pdf->Cell(30, 7, utf8_decode($voter['telefono']), 1);
            $pdf->Cell(115, 7, utf8_decode($voter['direccion']), 1);
            $pdf->Cell(30, 7, utf8_decode($voter['estado']), 1);
            $pdf->Ln();
        }
    } else {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'No se encontraron votantes registrados en el sistema.', 0, 1);
    }

} catch (Exception $e) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(255, 0, 0); // Color rojo para errores
    $pdf->MultiCell(0, 10, utf8_decode("Error al generar el reporte: " . $e->getMessage()));
}


// --- Salida del PDF ---
// 'D' para forzar la descarga, 'I' para mostrar en el navegador.
$pdf->Output('D', 'Padron_General_por_Zona.pdf');
?>
