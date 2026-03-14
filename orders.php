<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo json_encode(["error" => "Datos inválidos"]);
  exit;
}

$tamano = $data["tamano"];
$tipoVenta = $data["tipoVenta"]; // U o D
$cantidad = (int)$data["cantidad"];
$precio_unitario = (float)$data["precio_unitario"];

$cantidad_velas = ($tipoVenta === "D") 
  ? $cantidad * 12 
  : $cantidad;

$subtotal = $cantidad_velas * $precio_unitario;

try {
  $pdo->beginTransaction();

  // 1️⃣ Obtener vela
  $sqlVela = "SELECT id_velas, stock_velas FROM velas WHERE tamano = :tamano AND activo = true";
  $stmt = $pdo->prepare($sqlVela);
  $stmt->execute([":tamano" => $tamano]);
  $vela = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$vela) {
    throw new Exception("Tamaño no encontrado");
  }

  if ($vela["stock_velas"] < $cantidad_velas) {
    throw new Exception("Stock insuficiente");
  }

  $id_velas = $vela["id_velas"];

  // 2️⃣ Crear pedido
  $sqlPedido = "INSERT INTO pedido (total) VALUES (:total) RETURNING id_pedido";
  $stmt = $pdo->prepare($sqlPedido);
  $stmt->execute([":total" => $subtotal]);
  $id_pedido = $stmt->fetchColumn();

  // 3️⃣ Crear detalle
  $sqlDetalle = "
    INSERT INTO detalle_pedido 
    (id_pedido, id_velas, cantidad_velas, precio_unitario, subtotal)
    VALUES (:id_pedido, :id_velas, :cantidad, :precio, :subtotal)
  ";

  $stmt = $pdo->prepare($sqlDetalle);
  $stmt->execute([
    ":id_pedido" => $id_pedido,
    ":id_velas" => $id_velas,
    ":cantidad" => $cantidad_velas,
    ":precio" => $precio_unitario,
    ":subtotal" => $subtotal
  ]);

  // 4️⃣ Actualizar stock
  $sqlStock = "
    UPDATE velas
    SET stock_velas = stock_velas - :cantidad
    WHERE id_velas = :id_velas
  ";

  $stmt = $pdo->prepare($sqlStock);
  $stmt->execute([
    ":cantidad" => $cantidad_velas,
    ":id_velas" => $id_velas
  ]);

  $pdo->commit();

  echo json_encode([
    "ok" => true,
    "id_pedido" => $id_pedido
  ]);

} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode([
    "error" => true,
    "mensaje" => $e->getMessage()
  ]);
}
