<?php
// Cargar los datos de las estadísticas reales
$datos = include('../../data/estadisticas.php');
if (is_array($datos)) {
    extract($datos); 
}

// Lógica Formulario Solicitud
$mensaje_form = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'solicitud') {
    // Aquí podrías guardar la solicitud en BD o enviar un correo
    // Simularemos un éxito inmediato para la plataforma:
    $mensaje_form = "¡Solicitud registrada correctamente! Un asesor de ventas se comunicará pronto al celular proporcionado.";
}

$title = "Nosotros | EcoCusco";
$extra_head = '<!-- Chart JS para cargar los gráficos de data/estadisticas.php -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

ob_start();
?>

    <section class="hero">
        <h1>Una ciudad limpia, el orgullo de todos</h1>
        <p>Somos EcoCusco, la empresa líder en gestión de residuos sólidos en la ciudad imperial encargada de modernizar la recolección comunitaria.</p>
    </section>

    <main class="container">
        
        <section class="section-about">
            <div class="about-text">
                <h2>¿Quiénes Somos?</h2>
                <p>Nuestra misión es transformar la forma en que los ciudadanos interactúan con el servicio de recolección de basura. A través de innovación tecnológica y rutas optimizadas, logramos que los vecindarios permanezcan libres de focos infecciosos.</p>
                <p>Implementamos soluciones donde el vecino tiene el control y nuestro personal está digitalmente conectado, garantizando una atención transparente a cada barrio que contrata nuestro servicio corporativo.</p>
                
                <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <i class="fas fa-bullseye" style="font-size: 24px; color: var(--primary); margin-bottom: 10px;"></i>
                        <h4 style="margin: 0 0 5px;">Misión Integral</h4>
                        <p style="font-size: 13px; color: var(--text-gray); margin:0;">Garantizar trazabilidad del residuo desde su origen hasta el centro de acopio.</p>
                    </div>
                    <div>
                        <i class="fas fa-earth-americas" style="font-size: 24px; color: var(--primary); margin-bottom: 10px;"></i>
                        <h4 style="margin: 0 0 5px;">Visión Cero-Huella</h4>
                        <p style="font-size: 13px; color: var(--text-gray); margin:0;">Ser el estándar nacional en recolección automatizada y reducción de emisiones.</p>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Reciclaje EcoCusco">
                <div class="badge-float">
                    <span><?php echo isset($totalReportes) ? $totalReportes : '100+'; ?></span>
                    <span style="font-size: 12px; font-weight: normal;">Zonas Atendidas</span>
                </div>
            </div>
        </section>

        <!-- Valores Corporativos -->
        <h2 class="text-center" style="font-size: 32px; color: var(--dark); margin-top: 40px; text-align: center;">Nuestros Valores</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-clock"></i>
                <h3 style="margin-bottom: 15px;">Puntualidad Extrema</h3>
                <p style="color: var(--text-gray); font-size: 14px;">El tiempo de nuestros clientes es oro. Operamos bajo cronogramas GPS estrictos que garantizan el recojo a la hora exacta acordada.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-leaf"></i>
                <h3 style="margin-bottom: 15px;">Ecología Real</h3>
                <p style="color: var(--text-gray); font-size: 14px;">Separamos, reciclamos y disponemos correctamente. Disminuimos el impacto de la ciudad en los entornos naturales del Cusco.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-handshake"></i>
                <h3 style="margin-bottom: 15px;">Transparencia</h3>
                <p style="color: var(--text-gray); font-size: 14px;">Facturación clara sin cobros ocultos. Cada cliente sabe exactamente por qué volumen está pagando mediante la plataforma web.</p>
            </div>
        </div>

        <!-- Historia -->
        <div class="history-section">
            <h2 style="font-size: 32px; color: var(--primary-dark); margin-bottom: 20px;">Nuestra Historia</h2>
            <p style="color: var(--text-gray); line-height: 1.8; max-width: 800px; margin: 0 auto;">Nacimos hace más de 12 años ante la crisis de salubridad en las zonas periurbanas de Cusco. Comenzamos con un solo camión dando servicio vecinal, y hoy somos la principal flota logística privada que procesa más de 2.4 millones de toneladas al año, aliada con condominios, municipalidades locales y emporios comerciales. Hacia el 2030, nuestra meta es lograr "Cero Residuos a Vertedero" en nuestros planes corporativos premium.</p>
        </div>

        <!-- Equipo -->
        <div class="team-section">
            <h2 style="font-size: 32px; color: var(--dark); margin-bottom: 15px;">Nuestro Equipo Directivo y Operativo</h2>
            <p style="color: var(--text-gray); max-width: 600px; margin: 0 auto;">Profesionales comprometidos con el civismo y la ingeniería ambiental operativa.</p>
            
            <div class="team-grid">
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80" alt="CEO">
                    <div class="team-info">
                        <h4 style="margin: 0 0 5px; font-size: 20px;">Ing. Carlos Mendoza</h4>
                        <p style="color: var(--primary); font-weight: 600; margin: 0 0 15px; font-size: 14px;">Director Ejecutivo (CEO)</p>
                        <p style="color: var(--text-gray); font-size: 13px; margin: 0; line-height: 1.6;">Especialista en logística urbana con 20 años de experiencia, encargado de las alianzas vecinales y planificaciones estratégicas.</p>
                    </div>
                </div>
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80" alt="Operaciones">
                    <div class="team-info">
                        <h4 style="margin: 0 0 5px; font-size: 20px;">Arq. Valeria Ríos</h4>
                        <p style="color: var(--primary); font-weight: 600; margin: 0 0 15px; font-size: 14px;">Gerente de Operaciones GPS</p>
                        <p style="color: var(--text-gray); font-size: 13px; margin: 0; line-height: 1.6;">Diseña las rutas dinámicas y coordina las alertas tempranas de incidentes cívicos junto a la central telefónica y los conductores.</p>
                    </div>
                </div>
                <div class="team-card">
                    <img src="https://images.unsplash.com/photo-1622353381014-41f32a7629b3?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80" alt="Chofer">
                    <div class="team-info">
                        <h4 style="margin: 0 0 5px; font-size: 20px;">Julio Ramírez</h4>
                        <p style="color: var(--primary); font-weight: 600; margin: 0 0 15px; font-size: 14px;">Líder de Flota de Recojo</p>
                        <p style="color: var(--text-gray); font-size: 13px; margin: 0; line-height: 1.6;">Representante de los más de 50 recolectores que cada noche y madrugada mantienen las calles de nuestra ciudad impecables.</p>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Estadísticas Reales extraídas de data/estadisticas.php -->
    <?php if (isset($estadisticas)): ?>
    <section class="section-stats">
        <div class="container">
            <h2>Nuestros Números nos Respaldan</h2>
            <p class="subtitle">Cada kilo de residuo es una muestra de nuestro impacto. Datos extraídos en tiempo real de nuestras unidades operativas de las últimas 4 semanas.</p>
            
            <div class="stats-grid">
                <?php foreach($estadisticas as $tipo => $d): 
                    $cls = ($d['pct'] !== null ? ($d['pct'] >= 0 ? 'positive' : 'negative') : '');
                    $txt = ($d['pct'] !== null ? (($d['pct'] >= 0 ? '↗ ' : '↘ ') . $d['pct'] . '% que el mes anterior') : '—');
                ?>
                <div class="stat-card">
                    <div class="stat-label"><?= $tipo === 'Total' ? 'Volumen Total (Kg)' : $tipo.' (Kg)' ?></div>
                    <div class="stat-value"><?= number_format($d['actual'], 0) ?></div>
                    <div class="stat-trend <?= $cls ?>"><?= $txt ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Pasar variables PHP a JS -->
    <script>
        const meses = <?php echo json_encode($meses ?? []); ?>;
        const valMensual = <?php echo json_encode($valMensual ?? []); ?>;
        const mat = <?php echo json_encode($mat ?? []); ?>;
        const valDist = <?php echo json_encode($valDist ?? []); ?>;
    </script>
    <script src="../../js/charts.js"></script>
    <?php endif; ?>

    <main class="container">
        <div class="cta-section">
            <h2>¿Es usted directivo de un Barrio o Condominio?</h2>
            <p>Modernice la recolección de basura de sus vecinos. Obten precios especiales corporativos mensuales, reportes en zona y la máxima higiene para todas sus viviendas.</p>
            
            <?php if(!empty($mensaje_form)): ?>
                <div style="background: #D1FAE5; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> <?php echo $mensaje_form; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="nosotros.php">
                <input type="hidden" name="form_type" value="solicitud">
                <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 22px;"><i class="fas fa-file-signature" style="color:var(--primary);"></i> Formulario de Solicitud de Servicio</h3>
                
                <div class="form-grid">
                    <div>
                        <label>Nombre del Representante *</label>
                        <input type="text" name="nombre" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div>
                        <label>Teléfono Celular *</label>
                        <input type="text" name="telefono" placeholder="Ej: 987654321" required>
                    </div>
                    <div>
                        <label>Barrio / Urbanización *</label>
                        <input type="text" name="barrio" placeholder="Ej: Urb. Ttio" required>
                    </div>
                    <div>
                        <label>Tipo de Contrato</label>
                        <select name="tipo">
                            <option>Comité Vecinal (Varias casas)</option>
                            <option>Empresa Privada</option>
                            <option>Condominio/Edificio</option>
                            <option>Vivienda Unifamiliar</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 20px; text-align: left;">
                    <label>Correo Electrónico *</label>
                    <input type="email" name="correo" placeholder="correo@ejemplo.com" required>
                </div>

                <button type="submit" class="cta-btn">Solicitar Cotización Gratuita <i class="fas fa-arrow-right"></i></button>
            </form>
            
            <div style="margin-top: 25px; font-size: 14px; opacity: 0.6;">O llame al +51 084-556677 de Lunes a Viernes (08:00 - 18:00)</div>
        </div>
    </main>

