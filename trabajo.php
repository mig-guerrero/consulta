<?php
// --- INICIO DEL CÓDIGO DE SEGURIDAD ---
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}
// --- FIN DEL CÓDIGO DE SEGURIDAD ---

// Inicializar variables para los campos
$cedula = $n1 = $n2 = $a1 = $a2 = $tel = $dir = "";
$mensaje = "";

// Procesar el formulario al hacer clic en un botón
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    
    // Recopilar datos del formulario
    $datos = [
        'accion' => $accion,
        'cedula' => $_POST['cedula'] ?? '',
        'nombre1' => $_POST['nombre1'] ?? '',
        'nombre2' => $_POST['nombre2'] ?? '',
        'apellido1' => $_POST['apellido1'] ?? '',
        'apellido2' => $_POST['apellido2'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'direccion' => $_POST['direccion'] ?? ''
    ];

    // Configurar el envío de datos al script de Python mediante JSON
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($datos),
            'timeout' => 5 
        ],
    ];
    
    $context  = stream_context_create($options);
    
    // El signo '@' evita que PHP muestre errores feos si Python está apagado
    $resultado = @file_get_contents('http://127.0.0.1:5000/procesar', false, $context);

    if ($resultado === false) {
        $respuesta_json = [
            "mensaje" => "Error: No se pudo conectar con el servidor Python. Asegúrate de ejecutar 'python trabajo.py'.",
            "datos" => null
        ];
    } else {
        $respuesta_json = json_decode($resultado, true);
    }

    // Asignar los valores devueltos a las variables del formulario
    if ($respuesta_json) {
        $mensaje = $respuesta_json['mensaje'];
        
        if ($accion == 'consultar' && isset($respuesta_json['datos'])) {
            $d = $respuesta_json['datos'];
            $cedula = $d['cedula'] ?? ''; 
            $n1 = $d['nombre1'] ?? ''; 
            $n2 = $d['nombre2'] ?? '';
            $a1 = $d['apellido1'] ?? ''; 
            $a2 = $d['apellido2'] ?? ''; 
            $tel = $d['telefono'] ?? ''; 
            $dir = $d['direccion'] ?? '';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #e0f7fa 0%, #fce4ec 100%);
            min-height: 100vh;
            display: flex; 
            justify-content: center; 
            align-items: center;
            padding: 20px; 
        }

        .contenedor { 
            width: 100%;
            max-width: 650px; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05), 0 5px 15px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
        }

        .titulo { 
            text-align: center; 
            font-size: 26px; 
            font-weight: 700; 
            color: #2d3a4b;
            margin-bottom: 30px; 
        }

        .fila { 
            display: flex; 
            margin-bottom: 18px; 
            align-items: center; 
            gap: 15px;
        }

        .label { 
            width: 130px; 
            font-weight: 500; 
            font-size: 14px; 
            color: #555;
        }

        .input-campo { 
            flex-grow: 1; 
            background-color: #f8f9fa; 
            border: 2px solid #e9ecef; 
            border-radius: 12px; 
            padding: 12px 15px; 
            font-size: 15px; 
            font-family: 'Poppins', sans-serif;
            color: #333;
            transition: all 0.3s ease;
        }

        .input-campo:focus {
            outline: none;
            border-color: #56cfe1; 
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(86, 207, 225, 0.1);
        }

        .panel-botones { 
            display: flex; 
            gap: 10px; 
            margin-top: 35px; 
            flex-wrap: wrap;
        }

        .btn { 
            padding: 12px 20px; 
            font-weight: 600; 
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: white; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            flex: 1; 
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none; /* Para el botón de salir */
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn-guardar { background-color: #56cfe1; }
        .btn-guardar:hover { background-color: #4abccf; }
        
        .btn-consultar { background-color: #2d3a4b; }
        .btn-consultar:hover { background-color: #1e2735; }
        
        .btn-editar { background-color: #ffb703; color: #2d3a4b; }
        .btn-editar:hover { background-color: #e6a500; }
        
        .btn-eliminar { background-color: #ff6b6b; }
        .btn-eliminar:hover { background-color: #fa5252; }

        .btn-salir { 
            background-color: #ffffff; 
            color: #555; 
            border: 2px solid #e9ecef; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-salir:hover { background-color: #f8f9fa; }

        .alert { 
            background: #d1f2eb;
            color: #0e6251; 
            padding: 15px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-weight: 500;
            border-radius: 12px;
            border: 1px solid #a3e4d7;
        }

        .alert-error { 
            background: #fadbd8;
            color: #78281f; 
            border: 1px solid #f5b7b1;
        }

        @media (max-width: 600px) {
            .fila { flex-direction: column; align-items: flex-start; gap: 5px; }
            .input-campo { width: 100%; }
            .btn { min-width: 45%; }
        }
    </style>
</head>
<body>

<div class="contenedor">
    <div class="titulo">FORMULARIO DE REGISTRO</div>
    
    <?php if($mensaje): ?>
        <div class="alert <?php echo (strpos($mensaje, 'Error') !== false) ? 'alert-error' : ''; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="fila">
            <span class="label">CEDULA</span>
            <input type="text" name="cedula" class="input-campo" value="<?php echo htmlspecialchars($cedula); ?>" required>
        </div>
        <div class="fila">
            <span class="label">NOMBRE 1</span>
            <input type="text" name="nombre1" class="input-campo" value="<?php echo htmlspecialchars($n1); ?>">
        </div>
        <div class="fila">
            <span class="label">NOMBRE 2</span>
            <input type="text" name="nombre2" class="input-campo" value="<?php echo htmlspecialchars($n2); ?>">
        </div>
        <div class="fila">
            <span class="label">APELLIDO 1</span>
            <input type="text" name="apellido1" class="input-campo" value="<?php echo htmlspecialchars($a1); ?>">
        </div>
        <div class="fila">
            <span class="label">APELLIDO 2</span>
            <input type="text" name="apellido2" class="input-campo" value="<?php echo htmlspecialchars($a2); ?>">
        </div>
        <div class="fila">
            <span class="label">TELEFONO</span>
            <input type="text" name="telefono" class="input-campo" value="<?php echo htmlspecialchars($tel); ?>">
        </div>
        <div class="fila">
            <span class="label">DIRECCION</span>
            <input type="text" name="direccion" class="input-campo" value="<?php echo htmlspecialchars($dir); ?>">
        </div>

        <div class="panel-botones">
            <button type="submit" name="accion" value="guardar" class="btn btn-guardar">GUARDAR</button>
            <button type="submit" name="accion" value="consultar" class="btn btn-consultar">CONSULTAR</button>
            <button type="submit" name="accion" value="editar" class="btn btn-editar">EDITAR</button>
            <button type="submit" name="accion" value="eliminar" class="btn btn-eliminar">ELIMINAR</button>
            
            <!-- Botón Salir configurado como enlace para cerrar sesión -->
            <a href="logout.php" class="btn btn-salir">SALIR</a>
        </div>
    </form>
</div>

</body>
</html>