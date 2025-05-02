<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtener el código del producto desde la URL
$codigoProducto = $_GET['codigo'] ?? '';

if (trim($codigoProducto) === '') {
    enviarRespuesta(400, "Solicitud incorrecta: código no proporcionado");
    exit;
}

// Configuración de la base de datos
$servidor = "localhost";
$usuarioBD = "root";
$claveBD = "";
$nombreBD = "pos";

// Conexión
$conexion = new mysqli($servidor, $usuarioBD, $claveBD, $nombreBD);

if ($conexion->connect_error) {
    enviarRespuesta(500, "Error de servidor: no se pudo establecer la conexión");
    exit;
}

// Consulta preparada
$consulta = $conexion->prepare("
    SELECT 
        producto_codigo,
        producto_nombre,
        producto_precio,
        producto_imagen
    FROM productos
    WHERE producto_codigo = ?
");

$consulta->bind_param("s", $codigoProducto);

if (!$consulta->execute()) {
    enviarRespuesta(500, "Error al ejecutar la consulta");
    $consulta->close();
    $conexion->close();
    exit;
}

$resultado = $consulta->get_result();

if ($producto = $resultado->fetch_assoc()) {
    enviarRespuesta(200, "Producto encontrado", $producto);
} else {
    enviarRespuesta(404, "Producto no encontrado");
}

$consulta->close();
$conexion->close();

// -----------------------------------------------
// Función para estructurar la respuesta en JSON
// -----------------------------------------------
function enviarRespuesta(int $codigoHTTP, string $mensaje, array $datos = null): void {
    http_response_code($codigoHTTP);
    $respuesta = [
        "estado"  => $codigoHTTP,
        "mensaje" => $mensaje
    ];
    if (!is_null($datos)) {
        $respuesta["resultado"] = $datos;
    }
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
}
