<?php
$database = __DIR__ . '/db/scanned_products.db';

try {
    $connSQLite = new PDO("sqlite:$database");
    $connSQLite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a SQLite: " . $e->getMessage());
}
