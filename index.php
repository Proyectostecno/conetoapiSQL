<?php
session_start();

// Incluir el archivo de conexión
include 'controllers/db_connection.php';

// Obtener los despachos agrupados por número único
$despachos = array();
$query = "SELECT DISTINCT numero FROM despachofacturas WHERE anulado = 0";
$result = sqlsrv_query($conn, $query);
if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $despachos[] = $row['numero'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['despacho'])) {
    $despacho = $_POST['despacho'];

    // Obtener las facturas del despacho seleccionado y agrupar productos
    $query = "
        SELECT Numero, IdReferencia, Descripcion, SUM(Cantidad) AS Cantidad
        FROM Facturas2 
        WHERE Numero IN (SELECT documento FROM despachofacturas WHERE numero = ?)
        GROUP BY Numero, IdReferencia, Descripcion";
    $params = array($despacho);
    $result = sqlsrv_query($conn, $query, $params);

    if ($result === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $productosPermitidos = [];
    $productosDespacho = [];

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $productosPermitidos[] = $row['IdReferencia'];
        $productosDespacho[$row['IdReferencia']] = [
            'descripcion' => $row['Descripcion'],
            'cantidad' => $row['Cantidad']
        ];
    }

    $_SESSION['despacho'] = $despacho;
    $_SESSION['productosPermitidos'] = $productosPermitidos;
    $_SESSION['productosDespacho'] = $productosDespacho;

    header('Location: scan.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Despacho</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow-md">
        <h1 class="text-2xl font-bold mb-4">Seleccionar Despacho</h1>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="despacho" class="block text-sm font-medium text-gray-700">Despacho:</label>
                <select id="despacho" name="despacho" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Seleccione un despacho</option>
                    <?php foreach ($despachos as $numero) : ?>
                        <option value="<?php echo $numero; ?>"><?php echo $numero; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Buscar Facturas
                </button>
            </div>
        </form>
    </div>
</body>

</html>
<?php
// Cerrar la conexión
sqlsrv_close($conn);
?>