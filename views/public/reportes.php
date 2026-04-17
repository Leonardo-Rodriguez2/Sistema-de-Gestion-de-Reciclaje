<?php
$title = "Reportar Incidentes | EcoCusco";
ob_start();
?>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Ayúdanos a mantener la comunidad limpia</h1>
        <p>¿Notaste acumulación de basura o un contenedor desbordado? Reportarlo toma menos de un minuto y moviliza a nuestros equipos.</p>
    </section>

    <!-- Main Content -->
    <main class="container">
        <!-- Izquierda: Información Corporativa -->
        <div class="info-panel">
            <img src="https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Personal de Limpieza de EPSIC">
            <h2>¿Por qué hacemos esto?</h2>
            <p>En EPSIC creemos que la higiene urbana es responsabilidad de todos. A través de este portal público, cualquier ciudadano puede enviarnos alertas inmediatas para atender zonas críticas que se encuentren en nuestra ruta programada habitual.</p>
            
            <ul class="benefits-list">
                <li>
                    <i class="fas fa-truck-fast"></i>
                    <div>
                        <strong>Respuesta Rápida</strong><br>
                        Nuestras unidades en ruta reciben su alerta en instantes.
                    </div>
                </li>
                <li>
                    <i class="fas fa-leaf"></i>
                    <div>
                        <strong>Cuidado del Medio Ambiente</strong><br>
                        Evitamos la contaminación de calles, ríos y áreas turísticas previniendo focos infecciosos.
                    </div>
                </li>
                <li>
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <strong>Mapeo Inteligente</strong><br>
                        Los reportes nos ayudan a rediseñar y mejorar las frecuencias de recolección en los barrios más vulnerables.
                    </div>
                </li>
            </ul>

            <div style="background: var(--light); padding: 20px; border-radius: 8px; text-align: center;">
                <h4 style="margin: 0 0 10px 0; color: var(--dark);">¿Quieres contratar servicio regular para tu zona?</h4>
                <p style="font-size: 14px; margin-bottom: 15px;">Habla con un asesor y pide el registro oficial para tu vivienda/barrio.</p>
                <a href="/reciclaje/views/public/nosotros.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Ver Planes y Estadísticas &rarr;</a>
            </div>
        </div>

        <!-- Derecha: El Formulario Funcional -->
        <div class="form-panel">
            <h3 style="margin-top: 0; font-size: 22px; color: var(--dark); margin-bottom: 25px;">
                <i class="fas fa-map-location-dot" style="color: var(--primary);"></i> Formulario de Incidentes
            </h3>

            <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                <div class="success-banner">
                    <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                    <div>
                        <strong>¡Reporte Enviado!</strong><br>
                        Gracias. Nuestro equipo ha sido notificado y se dirigirá a la zona.
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                
                <div class="form-group">
                    <label>Dirección Exacta del Incidente *</label>
                    <input type="text" name="ubicacion" placeholder="Ej: Av. El Sol esq. Puente Rosario" required>
                    <div class="map-preview">
                        [ Imagen del insidente o de lo que deseas reportar ] 
                        [ Opcional ]
                    </div>
                </div>

                <div class="form-group">
                    <label>¿Qué tipo de insidente desea reportar? (Elige máximo 3)</label>
                    <div class="residue-grid">
                        <label class="residue-option">
                            <input type="checkbox" name="tipo_incidente[]" value="Basura acumulada en lugar indebido" class="tipo-residuo">
                            <div class="residue-card"><i class="fas fa-dumpster"></i> Basura acumulada en lugar indebido</div>
                        </label>
                        <label class="residue-option">
                            <input type="checkbox" name="tipo_incidente[]" value="Contenedor lleno o desbordado" class="tipo-residuo">
                            <div class="residue-card"><i class="fas fa-trash-alt"></i> Contenedor lleno o desbordado</div>
                        </label>
                        <label class="residue-option">
                            <input type="checkbox" name="tipo_incidente[]" value="No pasó el camión recolector" class="tipo-residuo">
                            <div class="residue-card"><i class="fas fa-truck"></i> No pasó el camión recolector</div>
                        </label>
                        <label class="residue-option">
                            <input type="checkbox" name="tipo_incidente[]" value="Quema o incendio de basura" class="tipo-residuo">
                            <div class="residue-card"><i class="fas fa-fire"></i> Quema o incendio de basura en la comunidad</div>
                        </label>
                        <label class="residue-option">
                            <input type="checkbox" name="tipo_incidente[]" value="Animales esparciendo basura" class="tipo-residuo">
                            <div class="residue-card"><i class="fas fa-dog"></i> Animales esparciendo basura</div>
                        </label>
                        <label class="residue-option">
                            <input type="checkbox" name="tipo_incidente[]" value="Vertido de residuos peligrosos" class="tipo-residuo">
                            <div class="residue-card"><i class="fas fa-biohazard"></i> Vertido de residuos peligrosos</div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nivel de Urgencia</label>
                    <select name="urgencia" required>
                        <option value="Normal">Normal - Basura acumulada en la vereda</option>
                        <option value="Alta">Alta - Contenedor desbordado impidiendo el paso</option>
                        <option value="Crítica">Crítica - Riesgo para la salud o animales escarbando</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Descripción / Detalles adicionales *</label>
                    <textarea name="descripcion" rows="4" placeholder="Bríndanos detalles que ayuden a los recolectores a identificar mejor el problema..." required></textarea>
                </div>

                <button type="submit" class="submit-btn" style="background-color: var(--primary); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    Enviar Alerta al Equipo <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </main>

    <!-- IMPACT SECTION -->
    <section class="impact-section">
        <div class="impact-container">
            <div>
                <h2 style="font-size: 32px; margin-bottom: 20px;">El Impacto de tus Reportes Cívicos</h2>
                <p style="font-size: 16px; opacity: 0.8; line-height: 1.6; margin-bottom: 30px;">
                    Cada vez que utilizas este portal, no solo limpias una calle. Evitas focos infecciosos, previenes que los plásticos tapen los drenajes pluviales en época de lluvias y proteges a los animales callejeros de ingerir desechos tóxicos.
                </p>
                <div class="impact-stats">
                    <div class="impact-stat-card">
                        <h3 style="color: var(--primary); font-size: 28px; margin: 0 0 5px 0;">0</h3>
                        <p style="margin: 0; font-size: 14px; opacity: 0.9;">Reportes Atendidos</p>
                    </div>
                    <div class="impact-stat-card">
                        <h3 style="color: var(--primary); font-size: 28px; margin: 0 0 5px 0;">0</h3>
                        <p style="margin: 0; font-size: 14px; opacity: 0.9;">Focos Infecciosos</p>
                    </div>
                    <div class="impact-stat-card">
                        <h3 style="color: var(--primary); font-size: 28px; margin: 0 0 5px 0;"></h3>
                        <p style="margin: 0; font-size: 14px; opacity: 0.9;"></p>
                    </div>
                    <div class="impact-stat-card">
                        <h3 style="color: var(--primary); font-size: 28px; margin: 0 0 5px 0;">3h</h3>
                        <p style="margin: 0; font-size: 14px; opacity: 0.9;">Tiempo Resp. Promedio</p>
                    </div>
                </div>
            </div>
            <div>
                <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Impacto Ambiental" style="width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            </div>
        </div>
    </section>

    <!-- SUCCESS CASES -->
    <section class="cases-section">
        <div class="cases-container text-center">
            <h2 class="fw-bold mb-3" style="color: var(--dark); font-size: 28px;">Casos de Éxito Recientes</h2>
            <p class="text-muted mb-5">Resultados reales del trabajo conjunto de nuestra central logística y la ciudadanía.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; text-align: left;">
                
                <div class="case-card">
                    <div class="case-images">
                        <div style="width:50%; background: #FFE4E6; display:flex; align-items:center; justify-content:center; color:#BE123C; font-weight:bold; font-size:18px;">Antes</div>
                        <div style="width:50%; background: #D1FAE5; display:flex; align-items:center; justify-content:center; color:#047857; font-weight:bold; font-size:18px;">Después</div>
                    </div>
                    <div class="case-content">
                        <h4 style="color: var(--dark); margin-bottom: 10px; font-weight: 700;">Recuperación Mercado Sur</h4>
                        <p class="text-muted small mb-0">Un reporte anónimo alertó sobre 5 toneladas de desmonte tapando un acceso peatonal desde hacía semanas. El escuadrón de "Recojo Especial" despejó el área en 45 minutos y lavó la calle.</p>
                    </div>
                </div>

                <div class="case-card">
                    <div class="case-images">
                        <div style="width:50%; background: #FFE4E6; display:flex; align-items:center; justify-content:center; color:#BE123C; font-weight:bold; font-size:18px;">Antes</div>
                        <div style="width:50%; background: #D1FAE5; display:flex; align-items:center; justify-content:center; color:#047857; font-weight:bold; font-size:18px;">Después</div>
                    </div>
                    <div class="case-content">
                        <h4 style="color: var(--dark); margin-bottom: 10px; font-weight: 700;">Contenedores Sector A</h4>
                        <p class="text-muted small mb-0">A través del mapa y la geolocalización de varios reportes idénticos en 15 días, identificamos que un barrio no contaba con suficientes tachos. Se instalaron 5 contenedores fijos EcoCusco.</p>
                    </div>
                </div>

                <div class="case-card">
                    <div class="case-images">
                        <div style="width:50%; background: #FFE4E6; display:flex; align-items:center; justify-content:center; color:#BE123C; font-weight:bold; font-size:18px;">Antes</div>
                        <div style="width:50%; background: #D1FAE5; display:flex; align-items:center; justify-content:center; color:#047857; font-weight:bold; font-size:18px;">Después</div>
                    </div>
                    <div class="case-content">
                        <h4 style="color: var(--dark); margin-bottom: 10px; font-weight: 700;">Prevención Vía Evitamiento</h4>
                        <p class="text-muted small mb-0">Vecinos reportaron el inicio de quemas ilegales de basura en las riberas del río Huatanay. La alerta nos permitió llegar junto con los agentes de seguridad a tiempo, evitando la propagación de toxinas al aire.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/public_layout.php';
