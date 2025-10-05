<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

if ($_SESSION['user_role'] !== 'coordinador') {
    die("Acceso denegado.");
}

$coordinator_id = $_SESSION['user_id'];

// Obtener las zonas asignadas al coordinador
$coordinator_zones_ids = [];
$stmt_zones = $conn->prepare("SELECT zona_id FROM coordinador_zona WHERE user_id = ?");
$stmt_zones->bind_param("i", $coordinator_id);
$stmt_zones->execute();
$result_zones = $stmt_zones->get_result();
while ($row = $result_zones->fetch_assoc()) {
    $coordinator_zones_ids[] = $row['zona_id'];
}
if (empty($coordinator_zones_ids)) {
    die("No tienes zonas asignadas para generar reportes.");
}

// Clase extendida para Header y Footer
class PDF extends FPDF {
    function Header() {
        $this->Image(__DIR__ . '/../../public/images/logo.png', 10, 8, 20);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, utf8_decode('Reporte de Coordinador'), 0, 0, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(80, 10, 'Fecha: ' . date('d/m/Y'), 0, 0, 'R');
        $this->Ln(25);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$report_type = $_GET['report'] ?? '';
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$placeholders = implode(',', array_fill(0, count($coordinator_zones_ids), '?'));

switch ($report_type) {
    case 'votantes_mis_zonas':
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('Padrón de Votantes en mis Zonas'), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 10, 'DNI', 1, 0, 'C');
        $pdf->Cell(70, 10, 'Nombre Completo', 1, 0, 'C');
        $pdf->Cell(35, 10, utf8_decode('Teléfono'), 1, 0, 'C');
        $pdf->Cell(60, 10, 'Zona', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $sql = "SELECT v.dni, v.nombre, v.telefono, z.nombre as zona_nombre 
                FROM votantes v 
                LEFT JOIN zonas z ON v.zona_id = z.id 
                WHERE v.zona_id IN ($placeholders)
                ORDER BY z.nombre, v.nombre";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($coordinator_zones_ids)), ...$coordinator_zones_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            $pdf->Cell(25, 8, utf8_decode($row['dni']), 1);
            $pdf->Cell(70, 8, utf8_decode($row['nombre']), 1);
            $pdf->Cell(35, 8, utf8_decode($row['telefono']), 1);
            $pdf->Cell(60, 8, utf8_decode($row['zona_nombre']), 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'Reporte_Votantes_Mis_Zonas.pdf');
        break;

    // Puedes añadir aquí la lógica para los otros reportes del coordinador
    default:
        $pdf->Cell(0, 10, 'Tipo de reporte no valido.', 0, 1);
        $pdf->Output();
        break;
}
?>
