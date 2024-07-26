<?php
$serverName = "LAPTOP-QDHOGV8J"; // Nombre del servidor
$database = "DISFERRAGRO"; // Nombre de la base de datos

// Información de la conexión utilizando autenticación de Windows
$connectionInfo = array(
    "Database" => $database,
    "UID" => "", // Usuario vacío
    "PWD" => ""  // Contraseña vacía
);

// Conexión a la base de datos
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Verificar si la conexión fue exitosa
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// echo "Conexión exitosa a la base de datos DISFERRAGRO";
