# Informe Técnico Detallado y Exhaustivo del Código (EPSIC)

Este documento proporciona una descripción profunda y detallada de cada archivo, clase y método dentro del sistema **EPSIC**. Su objetivo es servir como manual técnico para desarrolladores, explicando no solo el "qué" sino el "cómo" y el "porqué" de la implementación.

---

## 1. Núcleo del Sistema (App Core)

### [app/config.php](file:///opt/lampp/htdocs/reciclaje/app/config.php)
Este es el archivo de configuración centralizado.
- **DB_SERVER, DB_NAME, DB_USER, DB_PASS**: Definen las credenciales de acceso a la base de datos MySQL. Se utilizan exclusivamente en `mainModel.php` para establecer conexiones PDO.
- **APP_URL**: Define la raíz de la aplicación (ej. `http://localhost/reciclaje/`). Se usa para construir rutas absolutas en enlaces, imágenes y redirecciones, evitando errores de rutas relativas al navegar por subcarpetas.

### [autoload.php](file:///opt/lampp/htdocs/reciclaje/autoload.php)
Implementa el estándar de carga automática de clases.
- **spl_autoload_register**: Registra una función anónima que se activa cada vez que se intenta instanciar una clase no cargada.
- **Lógica**: Convierte los namespaces (ej. `app\controllers\adminController`) en rutas de archivos válidas (`app/controllers/adminController.php`) reemplazando las barras invertidas por barras normales y verificando la existencia del archivo antes de incluirlo.

### [app/helpers.php](file:///opt/lampp/htdocs/reciclaje/app/helpers.php)
Conjunto de funciones globales de utilidad.
- **check_dashboard_access($allowed_roles)**: Realiza una validación de seguridad a nivel de vista. Verifica si el rol del usuario en sesión está dentro del array `$allowed_roles`. Si falla, redirige al login con un mensaje de error.
- **render_dashboard_alerts()**: Automatiza la visualización de notificaciones SweetAlert2 o alertas HTML basadas en las variables `$mensaje_exito` y `$mensaje_error`.
- **render_dashboard_stats($stats)**: Función de renderizado para las cajas de estadísticas superiores en los dashboards, permitiendo consistencia visual en todos los roles.

---

## 2. Modelos (Capa de Datos)

### [app/models/mainModel.php](file:///opt/lampp/htdocs/reciclaje/app/models/mainModel.php)
La clase padre de todos los modelos y controladores. Proporciona las herramientas base de manipulación de datos.
- **conectar()**: Método protegido que utiliza el patrón **Singleton** para mantener una única conexión PDO durante toda la ejecución de la petición. Configura el modo de error en `ERRMODE_EXCEPTION` para capturar fallos de SQL.
- **ejecutarConsulta($sql, $params)**: Prepara y ejecuta cualquier sentencia SQL de forma segura usando marcadores de posición (`?`), previniendo ataques de Inyección SQL.
- **limpiarCadena($cadena)**: Aplica una limpieza rigurosa a las entradas del usuario (trim, stripslashes, htmlspecialchars) para evitar XSS y caracteres basura.
- **guardarDatos / actualizarDatos / eliminarRegistro**: Proporcionan una interfaz CRUD genérica. Construyen las sentencias SQL dinámicamente basándose en arrays asociativos de campos y valores.

### [app/models/viewsModel.php](file:///opt/lampp/htdocs/reciclaje/app/models/viewsModel.php)
Responsable de la seguridad de navegación (Ruteo de Vistas).
- **$listaBlanca**: Un array asociativo multidimensional que define qué páginas (archivos .php) tiene permitido ver cada rol.
- **obtenerVista($page, $folder)**: Valida si la página solicitada está en la lista blanca del rol. Si es válida y el archivo físico existe, devuelve la ruta; de lo contrario, devuelve el dashboard por defecto del rol.
- **obtenerCarpetaRol($rol_id)**: Traduce el ID numérico del rol (1=Admin, 2=Gestor, etc.) al nombre de la subcarpeta física en `/views/`.

---

## 3. Controladores (Capa de Lógica)

### [app/controllers/viewsController.php](file:///opt/lampp/htdocs/reciclaje/app/controllers/viewsController.php)
El orquestador principal de las peticiones en el área privada.
- **preparar()**: 
  - Obtiene el `user_id` de la identidad activa (soporte multi-pestaña).
  - Gestiona el ruteo de peticiones **AJAX** (ej. `ajax_get_calles`).
  - Llama a `procesarPost()` si detecta una petición POST.
  - Ejecuta verificaciones especiales como `verificarDeudasBarrio()` para asegurar que los datos financieros estén al día antes de cargar la vista.
