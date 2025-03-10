<?php

error_log("El archivo register.php se está ejecutando.");  // Verifica si el archivo PHP se está ejecutando

error_reporting(E_ALL);  // Habilitar la visualización de todos los errores
ini_set('display_errors', 1);  // Mostrar los errores directamente en el navegador

header('Content-Type: application/json');

// Configuración de la base de datos
$host = 'localhost';
$port = '5432';
$dbname = 'dbname';
$user = 'user';
$password = 'password';
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    error_log("Error al conectar a la base de datos: " . pg_last_error());
    echo json_encode(["status" => "error", "message" => "Error de conexión a la base de datos."]);
    exit;
} else {
    error_log("Conexión exitosa a la base de datos.");
}

// Manejo de solicitudes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['check_codigo'])) {
        $codigo = trim($_GET['check_codigo']);
        if (verificarCodigoExistente($conn, $codigo)) {
            echo json_encode(["exists" => true]); // El código ya existe
        } else {
            echo json_encode(["exists" => false]); // El código no existe
        }
        exit;
    }

    if (!isset($_GET['type'])) {
        echo json_encode(["status" => "error", "message" => "Tipo de solicitud no especificado."]);
        exit;
    }

    switch ($_GET['type']) {
        case 'bodegas':
            cargarBodegas($conn);
            break;
        case 'sucursales':
            cargarSucursales($conn);
            break;
        case 'monedas':
            cargarMonedas($conn);
            break;
        case 'materiales':
            cargarMateriales($conn);  // Llamamos a la función para cargar los materiales
            break;
        default:
            echo json_encode(["status" => "error", "message" => "Tipo de solicitud no válido."]);
            break;
    }
    exit;
}


// Manejo de solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    registrarProducto($conn);
    exit;
}
// Función para verificar si el código de producto ya existe
function verificarCodigoExistente($conn, $codigo) {
    $query = "SELECT COUNT(*) FROM productos WHERE codigo = $1";
    $result = pg_query_params($conn, $query, [$codigo]);

    if (!$result) {
        error_log("Error al verificar el código del producto: " . pg_last_error($conn));
        echo json_encode(["status" => "error", "message" => "Error al verificar el código del producto."]);
        exit;
    }

    $count = pg_fetch_result($result, 0, 0);
    return $count > 0; // Retorna true si el código ya existe, de lo contrario false
}

// Función para obtener bodegas
function cargarBodegas($conn) {
    $query = "SELECT id, nombre FROM bodegas ORDER BY nombre";
    $result = pg_query($conn, $query);

    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Error al obtener bodegas."]);
        exit;
    }

    $bodegas = pg_fetch_all($result) ?: [];
    echo json_encode(["status" => "success", "data" => $bodegas]);
}

// Función para obtener sucursales
function cargarSucursales($conn) {
    $query = "SELECT id, nombre FROM sucursales ORDER BY nombre";
    $result = pg_query($conn, $query);

    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Error al obtener sucursales."]);
        exit;
    }

    $sucursales = pg_fetch_all($result) ?: [];
    echo json_encode(["status" => "success", "data" => $sucursales]);
}

// Función para obtener monedas
function cargarMonedas($conn) {
    $query = "SELECT id, nombre FROM monedas ORDER BY nombre";
    $result = pg_query($conn, $query);

    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Error al obtener monedas."]);
        exit;
    }

    $monedas = pg_fetch_all($result) ?: [];
    echo json_encode(["status" => "success", "data" => $monedas]);
}
// Función para obtener materiales
function cargarMateriales($conn) {
    $query = "SELECT id, nombre FROM materiales ORDER BY nombre";  // Consulta para obtener los materiales
    $result = pg_query($conn, $query);

    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Error al obtener materiales."]);
        exit;
    }

    $materiales = pg_fetch_all($result) ?: [];
    echo json_encode(["status" => "success", "data" => $materiales]);
}

