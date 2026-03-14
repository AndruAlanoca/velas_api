<?php
require_once "config.php";

echo json_encode([
    "ok" => true,
    "mensaje" => "Conectado a Supabase correctamente"
]);