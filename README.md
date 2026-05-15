# # EPSIC - Sistema de Gestión de Reciclaje

**EPSIC** es una plataforma web desarrollada en PHP para la administración de servicios de recolección de residuos, gestión de pagos por vivienda y monitoreo de incidencias ciudadanas.

## 🚀 Características
- **Gestión por Roles:** Admin, Gestor, Barrio, Calle, Personal.
- **Sistema Multi-Sesión:** Soporte para múltiples identidades simultáneas mediante SIDs.
- **Flujo de Recaudación:** Validación de pagos en cascada (Calle -> Barrio -> Gestor).
- **Reportes Geolocalizados:** Los ciudadanos pueden reportar focos de basura con descripción y fotos.
- **Configuración por Barrio:** Personalización de cuotas y multas por zona.

## 🛠️ Tecnologías
- **Backend:** PHP 8.x (Arquitectura MVC personalizada)
- **Base de Datos:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Otros:** PDO para seguridad en consultas, Namespaces y Autoload.

## 📋 Requisitos
- Servidor web (XAMPP, LAMPP, WAMP o Nginx).
- PHP >= 7.4.
- MySQL >= 5.7.

## 🔧 Instalación
1. Clona este repositorio en tu carpeta `htdocs` o `www`.
2. Crea una base de datos llamada `reciclaje_platform`.
3. Importa el archivo `database.sql` en la base de datos creada.
4. Configura tus credenciales en `app/config.php`.
5. Accede a `http://localhost/reciclaje/`.

## 👤 Credenciales de Prueba
- **Admin:** `admin@gmail.com` / `admin`
- **Gestor:** `gestor@ecocusco.com` / `admin`

---
Desarrollado para la optimización de servicios municipales y comunitarios.

