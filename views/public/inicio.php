<?php
// views/public/inicio.php — Página de Inicio Pública
$title = "EPSIC - Servicio de Recolección Corporativa y Residencial";

ob_start();
?>
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-7">
          <span class="badge bg-light text-success mb-3 px-3 py-2 fs-6 rounded-pill">Expertos en Recolección y Reciclaje</span>
          <h1>Líderes en Gestión Ambiental Integral</h1>
          <p class="lead mb-4 mt-3">Proveemos soluciones integrales de recolección de residuos para zonas comerciales y residenciales. Deja que nos encarguemos de mantener tu barrio limpio, saludable y 100% sustentable con tecnología de punta.</p>
          <a href="/reciclaje/views/public/servicios.php" class="btn btn-light text-success fw-bold px-4 py-3 me-3 rounded-pill shadow-sm">Ver Planes Disponibles</a>
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
            <div class="mb-4"><i class="fas fa-truck fa-3x" style="color: #10B981;"></i></div>
            <h4 class="fw-bold mb-3">Recolección Residencial Diaria</h4>
            <p class="text-muted">Servicio tercerizado de limpieza y de transporte de residuos con flotas propias certificadas. Olvídate de la acumulación en las esquinas de tu urbanización.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 card-feature p-4 text-center">
            <div class="mb-4"><i class="fas fa-recycle fa-3x" style="color: #10B981;"></i></div>
            <h4 class="fw-bold mb-3">Programa de Valorización</h4>
            <p class="text-muted">Desarrollo y gestión de campañas exclusivas de segregación en la fuente con alianzas vecinales para promover la economía circular.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 card-feature p-4 text-center">
            <div class="mb-4"><i class="fas fa-file-invoice-dollar fa-3x" style="color: #10B981;"></i></div>
            <h4 class="fw-bold mb-3">Cobranza y Tableros Digitales</h4>
            <p class="text-muted">Plataforma transparente con emisión automatizada de reportes, historiales de pago en línea y estado de deudas por vivienda afiliada.</p>
          </div>
        </div>
      </div>
      <div class="text-center mt-5">
        <a href="/reciclaje/views/public/servicios.php" class="btn btn-custom px-5 py-3 rounded-pill shadow">Descubre las diferencias entre Planes</a>
      </div>
    </div>
  </section>

  <!-- SECCIÓN: Cómo Funciona -->
  <section class="steps-section">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold" style="color: #064E3B;">¿Cómo empezar a trabajar con nosotros?</h2>
        <p class="text-muted lead">Un proceso diseñado para ser rápido, transparente y 100% digitalizado.</p>
      </div>
      <div class="row text-center g-4 position-relative">
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

  <!-- SECCIÓN: Por qué Elegirnos -->
  <section class="why-choose-us">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-5 mb-5 mb-lg-0">
          <h2 class="fw-bold mb-4 display-5">Más que camiones de basura, somos promotores de calidad de vida.</h2>
          <p class="lead mb-4 opacity-75">Nuestra infraestructura logística y digital nos pone a la vanguardia. Garantizamos un servicio impecable que añade valor a tu barrio y a la ciudad.</p>
          <ul class="list-unstyled">
            <li class="mb-3 fs-5"><i class="fas fa-check-circle text-success me-3"></i> Trazabilidad de camiones en ruta.</li>
            <li class="mb-3 fs-5"><i class="fas fa-check-circle text-success me-3"></i> Personal unificado, uniformado y asegurado.</li>
            <li class="mb-3 fs-5"><i class="fas fa-check-circle text-success me-3"></i> Atención al cliente 24/7 para emergencias.</li>
            <li class="fs-5"><i class="fas fa-check-circle text-success me-3"></i> Promotores activos de certificación verde comercial.</li>
          </ul>
        </div>
        <div class="col-lg-6 offset-lg-1">
          <div class="row g-4 text-center">
            <div class="col-6"><div class="p-4 bg-white bg-opacity-10 rounded-4"><i class="fas fa-shield-halved why-icon"></i><h4 class="fw-bold">100% Legal</h4><p class="small opacity-75 mb-0">Disposición final solo en rellenos sanitarios certificado.</p></div></div>
            <div class="col-6"><div class="p-4 bg-white bg-opacity-10 rounded-4"><i class="fas fa-hand-holding-dollar why-icon"></i><h4 class="fw-bold">Costos Claros</h4><p class="small opacity-75 mb-0">Sin sorpresas. Tarifas fijas estandarizadas facturadas.</p></div></div>
            <div class="col-6"><div class="p-4 bg-white bg-opacity-10 rounded-4"><i class="fas fa-seedling why-icon"></i><h4 class="fw-bold">Baja Emisión</h4><p class="small opacity-75 mb-0">Vehículos modernos con estándar y limitados en ruido.</p></div></div>
            <div class="col-6"><div class="p-4 bg-white bg-opacity-10 rounded-4"><i class="fas fa-laptop-code why-icon"></i><h4 class="fw-bold">Alta Tecnología</h4><p class="small opacity-75 mb-0">Sistema Web para gestionar reportes de incidentes al instante.</p></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="py-5" style="background-color: #ECFDF5;">
    <div class="container text-center py-5">
      <h2 class="fw-bold mb-4" style="color: #064E3B;">¿Listo para darle la bienvenida al progreso y la limpieza?</h2>
      <p class="lead text-muted mx-auto mb-5" style="max-width: 700px;">
        Nuestros asesores completan decenas de visitas semanales. Separa la tuya hoy mismo y protege el medio ambiente al confiar en verdaderos expertos logísticos.
      </p>
      <a href="/reciclaje/views/public/nosotros.php" class="btn btn-custom btn-lg shadow-sm px-5 py-3 rounded-pill">
        Llenar Solicitud de Contratación <i class="fas fa-file-signature ms-2"></i>
      </a>
    </div>
  </section>

  <section class="stats-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-3"><h2 class="display-4 fw-bold">0</h2><p class="fs-5 text-dark fw-bold">Años</p></div>
        <div class="col-md-3"><h2 class="display-4 fw-bold">0</h2><p class="fs-5 text-dark fw-bold">Familias Afiliadas</p></div>
        <div class="col-md-3"><h2 class="display-4 fw-bold">0</h2><p class="fs-5 text-dark fw-bold">Barrios Afiliados</p></div>
        <div class="col-md-3"><h2 class="display-4 fw-bold">0</h2><p class="fs-5 text-dark fw-bold">Vehículos Activos</p></div>
      </div>
    </div>
  </section>

