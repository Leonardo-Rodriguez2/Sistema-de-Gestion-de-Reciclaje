<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestros Servicios | EcoCusco</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10B981;
            --primary-dark: #059669;
            --dark: #1F2937;
            --light: #F3F4F6;
            --text-gray: #4B5563;
            --accent: #F59E0B;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light);
            color: var(--dark);
        }

        /* Hero */
        .hero {
            background: linear-gradient(rgba(31, 41, 55, 0.85), rgba(16, 185, 129, 0.85)), url('https://images.unsplash.com/photo-1605600659873-d808a1d85715?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover no-repeat;
            padding: 80px 20px;
            text-align: center;
            color: white;
            position: relative;
        }

        .hero h1 {
            font-size: 45px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .section-title p {
            color: var(--text-gray);
            max-width: 600px;
            margin: 0 auto;
            font-size: 16px;
        }

        /* Pricing/Services Cards */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }

        .service-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-align: center;
            border-top: 5px solid transparent;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .service-card.popular {
            border-top-color: var(--primary);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.15);
        }

        .popular-badge {
            background: var(--primary);
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 30px;
            position: absolute;
            top: 20px;
            right: -30px;
            transform: rotate(45deg);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: var(--light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 35px;
            color: var(--primary);
        }

        .service-card.commercial .service-icon { color: var(--accent); background: #FEF3C7;}
        .service-card.special .service-icon { color: #8B5CF6; background: #EDE9FE;}

        .service-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .service-desc {
            color: var(--text-gray);
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            text-align: left;
            margin-bottom: 30px;
        }

        .feature-list li {
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-list i {
            color: var(--primary);
            font-size: 16px;
        }

        .btn-hire {
            display: inline-block;
            width: 100%;
            padding: 12px;
            background: var(--light);
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
            box-sizing: border-box;
        }

        .service-card.popular .btn-hire {
            background: var(--primary);
            color: white;
        }

        .btn-hire:hover {
            background: var(--dark);
            color: white;
        }

        /* Banner Final */
        .bottom-banner {
            background: url('https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover no-repeat;
            border-radius: 16px;
            padding: 60px;
            text-align: center;
            color: white;
            position: relative;
        }

        .bottom-banner::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(31, 41, 55, 0.85);
            border-radius: 16px;
            z-index: 1;
        }

        .bottom-banner .content {
            position: relative;
            z-index: 2;
        }

        /* Secciones extra (Proceso, FAQ, Tabla) */
        .process-section { padding: 60px 0; background-color: white; border-radius: 16px; margin-bottom: 60px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .process-step { margin-bottom: 30px; }
        .process-step i { font-size: 3rem; color: var(--primary); margin-bottom: 15px; }
        
        .faq-section { margin-bottom: 60px; }
        .accordion-item { border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 10px; overflow: hidden; }
        .accordion-header { background: white; padding: 20px; font-weight: 600; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .accordion-body { background: #F9FAFB; padding: 20px; font-size: 14px; display: none; border-top: 1px solid #E5E7EB; color: var(--text-gray); line-height: 1.6;}
        
        .compare-section { margin-bottom: 60px; }
        .compare-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .compare-table th { background: var(--dark); color: white; padding: 15px; text-align: center; }
        .compare-table td { padding: 15px; text-align: center; border-bottom: 1px solid #E5E7EB; }
        .compare-table td.eco-col { background: #ECFDF5; color: var(--primary-dark); font-weight: 600; }
        
        @media (max-width: 900px) {
            .services-grid { grid-template-columns: 1fr; }
            .process-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>

    <?php include 'components/header.php'; ?>

    <section class="hero">
        <h1>Soluciones de Recolección a Medida</h1>
        <p>Desde barrios organizados hasta complejos corporativos, diseñamos rutas eficientes para garantizar la higiene de su entorno con trazabilidad 100% digital.</p>
    </section>

    <div class="container">
        
        <div class="section-title">
            <h2>Nuestros Planes de Servicio</h2>
            <p>Conozca las diferentes modalidades de recolección y facturación mensual. Todos los planes incluyen acceso gratuito a nuestra Plataforma de Clientes.</p>
        </div>

        <div class="services-grid">
            
            <!-- Plan Residencial -->
            <div class="service-card popular">
                <div class="popular-badge">Más Elegido</div>
                <div class="service-icon"><i class="fas fa-house-chimney-window"></i></div>
                <h3 class="service-title">Plan Vecinal</h3>
                <p class="service-desc">Para Comités, Urbanizaciones o Barrios completos que desean ordenar su recolección de forma unificada.</p>
                
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Frecuencia Intradiaria (3 por sem.)</li>
                    <li><i class="fas fa-check"></i> Perfil individual por cada familia</li>
                    <li><i class="fas fa-check"></i> Dashboard Financiero por Vivienda</li>
                    <li><i class="fas fa-check"></i> Recolección diferenciada básica</li>
                    <li><i class="fas fa-check"></i> Acceso global a Reportes Express</li>
                </ul>

                <a href="nosotros.php" class="btn-hire">Solicitar Cotización</a>
            </div>

            <!-- Plan Comercial -->
            <div class="service-card commercial">
                <div class="service-icon"><i class="fas fa-store"></i></div>
                <h3 class="service-title">Plan Comercial</h3>
                <p class="service-desc">Atención exclusiva para Negocios, Restaurantes o Plazas Comerciales con gran volumen de generación diaria.</p>
                
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Recolección Nocturna Diaria</li>
                    <li><i class="fas fa-check"></i> Contenedores EcoCusco de regalo</li>
                    <li><i class="fas fa-check"></i> Facturación corporativa mensual</li>
                    <li><i class="fas fa-check"></i> Certificado Anual de Higiene</li>
                    <li><i class="fas fa-check"></i> Capacitación a empleados</li>
                </ul>

                <a href="nosotros.php" class="btn-hire">Contactar a Ventas</a>
            </div>

            <!-- Servicios Especiales -->
            <div class="service-card special">
                <div class="service-icon"><i class="fas fa-truck-ramp-box"></i></div>
                <h3 class="service-title">Recojo Especial</h3>
                <p class="service-desc">Contratación por evento (Pago Único) para recolección de volúmenes excepcionales, desmonte o electrónicos.</p>
                
                <ul class="feature-list">
                    <li><i class="fas fa-check"></i> Maquinaria Pesada disponible</li>
                    <li><i class="fas fa-check"></i> Disposición certificada (RAEE)</li>
                    <li><i class="fas fa-check"></i> Retiro de Muebles grandes</li>
                    <li><i class="fas fa-check"></i> Limpieza post-construcción</li>
                    <li><i class="fas fa-check"></i> Cero intermediarios</li>
                </ul>

                <a href="nosotros.php" class="btn-hire">Agendar Recojo Único</a>
            </div>

        </div>

        <!-- Proceso Logístico -->
        <div class="process-section px-4">
            <h2 class="fw-bold mb-4" style="color: var(--dark); font-size: 28px;">Nuestro Proceso Logístico de Recolección</h2>
            <p class="text-muted mb-5" style="max-width: 700px; margin: 0 auto;">Desde que cerramos una bolsa hasta que el residuo llega a su punto de valorización, empleamos un sistema rastreable.</p>
            
            <div class="process-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div class="process-step">
                    <i class="fas fa-route"></i>
                    <h5 class="fw-bold">1. Planificación</h5>
                    <p class="text-muted small">Mapeo GPS del barrio para optimizar la ruta y reducir la huella de carbono de nuestros camiones.</p>
                </div>
                <div class="process-step">
                    <i class="fas fa-trash-can"></i>
                    <h5 class="fw-bold">2. Recojo Exacto</h5>
                    <p class="text-muted small">Vehículos modernos recogen puntualmente en su puerta o punto de acopio designado, sin dejar rastro.</p>
                </div>
                <div class="process-step">
                    <i class="fas fa-recycle"></i>
                    <h5 class="fw-bold">3. Segregación</h5>
                    <p class="text-muted small">Transportamos los residuos sólidos a plantas de transferencia donde se separa material reciclable (PET, Cartón).</p>
                </div>
                <div class="process-step">
                    <i class="fas fa-earth-americas"></i>
                    <h5 class="fw-bold">4. Disp. Final</h5>
                    <p class="text-muted small">Entierro en relleno sanitario certificado de lo no reciclable, garantizando 0% contaminación de ríos locales.</p>
                </div>
            </div>
        </div>

        <!-- Tabla Comparativa -->
        <div class="compare-section text-center">
            <h2 class="fw-bold mb-4" style="color: var(--dark); font-size: 28px;">¿Por qué cambiar a EcoCusco?</h2>
            <p class="text-muted mb-4">La diferencia entre un servicio tercerizado corporativo y los métodos tradicionales.</p>
            
            <div style="overflow-x: auto;">
                <table class="compare-table">
                    <thead>
                        <tr>
                            <th style="text-align: left; background: var(--light); color: var(--dark);">Característica del Servicio</th>
                            <th style="background: var(--primary);">Con EcoCusco</th>
                            <th style="background: #ef4444;">Recolección Informal o Pública</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: left; font-weight: 600;">Puntualidad de Rutas</td>
                            <td class="eco-col"><i class="fas fa-check-circle"></i> Horarios Garantizados</td>
                            <td style="color: #6B7280;">Incierto (Demoras)</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 600;">Personal</td>
                            <td class="eco-col"><i class="fas fa-check-circle"></i> Uniformado y Asegurado</td>
                            <td style="color: #6B7280;">Irregular</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 600;">Gestión de Multas</td>
                            <td class="eco-col"><i class="fas fa-check-circle"></i> Emisión de Certificados Ambientales</td>
                            <td style="color: #6B7280;">Sin Respaldo Documentario</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 600;">Atención a Incidentes</td>
                            <td class="eco-col"><i class="fas fa-check-circle"></i> Portal de Reportes Web 24/7</td>
                            <td style="color: #6B7280;">Sin canal de reclamos directos</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FAQ -->
        <div class="faq-section">
            <h2 class="fw-bold mb-4 text-center" style="color: var(--dark); font-size: 28px;">Preguntas Frecuentes</h2>
            
            <div class="accordion-item">
                <div class="accordion-header" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">
                    ¿Tienen cobertura en toda la región de Cusco? <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body">
                    Actualmente operamos en el casco urbano de Cusco ciudad, Wanchaq, San Sebastián y Santiago. Estamos planificando la expansión hacia San Jerónimo y Poroy para el segundo semestre del año. Consúltanos para habilitar un circuito si tu asociación es grande.
                </div>
            </div>
            
            <div class="accordion-item">
                <div class="accordion-header" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">
                    ¿Cómo se realiza el pago del Plan Vecinal? <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body">
                    Cada vivienda perteneciente a un Plan Vecinal recibe un usuario y contraseña. Al ingresar a nuestra Plataforma de Clientes, el vecino verá su estado de cuenta mensual y el Gestor del Barrio registrará los pagos consolidados. Las facturas son electrónicas.
                </div>
            </div>

            <div class="accordion-item">
                <div class="accordion-header" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">
                    ¿Qué ocurre si la basura recolectada sobrepasa mi plan? <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-body">
                    Nuestro personal está capacitado para recoger la carga ordinaria. En caso de volúmenes excepcionales (como mudanzas o fiestas particulares), se reporta al sistema y se emite un recargo por "Recojo Especial" a esa sola vivienda o local comercial tras notificárselo vía correo electrónico.
                </div>
            </div>
        </div>

        <div class="bottom-banner">
            <div class="content">
                <h2 style="font-size:32px; margin-bottom: 20px;">¿No está seguro qué plan le conviene?</h2>
                <p style="font-size:18px; margin-bottom: 30px;">Agende una reunión gratuita de 15 minutos con nuestros asesores de impacto ambiental. Evaluaremos la zona y el volumen para ofrecerle la mejor tarifa.</p>
                <a href="nosotros.php" class="btn-hire" style="background:var(--primary); color:white; border:none; width:auto; padding: 15px 40px; border-radius:30px;">Ir a Formulario de Solicitud <i class="fas fa-arrow-right" style="margin-left: 10px;"></i></a>
            </div>
        </div>

    </div>

    <footer style="text-align: center; padding: 40px; color: var(--text-gray); font-size: 14px; background: white; border-top: 1px solid #E5E7EB;">
        © <?php echo date('Y'); ?> EcoCusco S.A. Todos los derechos reservados.<br>
        Plataforma corporativa de gestión de recursos sólidos urbanos.
    </footer>

</body>
</html>
