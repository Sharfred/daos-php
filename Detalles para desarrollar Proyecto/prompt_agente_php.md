# Prompt para el Agente (Versión PHP)

Copia y pega el siguiente texto en tu nueva conversación con el agente para iniciar la versión PHP del proyecto:

---

**Rol:** Eres un desarrollador Senior Full-Stack especializado en PHP 8+, MySQL (PDO) y despliegue en la nube (Railway).

**Contexto del Proyecto:**
Tengo una copia del proyecto web de "DAO'S GYM", un gimnasio local. Actualmente la interfaz (HTML/CSS/JS) está completa y lista, pero necesito que construyas un backend puro en **PHP** (sin Node.js) que se conecte a una base de datos MySQL, para cumplir estrictamente con los requerimientos de mi rúbrica escolar.

El proyecto cuenta con las siguientes tablas en MySQL:
1. `usuarios` (id, usuario, password_hash) -> Para el login de administradores.
2. `clientes` (id_cliente, nombre, apellido_p, apellido_m, edad, fecha_registro, estado)
3. `membresias` (id_membresia, tipo, duracion, costo, estado)
4. `inscripciones` (id_inscripcion, fecha_inicio, fecha_fin, id_cliente, id_membresia, estado)
5. `pagos` (id_pago, monto, fecha_pago, id_cliente, estado)
*Nota: El campo `estado` es ENUM('activo', 'inactivo') para manejar borrados lógicos (soft deletes).*

**Tus Tareas (Paso a Paso):**

1. **Configuración del Backend PHP (PDO):**
   - Crea un archivo de configuración de base de datos (`db.php` o similar) que utilice `PDO` para conectarse a MySQL. 
   - Debe leer las credenciales desde variables de entorno (`$_ENV` o `getenv()`) usando el formato de Railway (Ej: leyendo la variable `MYSQL_URL` o separadas `MYSQL_HOST`, `MYSQL_USER`, etc.).

2. **Endpoints (APIs) en PHP:**
   - Necesito que crees archivos PHP que actúen como API REST (que reciban y devuelvan JSON) para que mi archivo `app.js` (frontend) pueda consumirlos mediante `fetch()`.
   - **Rutas públicas:**
     - `/php/api/publico/registro.php`: Recibe POST para registrar un nuevo cliente desde la página principal.
   - **Rutas privadas (Protegidas por Sesión PHP):**
     - `/php/api/auth/login.php`: Autenticación de administradores. Debe iniciar una sesión segura (`session_start()`).
     - `/php/api/clientes.php`, `/php/api/membresias.php`, `/php/api/inscripciones.php`, `/php/api/pagos.php`: Archivos para realizar las operaciones CRUD (Crear, Leer, Actualizar, Borrado lógico).

3. **Adaptación del Frontend:**
   - Deberás indicarme qué líneas cambiar en mi `app.js` para que las rutas del `fetch()` apunten a los nuevos archivos `.php` en lugar de la antigua API de Node.js. (Ej: Cambiar `/api/clientes` por `/php/api/clientes.php`).

4. **Preparación para Railway (DevOps):**
   - El proyecto se va a subir a Railway. Dado que Railway no ejecuta PHP de forma nativa con solo subir archivos, deberás proveerme de los archivos de configuración necesarios. 
   - Genera un archivo `nixpacks.toml` o un `Dockerfile` (junto con un archivo `apache2.conf` si es necesario) para que Railway instale Apache y PHP 8, y levante el servidor correctamente cuando yo haga el `git push`.

**Restricciones y Reglas:**
- Todo el código PHP debe estar fuertemente protegido contra Inyecciones SQL usando **Sentencias Preparadas de PDO**.
- No utilices frameworks como Laravel o CodeIgniter. Debe ser PHP puro ("Vanilla PHP").
- Devuelve las respuestas siempre en formato JSON (`header('Content-Type: application/json')`) y maneja correctamente los códigos de estado HTTP (200, 400, 401, 500).

¡Por favor, analiza el requerimiento y dame un plan de implementación detallado para comenzar!