<?php

$content = ob_get_clean();
include __DIR__ . '/../layouts/public_layout.php';
?>

<style>
    .hero-section {
      background: linear-gradient(rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9)), url('https://images.unsplash.com/photo-1595278069441-2cf29f8005a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
      padding: 100px 0; color: white; text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }
    .hero-section h1 { font-weight: 700; font-size: 3rem; }
    .features-section { background-color: #f8f9fa; padding: 80px 0; }
    .steps-section { padding: 80px 0; background-color: white; }
    .step-circle { width: 80px; height: 80px; border-radius: 50%; background-color: #ECFDF5; color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 20px; border: 3px solid #10B981; }
    .why-choose-us { background-color: #064E3B; color: white; padding: 80px 0; }
    .why-icon { color: #34D399; font-size: 2.5rem; margin-bottom: 20px; }
    .testimonials-section { padding: 80px 0; background-color: #f8f9fa; }
    .stats-section { background-color: #10B981; color: white; padding: 60px 0; text-align: center; }
    .btn-custom { background-color: #059669; color: white; border: none; padding: 12px 30px; font-weight: 600; font-size: 1.1rem; border-radius: 8px;}
    .btn-custom:hover { background-color: #047857; color: white; }
    .card-feature { transition: transform 0.3s ease; border: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-radius: 12px;}
    .card-feature:hover { transform: translateY(-5px); }
</style>