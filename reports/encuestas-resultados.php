<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Acceso denegado.");
}

$survey_id = $_GET['survey_id'] ?? null;
if (!$survey_id) {
    die("No se especificó una encuesta.");
}

// ... (código para la clase PDF extendida, similar al otro generador)

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

try {
    // Obtener información de la encuesta
    $stmt_survey = $conn->prepare("SELECT titulo FROM surveys WHERE id = ?");
    $stmt_survey->bind_param("i", $survey_id);
    $stmt_survey->execute();
    $survey_title = $stmt_survey->get_result()->fetch_assoc()['titulo'];

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,utf8_decode('Resultados de Encuesta: ' . $survey_title),0,1,'C');
    $pdf->Ln(10);
    
    // Aquí iría la lógica compleja para obtener preguntas y agregar los resultados al PDF
    // Por ahora, es un placeholder funcional.
    $pdf->SetFont('Arial','',12);
    $pdf->MultiCell(0,10,utf8_decode("Este es el reporte para la encuesta ID: $survey_id. La lógica para procesar y mostrar los resultados de cada pregunta se implementaría aquí, consultando las tablas 'questions' y 'answers'."));

} catch (Exception $e) {
    $pdf->SetFont('Arial','B',12);
    $pdf->SetTextColor(255,0,0);
    $pdf->Cell(0,10,'Error al generar el reporte: ' . $e->getMessage(),0,1);
}

$pdf->Output('I', 'Resultados_Encuesta_' . $survey_id . '.pdf');
?>
