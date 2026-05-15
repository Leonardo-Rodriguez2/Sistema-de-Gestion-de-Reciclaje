# Proyecto EPSIC: Sistema Integral de Gestión de Reciclaje y Recaudación

## 1. Introducción y Propósito
El proyecto **EPSIC** (Sistema de Gestión de Reciclaje EcoCusco) nace como una solución tecnológica para digitalizar y transparentar el proceso de recolección de residuos sólidos y el cobro de arbitrios municipales o comunitarios. El sistema aborda la problemática de la morosidad, la falta de comunicación entre niveles de gestión y la ineficiencia en el reporte de focos de contaminación.

---

## 2. Modelo de Gobernanza (Estructura de Roles)
El sistema implementa un modelo jerárquico de permisos para asegurar que cada usuario actúe solo dentro de su competencia:

### 2.1. Administrador (Nivel 1)
- **Alcance Global:** Tiene visibilidad sobre todos los barrios y calles.
- **Gestión de Personal:** Crea y edita cuentas para todos los roles, asignándoles sus respectivos barrios o calles.
- **Integridad del Sistema:** Define la estructura geográfica base del proyecto.

### 2.2. Gestor de Pagos (Nivel 2)
- **Control Financiero:** Su dashboard se enfoca en la validación final del dinero.
- **Cierre de Ciclo:** Recibe las recaudaciones consolidadas de los barrios y las marca como "Verificadas", cerrando el flujo contable.

### 2.3. Encargado de Barrio (Nivel 3)
- **Supervisión Zonal:** Actúa como un puente entre la calle y la administración central.
- **Auditor de Cambios:** Aprueba o rechaza las solicitudes de alta/baja de viviendas.
- **Configurador de Tarifas:** Define cuánto se cobra en su barrio por mes y cuánto es la multa por reconexión/renovación.

### 2.4. Encargado de Calle (Nivel 4)
- **Operador de Proximidad:** Es quien tiene contacto directo con los vecinos.
- **Cobrador:** Registra los pagos recibidos en efectivo o transferencia casa por casa.
- **Informante:** Reporta solicitudes de nuevos vecinos o retiros del servicio.

### 2.5. Personal Obrero / Recolector
- **Operativo de Campo:** Visualiza los reportes ciudadanos de basura acumulada.
- **Rutas de Atención:** Cambia el estado de los reportes a "En Proceso" o "Completado".

---

## 3. Arquitectura Técnica y Patrones de Diseño

### 3.1. Patrón MVC (Modelo-Vista-Controlador)
El sistema está construido sobre una arquitectura limpia sin dependencias externas pesadas, lo que garantiza velocidad y facilidad de mantenimiento.
- **Modelos:** Encapsulan la lógica de conexión a la base de datos y la seguridad de las vistas.
- **Vistas:** Utilizan PHP nativo mezclado con HTML5 para un renderizado rápido del lado del servidor.
- **Controladores:** Procesan las peticiones del usuario y deciden qué acciones tomar sobre los datos.

### 3.2. Sistema de Multi-Sesión por SID
Una innovación clave de EPSIC es su capacidad para manejar múltiples identidades en una misma sesión de navegador. Mediante el uso de un parámetro `sid` en la URL, el sistema diferencia contextos, permitiendo que un usuario pueda gestionar diferentes roles en pestañas paralelas sin conflictos de sesión.

---

## 4. El Ciclo de Vida del Dinero (Flujo Financiero)

1.  **Generación de Deuda:** El sistema crea registros en la tabla `cobros` automáticamente para cada vivienda.
2.  **Cobro en Calle:** El Encargado de Calle cobra al vecino y marca el registro como "Pagado".
3.  **Liquidación de Calle:** El encargado envía el total de su recaudación al Barrio. Los registros de pago se vinculan a un `recaudacion_id` tipo 'Calle'.
4.  **Consolidación de Barrio:** El Encargado de Barrio verifica los montos, los suma y envía una "Recaudación de Barrio" al Gestor.
5.  **Validación Final:** El Gestor de Pagos revisa la recaudación del barrio y la marca como "Verificado".

---

