# Informe Detallado de Funciones y Lógica del Proyecto

Este documento explica cada función, método, bucle, condicional, array y estructura relevante de los archivos principales del sistema, indicando para qué sirve cada parte y cómo se relacionan entre sí.

---

## app/config.php

- **Constantes:**
  - `DB_SERVER`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_URL`: Definen los parámetros de conexión a la base de datos y la URL base de la aplicación. Son usadas por los modelos para conectarse a la base de datos desde cualquier parte del sistema.

---

## app/helpers.php

- **check_dashboard_access($allowed_roles = [1])**
  - Verifica si el usuario tiene acceso al dashboard según su rol. Si no está autenticado o no tiene permisos, redirige al login o muestra error. Utiliza la variable global `$pdo` para consultar la base de datos.
  - Se usa en las vistas privadas para restringir el acceso a los paneles.

- **render_dashboard_alerts($exito, $error)**
  - Si existen mensajes de éxito o error, incluye el componente visual de alertas. Permite mostrar notificaciones en los dashboards.

- **render_dashboard_stats($stats = [])**
  - Incluye el componente visual de estadísticas, usando el array `$stats`. Permite mostrar métricas en los dashboards.

---

## autoload.php

- **spl_autoload_register(function ($clase))**
  - Permite la carga automática de clases. Convierte el namespace en una ruta de archivo y lo incluye si existe. Así, los controladores y modelos pueden ser instanciados en cualquier parte del sistema sin hacer `require` manual.

---

## router.php

- **session_start()**: Inicia la sesión PHP.
- **Condicional de autenticación**: Si no existe `$_SESSION['user_id']`, redirige al login.
- **Manejo de multi-sesión**: Permite que cada pestaña tenga una identidad de usuario diferente. Sincroniza el usuario activo en la sesión global.
- **Controlador de vistas**: Instancia `viewsController` y ejecuta su método `preparar()`, que determina qué vista cargar y procesa la lógica de la petición.
- **Variables globales**: Inyecta variables como `$pdo`, `$page`, `$mensaje_exito`, `$mensaje_error` para ser usadas en las vistas.

---

## app/controllers/viewsController.php

- **preparar()**
  - Obtiene el usuario activo desde la sesión. Si no existe, destruye la sesión y redirige al login.
  - Determina la carpeta de vistas según el rol del usuario.
  - Crea la conexión PDO a la base de datos.
  - Obtiene la página solicitada (por GET, default 'dashboard').
  - Si la página es 'ajax_get_calles', ejecuta una consulta AJAX y retorna JSON (usado para cargar calles dinámicamente en formularios).
  - Llama a `procesarPost($folder)` para manejar formularios POST según el rol.
  - Obtiene la ruta de la vista validada y devuelve los datos necesarios para el renderizado.

- **procesarPost($folder)**
  - Si la petición es POST, instancia el controlador correspondiente al rol y ejecuta su método `procesarAcciones()`.
  - Así, cada rol puede tener lógica propia para procesar formularios.

---

## app/controllers/gestorController.php

- **procesarAcciones()**
  - Si la acción es 'verificar_recaudacion', actualiza el estado de una recaudación a 'Verificado'.
  - Muestra mensajes de éxito o error según el resultado.
  - Se usa cuando el gestor valida pagos enviados por barrios.

---

## app/controllers/loginController.php

- **iniciarSesion()**
  - Recibe email y contraseña por POST, valida los datos y consulta la base de datos.
  - Si el usuario existe y la contraseña es correcta, crea la identidad en la sesión y redirige al router.
  - Si falla, retorna mensaje de error.

- **cerrarSesion()**
  - Elimina la identidad actual de la sesión y redirige al login.

---

## app/controllers/recolectorController.php

- **procesarAcciones()**
  - Preparado para futuras acciones del recolector. Actualmente no ejecuta ninguna acción.

---

## app/controllers/barrioController.php

- **procesarAcciones()**
  - Si la acción es 'procesar_solicitud', aprueba o rechaza solicitudes de alta/baja de vivienda. Si es alta, registra la vivienda; si es baja, elimina la vivienda. Actualiza el estado de la solicitud.
  - Si la acción es 'nuevo_vecino', inserta una nueva vivienda. Si proviene de una solicitud, la marca como aprobada.
  - Si la acción es 'enviar_recaudacion_gestor', suma los pagos pendientes de calles y, si hay pagos, crea una recaudación de barrio y actualiza los estados.

---

## app/controllers/calleController.php

- **procesarAcciones()**
  - Si la acción es 'solicitar_alta', verifica que el encargado tenga una calle asignada e inserta una solicitud de alta de vivienda.
  - Si la acción es 'solicitar_baja', verifica que el encargado tenga una calle asignada e inserta una solicitud de baja de vivienda.
  - Si la acción es 'procesar_pago', marca un cobro como pagado.
  - Si la acción es 'enviar_recaudacion_barrio', suma los pagos de la calle y, si hay pagos, crea una recaudación y actualiza los cobros.

---

## app/controllers/adminController.php

- **procesarAcciones()**
  - Si la acción es 'add_user', inserta un nuevo usuario y sus detalles según el rol.
  - Si la acción es 'edit_user', actualiza los datos de un usuario existente y sus detalles.
  - Si la acción es 'nueva_calle', inserta una nueva calle.
  - Si la acción es 'nuevo_barrio', inserta un nuevo barrio.
  - Si la acción es 'nuevo_vecino_admin', inserta una nueva vivienda, asignando encargado si corresponde.

- **insertarDetallesRol($pdo, $user_id, $rol_id)**
  - Inserta los detalles adicionales en la tabla correspondiente según el rol del usuario (barrio, calle, gestor, personal obrero).

---

# ¿Cómo se unen las funcionalidades?

- **autoload.php** permite que cualquier clase (modelo/controlador) se cargue automáticamente en cualquier archivo.
- **router.php** es el punto de entrada para usuarios logueados: verifica la sesión, determina el rol y la página, y delega la lógica a `viewsController`.
- **viewsController** decide qué controlador de rol debe procesar la petición POST y qué vista cargar.
- **Cada controlador** (admin, barrio, calle, gestor, recolector) implementa la función `procesarAcciones()` para manejar la lógica específica de su rol.
- **helpers.php** provee funciones globales para validar acceso y renderizar componentes visuales.
- **Las vistas** usan las variables y datos preparados por los controladores para mostrar la información y formularios al usuario.

Así, el sistema está modularizado: cada rol tiene su lógica, las vistas son dinámicas según el usuario, y la seguridad se controla en cada paso.
