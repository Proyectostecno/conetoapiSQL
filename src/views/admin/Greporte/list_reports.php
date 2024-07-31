<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../../../index.php');
    exit();
}
// Incluir el archivo de conexión SQLite
include '../../../../controllers/db_connection_sqlite.php';

// Obtener todos los reportes
$stmt = $connSQLite->prepare("SELECT * FROM Reportes");
$stmt->execute();
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la lista única de despachos
$despachos = [];
foreach ($reportes as $reporte) {
    if (!in_array($reporte['despacho'], $despachos)) {
        $despachos[] = $reporte['despacho'];
    }
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
    <link rel="stylesheet" href="../../../resources/css/inicio_styles.css">
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
            <li>
                <a href="../index.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Inicio</span>
                </a>
            </li>
            <li>
                <a href="../scan/scan.php">
                    <i class='bx bx-barcode-reader'></i>
                    <span class="text">Verificación</span>
                </a>
            </li>

            <li class="active">
                <a href="../Greporte/list_reports.php">
                    <i class='bx bxs-report'></i>
                    <span class="text">Reportes</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="../Rregistro/register.php">
                    <i class='bx bx-user'></i>
                    <span class="text">R usuarios</span>
                </a>
            </li>
            <li>
                <a href="../Rregistro/list_users.php">
                    <i class='bx bx-group'></i>
                    <span class="text">Listado usuarios</span>
                </a>
            </li>
            <li>
                <a href="../../../../controllers/logout.php" class="logout">
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
        <div class="max-w-4xl mx-auto bg-white p-8 rounded shadow-md">
            <h1 class="text-2xl font-bold mb-4">Listado de Reportes</h1>

            <!-- Filtros -->
            <div class="mb-4 flex space-x-4">
                <input type="date" id="filter-date" class="border p-2 rounded" placeholder="Filtrar por fecha">
                <select id="filter-despacho" class="border p-2 rounded">
                    <option value="">Filtrar por despacho</option>
                    <?php foreach ($despachos as $despacho) : ?>
                        <option value="<?php echo htmlspecialchars($despacho); ?>"><?php echo htmlspecialchars($despacho); ?></option>
                    <?php endforeach; ?>
                </select>
                <button onclick="applyFilters()" class="bg-blue-500 text-white px-4 py-2 rounded">Aplicar filtros</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-600 font-medium uppercase tracking-wider">ID Reporte</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-600 font-medium uppercase tracking-wider">Despacho</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-600 font-medium uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-600 font-medium uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="report-table" class="bg-white">
                        <?php foreach ($reportes as $reporte) : ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($reporte['id']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($reporte['despacho']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200"><?php echo htmlspecialchars($reporte['timestamp']); ?></td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                    <a href="generate_report.php?id=<?php echo $reporte['id']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Descargar</a>
                                    <button onclick="viewReport(<?php echo $reporte['id']; ?>)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-4">Ver</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4 flex justify-center space-x-1">
                <button onclick="previousPage()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded">&laquo;</button>
                <div id="pagination-numbers" class="flex space-x-1"></div>
                <button onclick="nextPage()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded">&raquo;</button>
            </div>
        </div>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <!-- Modal -->
    <div id="reportModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 overflow-auto max-h-screen">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Contenido del Reporte</h3>
                    <div class="mt-2">
                        <input type="text" id="search" placeholder="Buscar..." class="border p-2 rounded w-full mb-4" onkeyup="searchInModal()">
                        <table id="modal-table" class="min-w-full bg-white border border-gray-200"></table>
                    </div>
                </div>
                <div id="modal-pagination" class="mt-4 flex justify-center space-x-1"></div>
                <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm" onclick="closeModal()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const rowsPerPage = 5;
        let currentPage = 1;
        let filteredReportes = <?php echo json_encode($reportes); ?>;
        const paginationNumbers = document.getElementById('pagination-numbers');

        function renderTable() {
            const tableBody = document.getElementById('report-table');
            tableBody.innerHTML = '';

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const paginatedReportes = filteredReportes.slice(start, end);

            for (const reporte of paginatedReportes) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">${reporte.id}</td>
                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">${reporte.despacho}</td>
                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">${reporte.timestamp}</td>
                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                        <a href="generate_report.php?id=${reporte.id}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Descargar</a>
                        <button onclick="viewReport(${reporte.id})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-4">Ver</button>
                    </td>
                `;
                tableBody.appendChild(row);
            }
            renderPagination();
        }

        function renderPagination() {
            paginationNumbers.innerHTML = '';
            const pageCount = Math.ceil(filteredReportes.length / rowsPerPage);
            for (let i = 1; i <= pageCount; i++) {
                const pageNumber = document.createElement('button');
                pageNumber.className = 'bg-gray-300 text-gray-700 px-3 py-1 rounded';
                pageNumber.innerText = i;
                pageNumber.onclick = () => goToPage(i);
                if (i === currentPage) {
                    pageNumber.classList.add('bg-blue-500', 'text-white');
                }
                paginationNumbers.appendChild(pageNumber);
            }
        }

        function nextPage() {
            const pageCount = Math.ceil(filteredReportes.length / rowsPerPage);
            if (currentPage < pageCount) {
                currentPage++;
                renderTable();
            }
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        }

        function goToPage(page) {
            currentPage = page;
            renderTable();
        }

        function applyFilters() {
            const dateFilter = document.getElementById('filter-date').value;
            const despachoFilter = document.getElementById('filter-despacho').value.toLowerCase();

            filteredReportes = <?php echo json_encode($reportes); ?>.filter(reporte => {
                const dateMatch = dateFilter ? reporte.timestamp.startsWith(dateFilter) : true;
                const despachoMatch = despachoFilter ? reporte.despacho.toLowerCase().includes(despachoFilter) : true;
                return dateMatch && despachoMatch;
            });

            currentPage = 1;
            renderTable();
        }

        function viewReport(id) {
            fetch(`view_report.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modal-table').innerHTML = data;
                    document.getElementById('reportModal').classList.remove('hidden');
                    modalCurrentPage = 1;
                    initModalPagination();
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }

        function searchInModal() {
            const searchQuery = document.getElementById('search').value.toLowerCase();
            const modalTable = document.getElementById('modal-table');
            const rows = modalTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const cells = Array.from(row.getElementsByTagName('td'));
                const match = cells.some(cell => cell.textContent.toLowerCase().includes(searchQuery));
                row.style.display = match ? '' : 'none';
            });

            modalCurrentPage = 1;
            initModalPagination();
        }

        function initModalPagination() {
            const modalTable = document.getElementById('modal-table');
            const rows = Array.from(modalTable.querySelectorAll('tbody tr'));
            const modalPagination = document.getElementById('modal-pagination');
            const visibleRows = rows.filter(row => row.style.display !== 'none');
            let modalCurrentPage = 1;
            const modalRowsPerPage = 5;

            function renderModalTable() {
                visibleRows.forEach((row, index) => {
                    row.style.display = (index >= (modalCurrentPage - 1) * modalRowsPerPage && index < modalCurrentPage * modalRowsPerPage) ? '' : 'none';
                });
                updateModalPagination();
            }

            function updateModalPagination() {
                modalPagination.innerHTML = '';
                const modalPageCount = Math.ceil(visibleRows.length / modalRowsPerPage);
                for (let i = 1; i <= modalPageCount; i++) {
                    const pageNumber = document.createElement('button');
                    pageNumber.className = 'bg-gray-300 text-gray-700 px-3 py-1 rounded';
                    pageNumber.innerText = i;
                    pageNumber.onclick = () => {
                        modalCurrentPage = i;
                        renderModalTable();
                    };
                    if (i === modalCurrentPage) {
                        pageNumber.classList.add('bg-blue-500', 'text-white');
                    }
                    modalPagination.appendChild(pageNumber);
                }
            }

            renderModalTable();
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderTable();
        });
    </script>

    <script src="../../../resources/js/Inicio_script.js"></script>
</body>

</html>
<?php
// Cerrar la conexión
$connSQLite = null;
?>