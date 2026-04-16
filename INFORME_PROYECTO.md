# Informe general del proyecto EcoCusco

## Resumen general
Este proyecto es un **Sistema de Gestión de Reciclaje** llamado "EPSIC", orientado a la administración de la recolección de residuos en barrios y zonas residenciales/comerciales. Permite gestionar usuarios, barrios, calles, viviendas, recaudaciones y roles, con acceso diferenciado según el tipo de usuario (admin, gestor, personal, barrio, calle, recolector).

---

## Archivos principales y su función

- **README.md**  
  Breve descripción: "Sistema-de-Gestion-de-Reciclaje".

- **autoload.php**  
  Carga automática de clases PHP usando namespaces. Convierte rutas tipo `app\controllers\jefeController` en rutas de archivo reales y las incluye automáticamente.

- **router.php**  
  Es el router principal para el área privada (usuarios logueados).  
  - Inicia sesión, verifica si el usuario está autenticado, maneja multi-sesión por pestaña, sincroniza la identidad activa y llama al controlador de vistas (`viewsController`) para preparar los datos y determinar qué página mostrar.
  - Si no hay usuario, redirige al login.

- **index.php**  
  Redirige siempre a la página pública de inicio (`/reciclaje/views/public/inicio.php`).

- **app/config.php**  
  Configuración de la base de datos (host, nombre, usuario, contraseña) y URL base de la app.

- **app/helpers.php**  
  Funciones auxiliares:
  - `check_dashboard_access`: Verifica si el usuario tiene acceso a un panel según su rol.
  - `render_dashboard_alerts`: Renderiza mensajes de éxito/error.
  - `render_dashboard_stats`: Renderiza estadísticas en el dashboard.

---

## Modelos

- **app/models/mainModel.php**  
  Modelo base del sistema.  
  - Provee la conexión PDO a la base de datos y métodos CRUD genéricos.
  - Todas las clases modelo heredan de aquí.
  - Métodos para ejecutar consultas y obtener usuarios.

- **app/models/viewsModel.php**  
  Controla qué páginas puede ver cada rol.  
  - Define una "lista blanca" de páginas permitidas por rol.
  - Si se quiere agregar una nueva página, se añade aquí y se crea el archivo correspondiente en `views/{rol}/`.

---

## Controladores

- **app/controllers/viewsController.php**  
  Controlador principal de vistas privadas.
  - Obtiene datos del usuario activo.
  - Determina la carpeta de vistas según el rol.
  - Maneja peticiones AJAX (por ejemplo, obtener calles de un barrio).
  - Procesa formularios POST según el rol.

- **app/controllers/gestorController.php**  
  Controlador para acciones del rol "gestor".
  - Ejemplo: Verificar recaudaciones enviadas por encargados de barrio.

---

## Otros archivos y carpetas relevantes

- **update_db_v2.sql / reciclaje_platform.sql**  
  Scripts SQL para crear o actualizar la base de datos.

- **/views/**  
  Contiene todas las vistas del sistema, organizadas por rol y propósito (admin, gestor, barrio, calle, personal, público, componentes, layouts).

- **/assets/**  
  Recursos estáticos como CSS e imágenes.

- **/uploads/**  
  Carpeta para archivos subidos (por ejemplo, imágenes).

---

## ¿Cómo se estructura el sistema?

- **MVC básico**:  
  - Modelos en `/app/models/`
  - Controladores en `/app/controllers/`
  - Vistas en `/views/`
- **Roles**:  
  - Cada rol tiene su propio conjunto de vistas y permisos.
- **Componentes reutilizables**:  
  - Ejemplo: `dashboard_alerts.php`, `dashboard_stats.php`, `footer_public.php`, etc.

---

## ¿Cómo se procesa una petición privada?

1. El usuario accede a una URL privada → entra por `router.php`.
2. Se verifica la sesión y el rol.
3. Se determina la página a mostrar según el rol y la petición.
4. El controlador de vistas prepara los datos y carga la vista correspondiente.
