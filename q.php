<?php
$database = __DIR__ . '/controllers/db/scanned_products.db';

try {
    $connSQLite = new PDO("sqlite:$database");
    $connSQLite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla de usuarios si no existe
    $connSQLite->exec("CREATE TABLE IF NOT EXISTS Usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL
    )");
} catch (PDOException $e) {
    die("Error al conectar a SQLite: " . $e->getMessage());
}
