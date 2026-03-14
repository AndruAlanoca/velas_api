<?php
header("Content-Type: application/json");
require_once "config.php";

$sql = "
  SELECT id_velas, tamano, stock_velas, precio_unitario
  FROM velas
  WHERE activo = true
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

echo json_encode($stmt->fetchAll());
