<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../index.php');
    exit();
}

// Incluir el archivo de conexión SQLite
include '../../../../controllers/db_connection_sqlite.php';

// Obtener el ID del reporte
$reporteId = $_GET['id'] ?? null;

if ($reporteId) {
    // Obtener los detalles del reporte
    $stmt = $connSQLite->prepare("SELECT * FROM ReporteDetalles WHERE reporte_id = :id");
    $stmt->bindParam(':id', $reporteId, PDO::PARAM_INT);
    $stmt->execute();
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($detalles) {
        // Construir el contenido del reporte
        $contenidoReporte = '<thead class="bg-gray-50">';
        $contenidoReporte .= '<tr>';
        $contenidoReporte .= '<th class="px-6 py-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-600 font-medium uppercase tracking-wider">ID Referencia</th>';
        $contenidoReporte .= '<th class="px-6 py-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-600 font-medium uppercase tracking-wider">Descripción</th>';
        $contenidoReporte .= '<th class="px-6 py-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-600 font-medium uppercase tracking-wider">Cantidad</th>';
        $contenidoReporte .= '</tr>';
        $contenidoReporte .= '</thead>';
        $contenidoReporte .= '<tbody class="bg-white">';
        foreach ($detalles as $detalle) {
            $contenidoReporte .= '<tr>';
            $contenidoReporte .= '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">' . htmlspecialchars($detalle['idReferencia']) . '</td>';
            $contenidoReporte .= '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">' . htmlspecialchars($detalle['descripcion']) . '</td>';
            $contenidoReporte .= '<td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">' . htmlspecialchars($detalle['cantidad']) . '</td>';
            $contenidoReporte .= '</tr>';
        }
        $contenidoReporte .= '</tbody>';
        echo $contenidoReporte;
    } else {
        echo "Reporte no encontrado.";
    }
} else {
    echo "ID de reporte no especificado.";
}

// Cerrar la conexión
$connSQLite = null;