?>

<script>
        // Limitar selección de checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.tipo-residuo');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const selected = document.querySelectorAll('.tipo-residuo:checked');
                    if (selected.length > 3) {
                        this.checked = false;
                        // Notificar silenciosamente si fuera necesario, o simplemente impedir la acción
                    }
                });
            });
        });
</script>

<style>
    :root {
            --primary: #10B981;
            --primary-dark: #059669;
            --dark: #1F2937;
            --light: #F3F4F6;
            --text-gray: #4B5563;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9)), url('https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover no-repeat;
            padding: 60px 20px;
            text-align: center;
            color: white;
        }

        .hero h1 {
            font-size: 40px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .hero p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Split Layout */
        .container {
            max-width: 1200px;
            margin: -40px auto 40px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            position: relative;
            z-index: 10;
        }

        @media (max-width: 900px) {
            .container { grid-template-columns: 1fr; margin-top: 20px; }
        }

        /* Left Side: Info & Marketing */
        .info-panel {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .info-panel img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-panel h2 {
            font-size: 24px;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }

        .info-panel p {
            color: var(--text-gray);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }

        .benefits-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            color: var(--text-gray);
        }
        
        .benefits-list i {
            color: var(--primary);
            font-size: 20px;
            margin-top: 2px;
        }

        /* Right Side: Form */
        .form-panel {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-top: 5px solid var(--primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        input[type=\"text\"], textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--dark);
            box-sizing: border-box;
            transition: all 0.3s;
            background: #F9FAFB;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            background: white;
        }

        /* Residuos Custom Checkboxes */
        .residue-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .residue-option {
            position: relative;
        }

        .residue-option input[type=\"checkbox\"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .residue-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            color: var(--text-gray);
            font-weight: 500;
            font-size: 14px;
        }

        .residue-option input:checked ~ .residue-card {
            border-color: var(--primary);
            background: #ECFDF5;
            color: var(--primary-dark);
        }

        .map-preview {
            width: 100%;
            height: 200px;
            background: #E5E7EB;
            border-radius: 8px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9CA3AF;
            font-size: 14px;
            border: 1px dashed #9CA3AF;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .success-banner {
            background: #D1FAE5;
            border: 1px solid #10B981;
            color: #065F46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
        }

        /* New Sections */
        .impact-section {
            background-color: var(--dark);
            color: white;
            padding: 60px 20px;
            margin-top: 60px;
        }
        .impact-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        .impact-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .impact-stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        .cases-section {
            padding: 80px 20px;
            background-color: white;
        }
        .cases-container {
            max-width: 1200px; margin: 0 auto;
        }
        .case-card {
            border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden;
            display: flex; flex-direction: column;
        }
        .case-images { display: flex; height: 200px; }
        .case-images img { width: 50%; object-fit: cover; }
        .case-content { padding: 25px; }

        @media (max-width: 900px) {
            .impact-container { grid-template-columns: 1fr; }
        }
</style>