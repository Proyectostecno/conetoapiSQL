<?php
session_start();


include '../../../../controllers/db_connection_sqlite.php';
require('../../../resources/fpdf186/fpdf.php');

if (!isset($_GET['id'])) {
    die('ID de reporte no especificado.');
}

$reporte_id = $_GET['id'];

// Obtener los detalles del reporte
$stmt = $connSQLite->prepare("SELECT * FROM ReporteDetalles WHERE reporte_id = ?");
$stmt->execute([$reporte_id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($detalles)) {
    die('No se encontraron detalles para este reporte.');
}

// Crear el PDF
class PDF extends FPDF
{
    function Header()
    {
        // Título
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Reporte de Productos Escaneados', 0, 1, 'C');
    }

    function Footer()
    {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'ID Reporte: ' . $reporte_id, 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 10, 'ID Referencia', 1);
$pdf->Cell(80, 10, 'Descripción', 1);
$pdf->Cell(30, 10, 'Cantidad', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
foreach ($detalles as $detalle) {
    $pdf->Cell(40, 10, $detalle['idReferencia'], 1);
    $pdf->Cell(80, 10, $detalle['descripcion'], 1);
    $pdf->Cell(30, 10, $detalle['cantidad'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'reporte_' . $reporte_id . '.pdf');
