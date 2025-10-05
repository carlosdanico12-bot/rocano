<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Acceso denegado.");
}

class PDF extends FPDF {
    function Header() {
        $this->Image(__DIR__ . '/../public/images/logo.png', 10, 8, 20);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, utf8_decode('Reporte de Campaña'), 0, 0, 'C');
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

switch ($report_type) {
    case 'votantes_por_zona':
        // ... (código del reporte, ya funcional)
        break;

    case 'indecisos_por_sector':
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,utf8_decode('Reporte de Votantes Indecisos'),0,1,'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(60, 10, 'Nombre', 1);
        $pdf->Cell(30, 10, utf8_decode('Teléfono'), 1);
        $pdf->Cell(50, 10, 'Direccion', 1);
        $pdf->Cell(50, 10, 'Zona', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
        $sql = "SELECT v.nombre, v.telefono, v.direccion, z.nombre as zona_nombre FROM votantes v LEFT JOIN zonas z ON v.zona_id = z.id WHERE v.estado = 'Indeciso' ORDER BY z.nombre";
        $result = $conn->query($sql);
        while($row = $result->fetch_assoc()) {
            $pdf->Cell(60, 8, utf8_decode($row['nombre']), 1);
            $pdf->Cell(30, 8, utf8_decode($row['telefono']), 1);
            $pdf->Cell(50, 8, utf8_decode($row['direccion']), 1);
            $pdf->Cell(50, 8, utf8_decode($row['zona_nombre']), 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'Reporte_Indecisos.pdf');
        break;

    case 'tareas_por_voluntario':
        // ... (código del reporte)
        break;

    case 'directorio_voluntarios':
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,utf8_decode('Directorio de Voluntarios Activos'),0,1,'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(80, 10, 'Nombre Completo', 1);
        $pdf->Cell(60, 10, 'Email', 1);
        $pdf->Cell(50, 10, 'Zona Asignada', 1);
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
        $sql = "SELECT u.name, u.email, z.nombre as zona_nombre FROM users u LEFT JOIN zonas z ON u.zona_id = z.id WHERE u.role_id = 3 AND u.approved = 1 ORDER BY u.name";
        $result = $conn->query($sql);
        while($row = $result->fetch_assoc()) {
            $pdf->Cell(80, 8, utf8_decode($row['name']), 1);
            $pdf->Cell(60, 8, utf8_decode($row['email']), 1);
            $pdf->Cell(50, 8, utf8_decode($row['zona_nombre']), 1);
            $pdf->Ln();
        }
        $pdf->Output('D', 'Directorio_Voluntarios.pdf');
        break;

    default:
        $pdf->Cell(0, 10, 'Tipo de reporte no valido.', 0, 1);
        $pdf->Output();
        break;
}
?>