## 5. Gestión de Viviendas y Solicitudes
El padrón de contribuyentes no es estático. Para evitar fraudes o errores:
- Cualquier alta o baja de vivienda iniciada por un Encargado de Calle queda en estado **Pendiente**.
- El Encargado de Barrio debe revisar los detalles (monto de deuda en caso de baja, o ubicación en caso de alta) antes de aprobar la transacción.
- Una vez aprobada, el sistema actualiza automáticamente el estado de la vivienda (`Activo`, `Suspendido`, `Anulado`).

---

## 9. Stack Tecnológico y Herramientas

### 9.1. Backend: PHP 8.x
Se ha elegido PHP nativo por su excelente rendimiento en servidores compartidos y su facilidad de despliegue. El código utiliza:
- **Namespaces:** Para evitar colisiones de nombres de clases y mantener un estándar PSR-4 simulado.
- **PDO (PHP Data Objects):** Para una abstracción segura de la capa de datos.
- **BCrypt:** Para el cifrado unidireccional de contraseñas.

### 9.2. Frontend: Vanilla Stack
- **HTML5 Semántico:** Para mejorar la accesibilidad y el SEO del área pública.
- **CSS3 (Custom Properties):** Uso de variables CSS para mantener un sistema de diseño consistente (colores, espaciados).
- **JavaScript (ES6+):** Manipulación del DOM nativa para validaciones y peticiones AJAX mediante `fetch`.

### 9.3. Librerías de Terceros (CDN)
- **SweetAlert2:** Utilizada para mostrar mensajes de éxito, error y confirmaciones de borrado con una estética premium.
- **Google Fonts (Outfit / Inter):** Para una tipografía moderna y legible.
- **FontAwesome / Lucide Icons:** Para la iconografía del dashboard.

---

## 10. Escalabilidad y Mejoras Futuras

### 10.1. Módulo de Pagos Digitales
El sistema está preparado para integrar APIs de pago como **Culqi**, **MercadoPago** o pasarelas locales mediante el registro de `transaction_id` en la tabla de pagos.

### 10.2. App Móvil para Recolectores
Mediante la creación de un endpoint API en `viewsController`, se podría desarrollar una app híbrida (Ionic/Flutter) que permita a los recolectores ver su ruta en un mapa en tiempo real.

### 10.3. Notificaciones Automáticas
Implementación de un cronjob que envíe correos electrónicos o mensajes de WhatsApp automáticos a los vecinos cuando su cobro mensual sea emitido o esté próximo a vencer.

---

## 11. Conclusión Técnica
EPSIC no es solo un software de cobro, es un ecosistema de gestión basado en la confianza y la auditoría. Su diseño modular permite que crezca según las necesidades del municipio o asociación de vecinos, manteniendo siempre la integridad de los datos financieros y operativos.

---

## 6. Módulo Ciudadano y Reportes
Cualquier ciudadano, sin necesidad de loguearse, puede acceder a la sección de reportes públicos.
- **Captura de Datos:** Se solicita la ubicación, el tipo de residuo y opcionalmente fotos.
- **Monitoreo:** El Personal Obrero recibe estos reportes en su dashboard en tiempo real para su pronta atención.

---

## 7. Base de Datos y Relaciones
El sistema descansa sobre una base de datos MySQL normalizada para evitar redundancia:
- **Relaciones 1:N**: Barrio -> Calles, Calle -> Viviendas.
- **Relaciones N:M (simuladas)**: Usuarios y Roles mediante tablas de detalles específicos.
- **Integridad Referencial**: Uso de `ON DELETE CASCADE` y `SET NULL` para mantener la base de datos limpia si se eliminan usuarios o zonas.

---

## 8. Seguridad y Mejores Prácticas
- **Contraseñas Seguras:** Uso de `password_hash()` con el algoritmo DEFAULT (BCrypt).
- **Prevención SQL Injection:** Uso mandatorio de sentencias preparadas PDO.
- **Protección XSS:** Limpieza de todas las variables de entrada mediante `htmlspecialchars()`.
- **Navegación Restringida:** Listas blancas de archivos por rol en `viewsModel.php`.