// Función para registrar un producto
function registrarProducto($conn) {
    error_log("Función registrarProducto ejecutada");

    if (empty($_POST)) {
        error_log("No se recibieron datos en \$_POST");
        echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
        exit;
    }

    error_log("Datos recibidos: " . print_r($_POST, true));

    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $bodega_id = $_POST['bodega'] ?? '';
    $sucursal_id = $_POST['sucursal'] ?? '';
    $moneda_id = $_POST['moneda'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $descripcion = trim($_POST['descripcion'] ?? '');
    $materiales = $_POST['material'] ?? [];

    // Validaciones
    if (empty($codigo) || empty($nombre) || empty($bodega_id) || empty($sucursal_id) || empty($moneda_id) || empty($precio) || empty($descripcion)) {
        error_log("Faltan datos obligatorios.");
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios."]);
        exit;
    }

    // Insertar producto
    $query_insert = "INSERT INTO productos (codigo, nombre, bodega_id, sucursal_id, moneda_id, precio, descripcion) 
                     VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id";
    $result_insert = pg_query_params($conn, $query_insert, [$codigo, $nombre, $bodega_id, $sucursal_id, $moneda_id, $precio, $descripcion]);

    if (!$result_insert) {
        error_log("Error en la inserción del producto: " . pg_last_error($conn));
        echo json_encode(["status" => "error", "message" => "Error al registrar el producto."]);
        exit;
    }

    $producto_row = pg_fetch_assoc($result_insert);
    if (!$producto_row) {
        error_log("No se obtuvo ID del producto.");
        echo json_encode(["status" => "error", "message" => "No se pudo obtener el ID del producto."]);
        exit;
    }

    $producto_id = $producto_row['id'];
    error_log("Producto registrado con ID: $producto_id");

    // Paso 1: Obtener los IDs de los materiales correctamente
    if (!empty($materiales)) {
        // Convertir array de PHP al formato PostgreSQL ({1,2,3})
        $materiales_pg = '{' . implode(',', $materiales) . '}';

        $query_materials = "SELECT id, nombre FROM materiales WHERE id = ANY($1::int[])";
        $result_materials = pg_query_params($conn, $query_materials, [$materiales_pg]);

        if (!$result_materials) {
            error_log("Error al obtener los materiales: " . pg_last_error($conn));
            echo json_encode(["status" => "error", "message" => "Error al obtener los materiales."]);
            exit;
        }
        // Mapear los IDs de materiales
        $material_ids = [];
        while ($row = pg_fetch_assoc($result_materials)) {
            $material_ids[$row['id']] = $row['id'];
        }

        // Paso 2: Insertar cada relación producto-material
        foreach ($materiales as $material) {
            if (isset($material_ids[$material])) {
                $material_id = $material_ids[$material];

                // Verificar si la relación ya existe
                $query_check = "SELECT COUNT(*) FROM producto_material WHERE producto_id = $1 AND material_id = $2";
                $result_check = pg_query_params($conn, $query_check, [$producto_id, $material_id]);

                if ($result_check) {
                    $count = pg_fetch_result($result_check, 0, 0);
                    if ($count == 0) { // Solo inserta si no existe
                        $query_relacion = "INSERT INTO producto_material (producto_id, material_id) VALUES ($1, $2)";
                        $result_relacion = pg_query_params($conn, $query_relacion, [$producto_id, $material_id]);

                        if (!$result_relacion) {
                            error_log("Error al asociar material '$material_id' al producto '$producto_id': " . pg_last_error($conn));
                        } else {
                            error_log("Material '$material_id' asociado al producto '$producto_id' correctamente.");
                        }
                    } else {
                        error_log("La relación producto-material ya existe para producto '$producto_id' y material '$material_id'.");
                    }
                } else {
                    error_log("Error al verificar existencia de la relación producto-material: " . pg_last_error($conn));
                }
            } else {
                error_log("Material '$material' no encontrado en la base de datos.");
            }
        }

    }

    echo json_encode(["status" => "success", "message" => "Producto registrado con éxito.", "producto_id" => $producto_id]);
}



// Cerrar conexión
pg_close($conn);
?>
