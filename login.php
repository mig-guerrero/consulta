<?php
session_start();
// Si ya está logueado, lo mandamos directo al formulario
if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
    header("Location: trabajo.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    $datos = ['usuario' => $usuario, 'password' => $password];
    
    // Preparamos la petición a Python
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($datos),
            'timeout' => 5 // Espera máximo 5 segundos
        ],
    ];
    
    $context  = stream_context_create($options);
    
    // El @ evita que salga un error feo si Python está apagado
    $resultado = @file_get_contents('http://127.0.0.1:5000/login', false, $context);

    if ($resultado === false) {
        $error = "Error: No se pudo conectar con el servidor Python. ¿Está encendido?";
    } else {
        $respuesta = json_decode($resultado, true);
        if ($respuesta && $respuesta['exito']) {
            // Credenciales correctas, creamos la sesión
            $_SESSION['logueado'] = true;
            $_SESSION['usuario'] = $usuario;
            header("Location: trabajo.php");
            exit;
        } else {
            $error = $respuesta['mensaje'] ?? "Usuario o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #e0f7fa 0%, #fce4ec 100%); 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            margin: 0; 
        }
        .login-box { 
            width: 100%; 
            max-width: 400px; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05); 
            text-align: center; 
        }
        h2 { 
            color: #2d3a4b; 
            margin-bottom: 25px; 
            font-weight: 700; 
        }
        .input-group { 
            margin-bottom: 20px; 
            text-align: left; 
        }
        .input-group label { 
            display: block; 
            font-weight: 500; 
            font-size: 14px; 
            color: #555; 
            margin-bottom: 8px; 
        }
        .input-group input { 
            width: 100%; 
            box-sizing: border-box; 
            background-color: #f8f9fa; 
            border: 2px solid #e9ecef; 
            border-radius: 12px; 
            padding: 12px 15px; 
            font-size: 15px; 
            font-family: 'Poppins', sans-serif; 
            transition: 0.3s; 
        }
        .input-group input:focus { 
            outline: none; 
            border-color: #56cfe1; 
            background-color: #fff; 
            box-shadow: 0 0 0 4px rgba(86, 207, 225, 0.1); 
        }
        .btn { 
            width: 100%; 
            padding: 14px; 
            background-color: #56cfe1; 
            color: white; 
            font-weight: 600; 
            font-size: 16px; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            transition: 0.2s; 
            font-family: 'Poppins', sans-serif; 
            margin-top: 10px;
        }
        .btn:hover { 
            background-color: #4abccf; 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
        }
        .alert-error { 
            background: #fadbd8; 
            color: #78281f; 
            padding: 12px; 
            margin-bottom: 20px; 
            border-radius: 12px; 
            font-size: 14px; 
            border: 1px solid #f5b7b1; 
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Iniciar Sesión</h2>
    
    <?php if($error): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <label>Usuario</label>
            <input type="text" name="usuario" required placeholder="Ingresa tu usuario">
        </div>
        <div class="input-group">
            <label>Contraseña</label>
            <input type="password" name="password" required placeholder="Ingresa tu contraseña">
        </div>
        <button type="submit" class="btn">ENTRAR</button>
    </form>
</div>

</body>
</html>
