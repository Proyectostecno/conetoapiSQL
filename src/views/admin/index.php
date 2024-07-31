<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

include '../../../controllers/db_connection.php';

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

    header('Location: scan/scan.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Reportes</title>
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- My CSS -->
    <link rel="stylesheet" href="../../resources/css/inicio_styles.css">
    <style>
        #historialList li {
            display: none;
        }

        #historialList li.visible {
            display: list-item;
        }

        .brand .text {
            color: #14B34B;
        }

        .brand .bx {
            color: #14B34B;
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Tecno Movil APP</span>
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="index.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Inicio</span>
                </a>
            </li>
            <li>
                <a href="scan/scan.php">
                    <i class='bx bx-barcode-reader'></i>
                    <span class="text">Verificación</span>
                </a>
            </li>

            <li>
                <a href="Greporte/list_reports.php">
                    <i class='bx bxs-report'></i>
                    <span class="text">Reportes</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="Rregistro/register.php">
                    <i class='bx bx-user'></i>
                    <span class="text">R usuarios</span>
                </a>
            </li>
            <li>
                <a href="Rregistro/list_users.php">
                    <i class='bx bx-group'></i>
                    <span class="text">Listado usuarios</span>
                </a>
            </li>
            <li>
                <a href="../../../controllers/logout.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Cerrar sesión</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <i class='bx bx-menu'></i>
            <a href="#" class="nav-link">Categorías</a>
        </nav>
        <!-- MAIN -->
        <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow-md mt-6">
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
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="../../resources/js/Inicio_script.js"></script>
</body>

</html>
<?php
// Cerrar la conexión
sqlsrv_close($conn);
?>