- **procesarPost($folder)**: Instancia dinámicamente el controlador de rol correspondiente (ej. `adminController`) y ejecuta su método `procesarAcciones()`.

### [app/controllers/adminController.php](file:///opt/lampp/htdocs/reciclaje/app/controllers/adminController.php)
Maneja las acciones administrativas de alto nivel.
- **add_user / edit_user**: Utiliza **Transacciones SQL** (`beginTransaction`, `commit`, `rollBack`) para asegurar que un usuario se cree junto con sus detalles específicos (DNI, Teléfono) de forma atómica.
- **insertarDetallesRol()**: Función privada que decide en qué tabla de detalles (`detalles_gestor`, `detalles_encargado_calle`, etc.) insertar la información adicional según el rol seleccionado.

### [app/controllers/barrioController.php](file:///opt/lampp/htdocs/reciclaje/app/controllers/barrioController.php)
Lógica financiera y operativa del Encargado de Barrio.
- **procesar_solicitud**: Aprueba o rechaza cambios en las viviendas. Si aprueba un 'Alta', inserta la vivienda; si es 'Baja', anula el servicio.
- **enviar_recaudacion_gestor**: Agrega todas las recaudaciones recibidas de sus calles y las envía en un solo bloque al Gestor de Pagos.
- **verificarDeudasBarrio()**: Un motor de reglas de negocio que recorre las viviendas, suspende servicios con deuda de 1 mes y genera multas automáticas a partir de los 2 meses de mora.

### [app/controllers/calleController.php](file:///opt/lampp/htdocs/reciclaje/app/controllers/calleController.php)
Lógica de campo del Encargado de Calle.
- **solicitar_alta / solicitar_baja / solicitar_renovacion**: En lugar de modificar la base de datos directamente, crea registros en `solicitudes_vivienda` para auditoría previa del Encargado de Barrio.
- **enviar_recaudacion_barrio**: Marca los cobros individuales como "enviados" y genera un registro de recaudación dirigido a su superior.

---

## 4. Archivos de Entrada y Flujo (Entry Points)

### [index.php](file:///opt/lampp/htdocs/reciclaje/index.php)
Punto de entrada público. Realiza una redirección simple al inicio público (`views/public/inicio.php`).

### [router.php](file:///opt/lampp/htdocs/reciclaje/router.php)
El núcleo del ruteo privado.
1. Inicia sesión y verifica autenticación.
2. Gestiona el **SID (Session ID)**: Permite que cada pestaña del navegador mantenga un contexto de usuario independiente, almacenando identidades en `$_SESSION['identities'][$sid]`.
3. Inyecta los resultados de `viewsController::preparar()` en el scope global para que las vistas tengan acceso a `$pdo`, `$user`, `$page`, etc.

---

## 5. Descripción de Vistas (Capa de Presentación)

### Vistas Públicas ([/views/public/](file:///opt/lampp/htdocs/reciclaje/views/public/))
- **login.php**: Formulario de acceso que envía datos a `loginController`.
- **inicio.php**: Landing page informativa para ciudadanos.
- **reportes.php**: Formulario público para que vecinos informen sobre acumulación de basura (usa geolocalización o descripción).

### Vistas de Administración ([/views/admin/](file:///opt/lampp/htdocs/reciclaje/views/admin/))
- **usuarios.php**: Tabla interactiva con búsqueda y filtros para gestionar todo el personal.
- **barrios.php / calles.php**: Gestión de la estructura geográfica del sistema.
- **monitor_pagos.php**: Vista global de la salud financiera del proyecto.

### Vistas de Barrio ([/views/barrio/](file:///opt/lampp/htdocs/reciclaje/views/barrio/))
- **solicitudes.php**: Bandeja de entrada de peticiones enviadas por los encargados de calle.
- **configuracion.php**: Panel para que el encargado defina los montos de cuota y multas de su zona.

### Vistas de Calle ([/views/calle/](file:///opt/lampp/htdocs/reciclaje/views/calle/))
- **viviendas.php**: Lista de vecinos bajo su responsabilidad.
- **reportar_pago.php**: Interfaz para registrar los cobros realizados casa por casa.

### Layouts y Componentes ([/views/layouts/](file:///opt/lampp/htdocs/reciclaje/views/layouts/), [/views/components/](file:///opt/lampp/htdocs/reciclaje/views/components/))
- **dashboard_layout.php**: Estructura maestra de las vistas privadas. Incluye el `<head>`, los scripts comunes y el sidebar.
- **sidebar.php**: Genera el menú lateral de forma dinámica según el rol del usuario logueado.
- **navbar.php**: Barra superior con el nombre del usuario y selector de identidades (soporte multi-pestaña).

