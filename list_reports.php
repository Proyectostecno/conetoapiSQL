<?php
session_start();

// Incluir el archivo de conexión SQLite
include 'controllers/db_connection_sqlite.php';

// Obtener todos los reportes
$stmt = $connSQLite->prepare("SELECT * FROM Reportes");
$stmt->execute();
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow-md">
        <h1 class="text-2xl font-bold mb-4">Listado de Reportes</h1>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-4 py-2">ID Reporte</th>
                    <th class="px-4 py-2">Despacho</th>
                    <th class="px-4 py-2">Fecha</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportes as $reporte) : ?>
                    <tr>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($reporte['id']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($reporte['despacho']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($reporte['timestamp']); ?></td>
                        <td class="border px-4 py-2">
                            <a href="generate_report.php?id=<?php echo $reporte['id']; ?>" class="text-blue-600 hover:text-blue-800">Descargar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?php
// Cerrar la conexión
$connSQLite = null;
?>