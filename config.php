<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$host = "aws-1-us-east-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.pljfgyqtlgoiftddqlpi";
$password = "12926657Lp*"; // la de Supabase

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error de conexión",
        "detalle" => $e->getMessage()
    ]);
    exit;
}