---

## 7. Desglose Exhaustivo de Vistas por Carpeta

### [/views/admin/](file:///opt/lampp/htdocs/reciclaje/views/admin/)
- **barrios.php**: Lista maestra de barrios con opciones para ver calles asociadas y configurar tarifas.
- **barrio_nuevo.php**: Formulario simple para el registro de nuevas zonas geográficas.
- **calles.php**: Listado de todas las calles del sistema, filtrable por barrio.
- **calle_nueva.php**: Formulario para vincular una nueva calle a un barrio existente.
- **usuarios.php**: El centro de control de personal. Muestra nombre, email, rol y acciones (Ver, Editar).
- **usuario_nuevo.php**: Formulario dinámico que cambia los campos extra (DNI, Area, Barrio) mediante JavaScript según el rol seleccionado.
- **usuario_editar.php**: Carga los datos actuales del usuario y permite su modificación, incluyendo el cambio de contraseña opcional.
- **monitor_pagos.php**: Dashboard financiero que muestra el total recaudado por mes y el estado de las deudas globales.
- **viviendas.php**: Padrón general de contribuyentes con filtros por calle y barrio.

### [/views/barrio/](file:///opt/lampp/htdocs/reciclaje/views/barrio/)
- **dashboard.php**: Muestra métricas clave: Solicitudes pendientes, Calles activas y Total recaudado en el mes.
- **calles.php**: Vista específica para que el encargado vea el progreso de recaudación de sus subordinados.
- **solicitudes.php**: Listado de peticiones de Alta/Baja/Renovación. Incluye botones para aprobar o rechazar directamente.
- **configuracion.php**: Permite definir la `cuota_mensual` y la `multa_renovacion` que se aplicará a todas las viviendas del barrio.
- **reportar_pago.php**: Interfaz para que el encargado de barrio registre pagos que le lleguen directamente o valide lotes de calles.

### [/views/calle/](file:///opt/lampp/htdocs/reciclaje/views/calle/)
- **viviendas.php**: Su herramienta principal de trabajo. Lista las casas, sus estados y permite iniciar solicitudes de baja o renovación.
- **registrar_vivienda.php**: Formulario para pedir el alta de una nueva vivienda al barrio.
- **reportar_pago.php**: Muestra la lista de vecinos con deudas y permite marcar cada mes como pagado individualmente.
- **solicitudes.php**: Historial de peticiones enviadas y su estado actual (Pendiente/Aprobado).

### [/views/gestor/](file:///opt/lampp/htdocs/reciclaje/views/gestor/)
- **recibos.php**: Listado de todas las recaudaciones enviadas por los barrios. Permite descargar el comprobante o verificar el monto.
- **historial.php**: Registro histórico de todos los movimientos financieros validados.
- **viviendas.php**: Acceso de solo lectura al padrón para consultas rápidas.

### [/views/public/](file:///opt/lampp/htdocs/reciclaje/views/public/)
- **login.php**: Interfaz de entrada con validación de lado del cliente y servidor.
- **reportes.php**: Permite a ciudadanos subir incidencias (acumulación de residuos) con coordenadas GPS simuladas o reales.
- **nosotros.php / servicios.php**: Páginas informativas sobre la misión de EcoCusco y el sistema EPSIC.

---

## 8. Diccionario de Datos de la Base de Datos

### Tabla `viviendas`
- **id**: Clave primaria.
- **propietario**: Nombre del titular del servicio.
- **barrio_id / calle_id**: Relaciones geográficas.
- **estado_servicio**: `Activo` (paga normal), `Suspendido` (mora), `Anulado` (baja).

### Tabla `cobros`
- **monto**: El valor configurado por el barrio al momento de generar el cobro.
- **tipo_cobro**: `Servicio` (mensualidad) o `Multa` (por mora o renovación).
- **estado**: `Pendiente`, `Pagado`, `Vencido`.

### Tabla `recaudaciones`
- **tipo**: `Calle` (dinero del vecino al encargado) o `Barrio` (dinero consolidado al gestor).
- **emisor_id / receptor_id**: Usuarios que participan en el traspaso de fondos.

### Tabla `solicitudes_vivienda`
- **monto_deuda / detalles_deuda**: Captura instantánea de la deuda al momento de pedir la baja, para que el barrio sepa cuánto cobrar antes de aprobar.
- **estado**: `Pendiente`, `Aprobado`, `Rechazado`.



