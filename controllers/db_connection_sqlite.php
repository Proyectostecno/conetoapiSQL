<?php
$database = __DIR__ . '/db/scanned_products.db';

try {
    $connSQLite = new PDO("sqlite:$database");
    $connSQLite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear la tabla de productos escaneados si no existe
    $connSQLite->exec("
        CREATE TABLE IF NOT EXISTS ProductosEscaneados (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            despacho TEXT NOT NULL,
            idReferencia TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Crear la tabla de reportes si no existe
    $connSQLite->exec("
        CREATE TABLE IF NOT EXISTS Reportes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            despacho TEXT NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Crear la tabla de detalles del reporte si no existe
    $connSQLite->exec("
        CREATE TABLE IF NOT EXISTS ReporteDetalles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reporte_id INTEGER NOT NULL,
            idReferencia TEXT NOT NULL,
            descripcion TEXT NOT NULL,
            cantidad INTEGER NOT NULL,
            FOREIGN KEY (reporte_id) REFERENCES Reportes(id)
        )
    ");
} catch (PDOException $e) {
    die("Error al conectar a SQLite: " . $e->getMessage());
}
