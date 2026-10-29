from flask import Flask, request, jsonify
import sqlite3

app = Flask(__name__)
DB_FILE = "sistema_registro.db"

def inicializar_base_datos():
    conexion = sqlite3.connect(DB_FILE)
    cursor = conexion.cursor()
    
    # 1. Tabla de usuarios (formulario)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS usuarios (
            cedula TEXT PRIMARY KEY, nombre1 TEXT NOT NULL, nombre2 TEXT,
            apellido1 TEXT NOT NULL, apellido2 TEXT, telefono TEXT, direccion TEXT
        )
    """)
    
    # 2. Tabla de Administradores (Login)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS admin (
            usuario TEXT PRIMARY KEY,
            password TEXT NOT NULL
        )
    """)
    
    # 3. Insertar el usuario admin por defecto (admin / 1234)
    try:
        cursor.execute("INSERT INTO admin (usuario, password) VALUES ('admin', '1234')")
    except sqlite3.IntegrityError:
        pass # Si ya existe, no hace nada
        
    conexion.commit()
    conexion.close()

def obtener_conexion():
    conexion = sqlite3.connect(DB_FILE)
    conexion.row_factory = sqlite3.Row 
    return conexion

# --- RUTA PARA EL LOGIN ---
@app.route('/login', methods=['POST'])
def login():
    datos = request.json
    usuario = datos.get('usuario')
    password = datos.get('password')
    
    conexion = obtener_conexion()
    cursor = conexion.cursor()
    cursor.execute("SELECT * FROM admin WHERE usuario = ? AND password = ?", (usuario, password))
    resultado = cursor.fetchone()
    conexion.close()
    
    if resultado:
        return jsonify({"exito": True, "mensaje": "Bienvenido"})
    else:
        return jsonify({"exito": False, "mensaje": "Usuario o contraseña incorrectos"})

# --- RUTA PARA EL FORMULARIO (GUARDAR, CONSULTAR, ETC) ---
@app.route('/procesar', methods=['POST'])
def procesar():
    datos = request.json
    accion = datos.get('accion')
    cedula = datos.get('cedula')
    
    conexion = obtener_conexion()
    cursor = conexion.cursor()
    respuesta = {"mensaje": "Acción no reconocida", "datos": None}
    
    try:
        if accion == 'guardar':
            sql = """INSERT INTO usuarios (cedula, nombre1, nombre2, apellido1, apellido2, telefono, direccion) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)"""
            valores = (cedula, datos['nombre1'], datos['nombre2'], datos['apellido1'], datos['apellido2'], datos['telefono'], datos['direccion'])
            cursor.execute(sql, valores)
            conexion.commit()
            respuesta["mensaje"] = "Registro guardado exitosamente."
            
        elif accion == 'consultar':
            sql = "SELECT * FROM usuarios WHERE cedula = ?"
            cursor.execute(sql, (cedula,))
            resultado = cursor.fetchone()
            if resultado:
                respuesta["mensaje"] = "Usuario encontrado."
                respuesta["datos"] = dict(resultado) 
            else:
                respuesta["mensaje"] = "Usuario no registrado."
                
        elif accion == 'editar':
            sql = """UPDATE usuarios SET nombre1=?, nombre2=?, apellido1=?, apellido2=?, telefono=?, direccion=? 
                     WHERE cedula=?"""
            valores = (datos['nombre1'], datos['nombre2'], datos['apellido1'], datos['apellido2'], datos['telefono'], datos['direccion'], cedula)
            cursor.execute(sql, valores)
            conexion.commit()
            respuesta["mensaje"] = "Registro actualizado exitosamente."
            
        elif accion == 'eliminar':
            sql = "DELETE FROM usuarios WHERE cedula = ?"
            cursor.execute(sql, (cedula,))
            conexion.commit()
            respuesta["mensaje"] = "Registro eliminado exitosamente."
            
    except sqlite3.Error as err:
        respuesta["mensaje"] = f"Error en la base de datos: {err}"
    finally:
        cursor.close()
        conexion.close()
        
    return jsonify(respuesta)

if __name__ == '__main__':
    inicializar_base_datos() # Crea las tablas antes de arrancar
    app.run(port=5000, debug=True)