<?php
$extra_css = "
        :root {
            --primary: #10B981;
            --primary-dark: #059669;
            --dark: #1F2937;
            --light: #F3F4F6;
            --text-gray: #4B5563;
        }

        /* Hero Nosotros */
        .hero {
            background: linear-gradient(rgba(31, 41, 55, 0.8), rgba(16, 185, 129, 0.8)), url('https://images.unsplash.com/photo-1574052309191-030a2f4a4df6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover no-repeat;
            padding: 100px 20px;
            text-align: center;
            color: white;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0% 100%);
        }

        .hero h1 {
            font-size: 50px;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -1.5px;
        }

        .hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Acerca de la Empresa */
        .section-about {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            padding: 60px 0;
            align-items: center;
        }

        .about-text h2 {
            font-size: 32px;
            color: var(--primary-dark);
            margin-top: 0;
            margin-bottom: 20px;
        }

        .about-text p {
            color: var(--text-gray);
            line-height: 1.8;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .about-image {
            position: relative;
        }

        .about-image img {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .badge-float {
            position: absolute;
            background: var(--primary);
            color: white;
            padding: 20px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 20px;
            text-align: center;
            bottom: -20px;
            right: -20px;
            box-shadow: 0 10px 20px rgba(16,185,129,0.3);
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        /* Sección de Impacto Real (Estadísticas Dinámicas) */
        .section-stats {
            background: white;
            padding: 80px 0;
            text-align: center;
        }

        .section-stats h2 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .section-stats .subtitle {
            color: var(--text-gray);
            font-size: 18px;
            margin-bottom: 50px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: var(--light);
            padding: 30px 20px;
            border-radius: 12px;
            border-bottom: 4px solid var(--primary);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 40px;
            font-weight: 800;
            color: var(--dark);
            margin: 10px 0;
        }

        .stat-label {
            color: var(--text-gray);
            text-transform: uppercase;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .stat-trend.positive { color: var(--primary); font-weight: 600; font-size: 14px; margin-top: 10px;}
        .stat-trend.negative { color: #EF4444; font-weight: 600; font-size: 14px; margin-top: 10px;}

        /* Call to Action - Contratar Servicio */
        .cta-section {
            background: var(--dark);
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 16px;
            margin: 60px 0;
        }

        .cta-section h2 { font-size: 32px; margin-bottom: 15px; }
        .cta-section p { font-size: 18px; margin-bottom: 30px; opacity: 0.8; max-width: 600px; margin-left: auto; margin-right: auto;}
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            text-align: left;
            margin-bottom: 20px;
        }

        .cta-section form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin: 20px auto 0;
            max-width: 700px;
            color: var(--dark);
        }

        .cta-section input, .cta-section select {
            width: 100%;
            padding: 12px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
            background: #F9FAFB;
        }

        .cta-section input:focus, .cta-section select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .cta-section label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .cta-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            transition: 0.3s;
            border: none;
            width: 100%;
            cursor: pointer;
        }

        .cta-btn:hover { background: var(--primary-dark); transform: scale(1.05); }

        /* Nuevas Secciones */
        .history-section { text-align: center; margin: 60px 0; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        .values-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin: 60px 0; }
        .value-card { background: white; padding: 40px 30px; border-radius: 12px; text-align: center; border-top: 5px solid var(--primary); box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .value-card i { font-size: 40px; color: var(--primary); margin-bottom: 20px; }
        
        .team-section { margin: 80px 0; text-align: center; }
        .team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 40px; }
        .team-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.03); text-align: left; }
        .team-card img { width: 100%; height: 250px; object-fit: cover; }
        .team-info { padding: 25px; }

        @media (max-width: 900px) {
            .section-about { grid-template-columns: 1fr; }
            .stats-grid, .values-grid, .team-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .stats-grid, .values-grid, .team-grid { grid-template-columns: 1fr; }
            .badge-float { display: none; }
        }
";
$extra_js = "";
$content = ob_get_clean();
include __DIR__ . '/../layouts/public_layout.php';
?>

