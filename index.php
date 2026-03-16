<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoCusco - Servicio de Recolección Corporativa y Residencial</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .hero-section {
      background: linear-gradient(rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9)), url('https://images.unsplash.com/photo-1595278069441-2cf29f8005a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
      padding: 100px 0;
      color: white;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }
    .hero-section h1 { font-weight: 700; font-size: 3rem; }
    .features-section { background-color: #f8f9fa; padding: 80px 0; }
    
    /* Cómo funciona */
    .steps-section { padding: 80px 0; background-color: white; }
    .step-circle {
      width: 80px; height: 80px;
      border-radius: 50%;
      background-color: #ECFDF5;
      color: #10B981;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem; font-weight: bold;
      margin: 0 auto 20px;
      border: 3px solid #10B981;
    }
    
    /* Por qué elegirnos */
    .why-choose-us { background-color: #064E3B; color: white; padding: 80px 0; }
    .why-icon { color: #34D399; font-size: 2.5rem; margin-bottom: 20px; }
    
    /* Testimonios */
    .testimonials-section { padding: 80px 0; background-color: #f8f9fa; }
    .testimonial-card {
      background: white; border-radius: 12px; padding: 30px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.05); border: none;
      position: relative;
    }
    .quote-icon { position: absolute; top: 20px; right: 20px; font-size: 3rem; color: #ECFDF5; z-index: 0; }
    .testimonial-content { position: relative; z-index: 1; }
    .client-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-right: 15px; }

    .stats-section { background-color: #10B981; color: white; padding: 60px 0; text-align: center; }
    .footer { background-color: #1F2937; color: white; padding: 60px 0; }
    .footer a { color: #9CA3AF; text-decoration: none; transition: 0.3s;}
    .footer a:hover { color: white; }
    .btn-custom { background-color: #059669; color: white; border: none; padding: 12px 30px; font-weight: 600; font-size: 1.1rem; border-radius: 8px;}
    .btn-custom:hover { background-color: #047857; color: white; }
    .card-feature { transition: transform 0.3s ease; border: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-radius: 12px;}
    .card-feature:hover { transform: translateY(-5px); }
  </style>
</head>
<body>
  <!-- HEADER INCLUIDO DESDE components/header.php -->
  <?php include 'components/header.php'; ?>

  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-7">
          <span class="badge bg-light text-success mb-3 px-3 py-2 fs-6 rounded-pill">Expertos en Recolección y Reciclaje</span>
          <h1>Líderes en Gestión Ambiental Integral en Cusco</h1>
          <p class="lead mb-4 mt-3">Proveemos soluciones integrales de recolección de residuos para zonas comerciales y residenciales. Deja que nos encarguemos de mantener tu barrio limpio, saludable y 100% sustentable con tecnología de punta.</p>
          <a href="servicios.php" class="btn btn-light text-success fw-bold px-4 py-3 me-3 rounded-pill shadow-sm">Ver Planes Disponibles</a>
          <a href="#contacto" class="btn btn-outline-light px-4 py-3 rounded-pill fw-bold">Contactar a un Asesor</a>
        </div>
      </div>
    </div>
  </section>

  <!-- SECCIÓN: Servicios Rápidos -->
  <section class="features-section">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold" style="color: #064E3B;">Nuestros Servicios Corporativos</h2>
        <p class="text-muted lead mx-auto" style="max-width: 700px;">Diseñados a la medida de los barrios modernos, empresas y municipalidades. Brindamos una solución "llave en mano" garantizando el recojo puntual sin complicaciones.</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 card-feature p-4 text-center">
            <div class="mb-4">
              <i class="fas fa-truck fa-3x" style="color: #10B981;"></i>
            </div>
            <h4 class="fw-bold mb-3">Recolección Residencial Diaria</h4>
            <p class="text-muted">Servicio tercerizado de limpieza y de transporte de residuos con flotas propias certificadas. Olvídate de la acumulación en las esquinas de tu urbanización.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 card-feature p-4 text-center">
            <div class="mb-4">
              <i class="fas fa-recycle fa-3x" style="color: #10B981;"></i>
            </div>
            <h4 class="fw-bold mb-3">Programa de Valorización</h4>
            <p class="text-muted">Desarrollo y gestión de campañas exclusivas de segregación en la fuente con alianzas vecinales para promover la economía circular.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 card-feature p-4 text-center">
            <div class="mb-4">
              <i class="fas fa-file-invoice-dollar fa-3x" style="color: #10B981;"></i>
            </div>
            <h4 class="fw-bold mb-3">Cobranza y Tableros Digitales</h4>
            <p class="text-muted">Plataforma transparente con emisión automatizada de reportes, historiales de pago en línea y estado de deudas por vivienda afiliada.</p>
          </div>
        </div>
      </div>
      <div class="text-center mt-5">
          <a href="servicios.php" class="btn btn-custom px-5 py-3 rounded-pill shadow">Descubre las diferencias entre Planes</a>
      </div>
    </div>
  </section>

  <!-- SECCIÓN NUEVA: Cómo Funciona -->
  <section class="steps-section">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold" style="color: #064E3B;">¿Cómo empezar a trabajar con nosotros?</h2>
        <p class="text-muted lead">Un proceso diseñado para ser rápido, transparente y 100% digitalizado.</p>
      </div>
      <div class="row text-center g-4 position-relative">
        <!-- Línea conectora de fondo (solo CSS visual) -->
        <div class="d-none d-lg-block" style="position:absolute; top:40px; left:16%; right:16%; height:3px; background:#E5E7EB; z-index:0;"></div>
        
        <div class="col-lg-3 col-md-6 position-relative z-1">
          <div class="step-circle shadow-sm">1</div>
          <h5 class="fw-bold mt-4">Contacto y Auditoría</h5>
          <p class="text-muted small px-2">Agenda una visita con nosotros. Un especialista revisará tu zona, la cantidad de viviendas y el volumen de basura generado para darte la tarifa exacta.</p>
        </div>
        
        <div class="col-lg-3 col-md-6 position-relative z-1">
          <div class="step-circle shadow-sm">2</div>
          <h5 class="fw-bold mt-4">Contrato y Credenciales</h5>
          <p class="text-muted small px-2">Firmamos el acuerdo (Plan Vecinal o Comercial). Entregamos las credenciales de plataforma, horarios fijos garantizados y contenedores.</p>
        </div>
        
        <div class="col-lg-3 col-md-6 position-relative z-1">
          <div class="step-circle shadow-sm">3</div>
          <h5 class="fw-bold mt-4">Ejecución Constante</h5>
          <p class="text-muted small px-2">Nuestra maquinaria pasa silenciosa y puntualmente. Tus vecinos bajan solo las bolsas correspondientes minimizando olores en la calle.</p>
        </div>
        
        <div class="col-lg-3 col-md-6 position-relative z-1">
          <div class="step-circle shadow-sm">4</div>
          <h5 class="fw-bold mt-4">Plataforma Online</h5>
          <p class="text-muted small px-2">Los residentes entran al Área de Clientes mes a mes para marcar sus deudas saldadas con nuestro Gestor y ver sus reportes.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- SECCIÓN NUEVA: Por qué Elegirnos -->
  <section class="why-choose-us">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-5 mb-5 mb-lg-0">
          <h2 class="fw-bold mb-4 display-5">Más que camiones de basura, somos promotores de calidad de vida.</h2>
          <p class="lead mb-4 opacity-75">Nuestra infraestructura logística y digital nos pone a la vanguardia. Garantizamos un servicio impecable que añade valor a tu barrio y a la ciudad, alejando las plagas y la depreciación predial que trae la basura acumulada.</p>
          <ul class="list-unstyled">
             <li class="mb-3 fs-5"><i class="fas fa-check-circle text-success me-3"></i> Trazabilidad GPS de camiones en ruta.</li>
             <li class="mb-3 fs-5"><i class="fas fa-check-circle text-success me-3"></i> Personal unificado, uniformado y asegurado.</li>
             <li class="mb-3 fs-5"><i class="fas fa-check-circle text-success me-3"></i> Atención al cliente 24/7 para emergencias.</li>
             <li class="fs-5"><i class="fas fa-check-circle text-success me-3"></i> Promotores activos de certificación verde comercial.</li>
          </ul>
        </div>
        <div class="col-lg-6 offset-lg-1">
          <div class="row g-4 text-center">
            <div class="col-6">
              <div class="p-4 bg-white bg-opacity-10 rounded-4">
                <i class="fas fa-shield-halved why-icon"></i>
                <h4 class="fw-bold">100% Legal</h4>
                <p class="small opacity-75 mb-0">Disposición final solo en rellenos sanitarios certificados por MINAM.</p>
              </div>
            </div>
            <div class="col-6">
              <div class="p-4 bg-white bg-opacity-10 rounded-4">
                <i class="fas fa-hand-holding-dollar why-icon"></i>
                <h4 class="fw-bold">Costos Claros</h4>
                <p class="small opacity-75 mb-0">Sin sorpresas. Tarifas fijas estandarizadas facturadas vía web.</p>
              </div>
            </div>
            <div class="col-6">
              <div class="p-4 bg-white bg-opacity-10 rounded-4">
                <i class="fas fa-seedling why-icon"></i>
                <h4 class="fw-bold">Baja Emisión</h4>
                <p class="small opacity-75 mb-0">Vehículos modernos con estándar Euro V limitados en ruido.</p>
              </div>
            </div>
            <div class="col-6">
              <div class="p-4 bg-white bg-opacity-10 rounded-4">
                <i class="fas fa-laptop-code why-icon"></i>
                <h4 class="fw-bold">Alta Tecnología</h4>
                <p class="small opacity-75 mb-0">Sistema Web para gestionar reportes cívicos al instante.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SECCIÓN NUEVA: Testimonios -->
  <section class="testimonials-section">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold" style="color: #064E3B;">Lo que dicen nuestros asociados</h2>
        <p class="text-muted lead">Juntas de vecinos y negocios que lograron un cambio real con nosotros.</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="testimonial-card">
            <i class="fas fa-quote-right quote-icon"></i>
            <div class="testimonial-content">
              <p class="text-muted fst-italic mb-4">"Antes teníamos problemas serios los fines de semana. Desde que la urbanización contrató el servicio corporativo diario con EcoCusco, el parque central vuelve a oler a flores y las disputas entre vecinos se terminaron. La plataforma de pagos nos ordenó las cuentas."</p>
              <div class="d-flex align-items-center">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-1.2.1&auto=format&fit=crop&w=150&q=80" alt="Cliente" class="client-img">
                <div>
                  <h6 class="fw-bold mb-0">Dr. Roberto Fernández</h6>
                  <span class="text-muted" style="font-size: 0.85rem;">Pdte. Junta Vecinal, Urb. Ttio</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="testimonial-card">
            <i class="fas fa-quote-right quote-icon"></i>
            <div class="testimonial-content">
              <p class="text-muted fst-italic mb-4">"Como cadena de restaurantes generamos mucho contenido orgánico que traía moscas en minutos. El plan Comercial de Recojo Nocturno fue un alivio. Dejamos los contenedores cerrados y a las 2 AM su personal retira todo sin interrumpir a nuestros comensales. Excelente."</p>
              <div class="d-flex align-items-center">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=150&q=80" alt="Cliente" class="client-img">
                <div>
                  <h6 class="fw-bold mb-0">María Luisa Cáceres</h6>
                  <span class="text-muted" style="font-size: 0.85rem;">Gerente General, RestoGroup</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="testimonial-card">
            <i class="fas fa-quote-right quote-icon"></i>
            <div class="testimonial-content">
              <p class="text-muted fst-italic mb-4">"Hace poco utilicé el botón de 'Reportar Incidente' de manera anónima porque alguien había dejado escombros de construcción tapando la vereda en mi cuadra. El equipo de Reacción Rápida lo limpió la misma tarde. Me alegra que la tecnología por fin sirva para el civismo."</p>
              <div class="d-flex align-items-center">
                <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-1.2.1&auto=format&fit=crop&w=150&q=80" alt="Cliente" class="client-img">
                <div>
                  <h6 class="fw-bold mb-0">Sofía Ramos</h6>
                  <span class="text-muted" style="font-size: 0.85rem;">Vecina del Distrito San Blas</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA de Enlace / Solicitud -->
  <section class="py-5" style="background-color: #ECFDF5;">
    <div class="container text-center py-5">
      <h2 class="fw-bold mb-4" style="color: #064E3B;">¿Listo para darle la bienvenida al progreso y la limpieza?</h2>
      <p class="lead text-muted mx-auto mb-5" style="max-width: 700px;">
        Nuestros asesores completan decenas de visitas semanales. Separa la tuya hoy mismo, descubre nuestras ventajas comparativas y protege el medio ambiente al confiar en verdaderos expertos logísticos.
      </p>
      <a href="nosotros.php" class="btn btn-custom btn-lg shadow-sm px-5 py-3 rounded-pill">Llenar Solicitud de Contratación <i class="fas fa-file-signature ms-2"></i></a>
    </div>
  </section>

  <section class="stats-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-3">
          <h2 class="display-4 fw-bold">12</h2>
          <p class="fs-5 text-dark fw-bold">Años de Verdad</p>
        </div>
        <div class="col-md-3">
          <h2 class="display-4 fw-bold">15K+</h2>
          <p class="fs-5 text-dark fw-bold">Familias Afiliadas</p>
        </div>
        <div class="col-md-3">
          <h2 class="display-4 fw-bold">2.4M</h2>
          <p class="fs-5 text-dark fw-bold">Tn. Procesadas al Año</p>
        </div>
        <div class="col-md-3">
          <h2 class="display-4 fw-bold">25</h2>
          <p class="fs-5 text-dark fw-bold">Vehículos Activos</p>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer" id="contacto">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <h5 class="fw-bold text-white mb-3"><i class="fas fa-leaf text-success me-2"></i> EcoCusco Empresarial</h5>
          <p class="text-muted pe-4">Transformando la recolección de basura en una cadena limpia, justa y sostenible mediante la gestión tercerizada. Contribuimos desde la base.</p>
        </div>
        <div class="col-md-2">
          <h6 class="fw-bold text-white mb-3">Compañía</h6>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="nosotros.php">Sobre Nosotros</a></li>
            <li class="mb-2"><a href="servicios.php">Nuestros Planes</a></li>
            <li class="mb-2"><a href="nosotros.php">Casos y Estadísticas</a></li>
            <li class="mb-2"><a href="reportes.php">Portal Cívico</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h6 class="fw-bold text-white mb-3">Contacto Comercial</h6>
          <p class="mb-2 text-muted"><i class="fas fa-envelope text-success me-2"></i> ventas@ecocusco.com</p>
          <p class="mb-2 text-muted"><i class="fas fa-phone text-success me-2"></i> +51 084 254 365</p>
          <p class="mb-2 text-muted"><i class="fas fa-map-marker-alt text-success me-2"></i> Av. Cultura 1230, Cusco Sur, Perú</p>
        </div>
        <div class="col-md-3">
          <h6 class="fw-bold text-white mb-3">Portal de Afiliados</h6>
          <p class="text-muted text-sm mb-3">Si ya cuentas con servicio contratado para tu vivienda, ingresa con las credenciales entregadas por tu junta vecinal.</p>
          <a href="login.php" class="btn btn-outline-success btn-sm w-100 py-2">Acceder a Mi Cuenta Seguro</a>
        </div>
      </div>
      <hr class="mt-5 mb-4 border-secondary">
      <div class="text-center text-muted small">
        &copy; <?php echo date('Y'); ?> EcoCusco Holdings S.A.C. Todos los derechos reservados.
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>