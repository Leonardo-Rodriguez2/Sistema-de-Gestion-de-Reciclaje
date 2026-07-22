<?php

namespace app\controllers;
use app\models\mainModel;
use PDO;

// =============================================
// app/controllers/calleController.php
// Acciones POST del Encargado de Calle (Rol 6)
// =============================================

class calleController extends mainModel {

    public function procesarAcciones() {
        global $user, $mensaje_exito, $mensaje_error;

        $this->asegurarColumnasPagos();

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        if (!$action) return;

        // 1. Solicitar Alta de Vivienda
        if ($action === 'solicitar_alta') {
            try {
                $calle = $this->obtenerCalleAsignada($user['id']);
                $this->ejecutarConsulta(
                    "INSERT INTO solicitudes_vivienda (tipo, calle_id, propietario, numero_casa, referencia, creado_por, estado)
                     VALUES ('Alta', ?, ?, ?, ?, ?, 'Pendiente')",
                    [$calle['calle_id'], $_POST['propietario'], $_POST['numero_casa'], $_POST['referencia'] ?? null, $user['id']]
                );
                $mensaje_exito = "Solicitud de registro enviada al encargado de barrio.";
            } catch (\Exception $e) {
                $mensaje_error = "Error al enviar solicitud: " . $e->getMessage();
            }
        }

        // 2. Solicitar Baja de Vivienda
        if ($action === 'solicitar_baja') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                $calle = $this->obtenerCalleAsignada($user['id']);

                $v = $this->ejecutarConsulta("SELECT id FROM viviendas WHERE id = ? AND calle_id = ?", [$vivienda_id, $calle['calle_id']])->fetch();
                if (!$v) throw new \Exception("Vivienda no válida o no pertenece a tu calle.");

                $chk = $this->ejecutarConsulta("SELECT id FROM solicitudes_vivienda WHERE vivienda_id = ? AND tipo = 'Baja' AND estado = 'Pendiente'", [$vivienda_id])->fetch();
                if ($chk) throw new \Exception("Ya existe una solicitud de baja pendiente para esta vivienda.");

                [$monto_deuda, $detalles_deuda] = $this->calcularDeuda($vivienda_id);

                $this->ejecutarConsulta(
                    "INSERT INTO solicitudes_vivienda (tipo, calle_id, vivienda_id, creado_por, estado, monto_deuda, detalles_deuda)
                     VALUES ('Baja', ?, ?, ?, 'Pendiente', ?, ?)",
                    [$calle['calle_id'], $vivienda_id, $user['id'], $monto_deuda, $detalles_deuda]
                );
                $mensaje_exito = "Solicitud de desincorporación enviada al Encargado de Barrio.";
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 3. Solicitar Renovación de Servicio
        if ($action === 'solicitar_renovacion') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                $calle = $this->obtenerCalleAsignada($user['id']);

                $v = $this->ejecutarConsulta("SELECT id, estado_servicio FROM viviendas WHERE id = ? AND calle_id = ?", [$vivienda_id, $calle['calle_id']])->fetch();
                if (!$v) throw new \Exception("Vivienda no válida.");
                if ($v['estado_servicio'] === 'Activo') throw new \Exception("El servicio ya está activo.");

                $chk = $this->ejecutarConsulta("SELECT id FROM solicitudes_vivienda WHERE vivienda_id = ? AND tipo = 'Renovacion' AND estado = 'Pendiente'", [$vivienda_id])->fetch();
                if ($chk) throw new \Exception("Ya existe una solicitud de renovación pendiente.");

                $this->ejecutarConsulta(
                    "INSERT INTO solicitudes_vivienda (tipo, calle_id, vivienda_id, creado_por, estado)
                     VALUES ('Renovacion', ?, ?, ?, 'Pendiente')",
                    [$calle['calle_id'], $vivienda_id, $user['id']]
                );
                $mensaje_exito = "Solicitud de renovación enviada al Encargado de Barrio.";
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 4. Marcar Pago Individual de una Vivienda (con comprobante)
        if ($action === 'marcar_pago_vivienda') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                $mes = (int)($_POST['mes'] ?? date('n'));
                $anio = (int)($_POST['anio'] ?? date('Y'));
                $ref = $_POST['referencia_pago'] ?? 'Sin referencia';
                
                $calle = $this->obtenerCalleAsignada($user['id']);

                $vivienda = $this->ejecutarConsulta(
                    "SELECT id, barrio_id FROM viviendas WHERE id = ? AND calle_id = ?", 
                    [$vivienda_id, $calle['calle_id']]
                )->fetch(\PDO::FETCH_ASSOC);

                if (!$vivienda) throw new \Exception("La vivienda no pertenece a tu sector.");

                $cuota = (float)$this->ejecutarConsulta(
                    "SELECT cuota_mensual FROM configuraciones_barrio WHERE barrio_id = ?",
                    [$vivienda['barrio_id']]
                )->fetchColumn() ?: 5.00;

                $chk = $this->ejecutarConsulta(
                    "SELECT id FROM cobros WHERE vivienda_id = ? AND mes = ? AND anio = ?",
                    [$vivienda_id, $mes, $anio]
                )->fetch();

                if ($chk) throw new \Exception("Esta vivienda ya registra un pago o cobro para este mes.");

                $lote_estado = $this->ejecutarConsulta(
                    "SELECT estado FROM lotes_calle WHERE calle_id=? AND anio=?",
                    [$calle['calle_id'], $anio]
                )->fetchColumn();

                if (in_array($lote_estado, ['Enviado', 'Aprobado'])) {
                    throw new \Exception("No puedes registrar pagos. El lote mensual ya fue cerrado o enviado.");
                }

                // Manejar subida de imagen de comprobante
                $comprobantePath = null;
                if (isset($_FILES['comprobante_pago']) && $_FILES['comprobante_pago']['error'] === UPLOAD_ERR_OK) {
                    $comprobantePath = $this->guardarArchivoPago($_FILES['comprobante_pago'], 'comprobantes');
                }

                // Guardar cobro con comprobante
                $this->ejecutarConsulta(
                    "INSERT INTO cobros (vivienda_id, mes, anio, monto, fecha_emision, fecha_vencimiento, referencia_pago, comprobante_calle, estado, estado_verificacion, tipo_cobro) 
                     VALUES (?, ?, ?, ?, CURRENT_DATE(), CURRENT_DATE(), ?, ?, 'Pagado', 'Pendiente', 'Servicio')",
                    [$vivienda_id, $mes, $anio, $cuota, $ref, $comprobantePath]
                );

                $mensaje_exito = "¡Pago registrado con éxito!" . ($comprobantePath ? " (con comprobante)" : "");
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 4b. Marcar Pago Individual (versión simplificada para modal)
        if ($action === 'marcar_pago_simple') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                $mes = (int)($_POST['mes'] ?? date('n'));
                $anio = (int)($_POST['anio'] ?? date('Y'));
                
                $calle = $this->obtenerCalleAsignada($user['id']);

                $vivienda = $this->ejecutarConsulta(
                    "SELECT id, barrio_id FROM viviendas WHERE id = ? AND calle_id = ?", 
                    [$vivienda_id, $calle['calle_id']]
                )->fetch(\PDO::FETCH_ASSOC);

                if (!$vivienda) throw new \Exception("La vivienda no pertenece a tu sector.");

                $cuota = (float)$this->ejecutarConsulta(
                    "SELECT cuota_mensual FROM configuraciones_barrio WHERE barrio_id = ?",
                    [$vivienda['barrio_id']]
                )->fetchColumn() ?: 5.00;

                $chk = $this->ejecutarConsulta(
                    "SELECT id FROM cobros WHERE vivienda_id = ? AND mes = ? AND anio = ? AND estado != 'Anulado'",
                    [$vivienda_id, $mes, $anio]
                )->fetch();

                if ($chk) throw new \Exception("Esta vivienda ya tiene registro para este mes.");

                $lote_estado = $this->ejecutarConsulta(
                    "SELECT estado FROM lotes_calle WHERE calle_id=? AND anio=?",
                    [$calle['calle_id'], $anio]
                )->fetchColumn();

                if (in_array($lote_estado, ['Enviado', 'Aprobado'])) {
                    throw new \Exception("El lote ya fue enviado.");
                }

                // Manejar comprobante
                $comprobantePath = null;
                if (isset($_FILES['comprobante_pago'])) {
                    if ($_FILES['comprobante_pago']['error'] === UPLOAD_ERR_OK) {
                        $comprobantePath = $this->guardarArchivoPago($_FILES['comprobante_pago'], 'comprobantes');
                    } elseif ($_FILES['comprobante_pago']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $errorCodes = [
                            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo (2MB)',
                            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                            UPLOAD_ERR_PARTIAL => 'El archivo solo se subió parcialmente',
                            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor',
                            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
                        ];
                        $errMsg = $errorCodes[$_FILES['comprobante_pago']['error']] ?? 'Código de error: ' . $_FILES['comprobante_pago']['error'];
                        throw new \Exception("Error al subir comprobante: " . $errMsg);
                    }
                }

                $ref = trim($_POST['referencia_pago'] ?? '');
                if ($ref === '') throw new \Exception("La referencia de pago es obligatoria.");

                $this->ejecutarConsulta(
                    "INSERT INTO cobros (vivienda_id, mes, anio, monto, fecha_emision, fecha_vencimiento, referencia_pago, comprobante_calle, estado, estado_verificacion, tipo_cobro) 
                     VALUES (?, ?, ?, ?, CURRENT_DATE(), CURRENT_DATE(), ?, ?, 'Pagado', 'Pendiente', 'Servicio')",
                    [$vivienda_id, $mes, $anio, $cuota, $ref, $comprobantePath]
                );

                $mensaje_exito = "Pago registrado correctamente." . ($comprobantePath ? " Con comprobante." : "");
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 5. Solicitar Exoneración de Cobro
        if ($action === 'solicitar_exoneracion') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                $calle = $this->obtenerCalleAsignada($user['id']);
                $tipo = $_POST['tipo_exencion'] ?? 'otro';
                $descripcion = trim($_POST['descripcion'] ?? '');

                // Validar que la vivienda pertenezca a la calle del usuario
                $v = $this->ejecutarConsulta(
                    "SELECT id, exento_cobro FROM viviendas WHERE id = ? AND calle_id = ?",
                    [$vivienda_id, $calle['calle_id']]
                )->fetch(\PDO::FETCH_ASSOC);
                if (!$v) throw new \Exception("La vivienda no pertenece a tu calle.");
                if ($v['exento_cobro']) throw new \Exception("Esta vivienda ya está exenta de cobro.");

                // Verificar que no exista una solicitud pendiente
                $chk = $this->ejecutarConsulta(
                    "SELECT id FROM exenciones_cobro WHERE vivienda_id = ? AND estado = 'Pendiente'",
                    [$vivienda_id]
                )->fetch();
                if ($chk) throw new \Exception("Ya existe una solicitud de exención pendiente para esta vivienda.");

                $this->ejecutarConsulta(
                    "INSERT INTO exenciones_cobro (vivienda_id, calle_id, barrio_id, tipo_exencion, descripcion, creado_por, estado)
                     VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')",
                    [$vivienda_id, $calle['calle_id'], $calle['barrio_id'], $tipo, $descripcion, $user['id']]
                );
                $mensaje_exito = "Solicitud de exención enviada al Encargado de Barrio.";
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 5b. Anular pago de una vivienda (deshacer)
        if ($action === 'anular_pago') {
            try {
                $cobro_id = (int)$_POST['cobro_id'];
                $calle = $this->obtenerCalleAsignada($user['id']);

                $cobro = $this->ejecutarConsulta(
                    "SELECT c.*, v.calle_id FROM cobros c 
                     JOIN viviendas v ON c.vivienda_id = v.id 
                     WHERE c.id = ? AND c.estado = 'Pagado'",
                    [$cobro_id]
                )->fetch(\PDO::FETCH_ASSOC);
                if (!$cobro) throw new \Exception("El cobro no existe o ya fue procesado.");
                if ($cobro['calle_id'] != $calle['calle_id']) throw new \Exception("Este cobro no pertenece a tu calle.");

                $lote_estado = $this->ejecutarConsulta(
                    "SELECT estado FROM lotes_calle WHERE calle_id=? AND mes=? AND anio=?",
                    [$calle['calle_id'], $cobro['mes'], $cobro['anio']]
                )->fetchColumn();
                if (in_array($lote_estado, ['Enviado', 'Aprobado'])) {
                    throw new \Exception("No puedes anular pagos. El lote ya fue cerrado.");
                }

                $motivo = trim($_POST['motivo_anulacion'] ?? 'Sin motivo');
                $this->ejecutarConsulta(
                    "UPDATE cobros SET estado='Anulado', observaciones=CONCAT(COALESCE(observaciones,''), ' | ANULADO: ', ?) WHERE id=?",
                    [$motivo, $cobro_id]
                );
                $mensaje_exito = "Pago anulado correctamente. Motivo: " . htmlspecialchars($motivo);
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 5c. Editar datos de un cobro (referencia, comprobante)
        if ($action === 'editar_cobro') {
            try {
                $cobro_id = (int)$_POST['cobro_id'];
                $referencia = trim($_POST['referencia_pago']);

                $calle = $this->obtenerCalleAsignada($user['id']);

                $cobro = $this->ejecutarConsulta(
                    "SELECT c.id, c.mes, c.anio FROM cobros c JOIN viviendas v ON c.vivienda_id = v.id 
                     WHERE c.id = ? AND v.calle_id = ? AND c.estado = 'Pagado'",
                    [$cobro_id, $calle['calle_id']]
                )->fetch(PDO::FETCH_ASSOC);
                if (!$cobro) throw new \Exception("El cobro no existe o no pertenece a tu calle.");

                $lote_est = $this->ejecutarConsulta(
                    "SELECT estado FROM lotes_calle WHERE calle_id=? AND mes=? AND anio=?",
                    [$calle['calle_id'], $cobro['mes'], $cobro['anio']]
                )->fetchColumn();
                if (in_array($lote_est, ['Enviado', 'Aprobado'])) {
                    throw new \Exception("No puedes editar cobros. El lote ya fue cerrado.");
                }

                $updateSQL = "UPDATE cobros SET referencia_pago = ?";
                $updateParams = [$referencia];

                if (!empty($_FILES['comprobante_pago']['name']) && $_FILES['comprobante_pago']['error'] === UPLOAD_ERR_OK) {
                    $ruta = $this->guardarArchivoPago($_FILES['comprobante_pago'], 'comprobantes');
                    if ($ruta) {
                        $updateSQL .= ", comprobante_calle = ?";
                        $updateParams[] = $ruta;
                    }
                }

                $updateSQL .= " WHERE id = ?";
                $updateParams[] = $cobro_id;

                $this->ejecutarConsulta($updateSQL, $updateParams);
                $mensaje_exito = "Cobro actualizado correctamente.";
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 6. Enviar Lote de Calle al Encargado de Barrio (con comprobante global)
        if ($action === 'enviar_lote_calle') {
            try {
                $mes = (int)$_POST['mes'];
                $anio = (int)$_POST['anio'];
                $referenciaLote = trim($_POST['referencia_lote'] ?? '');
                
                $calle = $this->obtenerCalleAsignada($user['id']);
                $calle_id = $calle['calle_id'];
                $barrio_id = $calle['barrio_id'];

                $total_casas = (int)$this->ejecutarConsulta("SELECT COUNT(*) FROM viviendas WHERE calle_id = ? AND estado_servicio='Activo' AND exento_cobro = 0", [$calle_id])->fetchColumn();
                $cuota = (float)$this->ejecutarConsulta("SELECT cuota_mensual FROM configuraciones_barrio WHERE barrio_id = ?", [$barrio_id])->fetchColumn() ?: 5.00;
                $monto_esperado = $total_casas * $cuota;

                // Crear cobros Pendientes para viviendas que aun no tienen cobro este mes
                $viviendasSinCobro = $this->ejecutarConsulta(
                    "SELECT v.id FROM viviendas v 
                     WHERE v.calle_id = ? AND v.estado_servicio = 'Activo' AND v.exento_cobro = 0
                     AND v.id NOT IN (SELECT vivienda_id FROM cobros WHERE mes = ? AND anio = ? AND estado != 'Anulado')
                     AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')",
                    [$calle_id, $mes, $anio]
                )->fetchAll(\PDO::FETCH_COLUMN);

                $fechaEmision = date('Y-m-d');
                $fechaVenc = date('Y-m-d', strtotime('+15 days'));
                foreach ($viviendasSinCobro as $vid) {
                    $this->ejecutarConsulta(
                        "INSERT INTO cobros (vivienda_id, mes, anio, monto, fecha_emision, fecha_vencimiento, estado, tipo_cobro)
                         VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', 'Servicio')",
                        [$vid, $mes, $anio, $cuota, $fechaEmision, $fechaVenc]
                    );
                }

                $casas_pagadas = (int)$this->ejecutarConsulta(
                    "SELECT COUNT(DISTINCT vivienda_id) FROM cobros c 
                     JOIN viviendas v ON c.vivienda_id = v.id 
                     WHERE v.calle_id = ? AND c.mes = ? AND c.anio = ? AND c.estado = 'Pagado'",
                    [$calle_id, $mes, $anio]
                )->fetchColumn();

                $monto_recolectado = (float)$this->ejecutarConsulta(
                    "SELECT COALESCE(SUM(c.monto),0) FROM cobros c 
                     JOIN viviendas v ON c.vivienda_id = v.id 
                     WHERE v.calle_id = ? AND c.mes = ? AND c.anio = ? AND c.estado = 'Pagado'",
                    [$calle_id, $mes, $anio]
                )->fetchColumn();

                $casas_morosas = $total_casas - $casas_pagadas;
                $alerta = ($monto_recolectado < $monto_esperado) ? 1 : 0;

                // Manejar comprobante global del lote
                $comprobanteLote = null;
                if (isset($_FILES['comprobante_lote']) && $_FILES['comprobante_lote']['error'] === UPLOAD_ERR_OK) {
                    $comprobanteLote = $this->guardarArchivoPago($_FILES['comprobante_lote'], 'comprobantes');
                }

                $this->ejecutarConsulta(
                    "INSERT INTO lotes_calle (periodo_mes, periodo_anio, mes, anio, calle_id, barrio_id, encargado_calle_id, monto_esperado, monto_recolectado, total_casas, casas_pagadas, casas_morosas, estado, fecha_envio, comprobante_lote, referencia_lote)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Enviado', NOW(), ?, ?)
                     ON DUPLICATE KEY UPDATE monto_recolectado=VALUES(monto_recolectado), casas_pagadas=VALUES(casas_pagadas), casas_morosas=VALUES(casas_morosas), estado='Enviado', fecha_envio=NOW(), comprobante_lote=VALUES(comprobante_lote), referencia_lote=VALUES(referencia_lote)",
                    [$mes, $anio, $mes, $anio, $calle_id, $barrio_id, $user['id'], $monto_esperado, $monto_recolectado, $total_casas, $casas_pagadas, $casas_morosas, $comprobanteLote, $referenciaLote]
                );

                $lote_id = $this->ejecutarConsulta("SELECT id FROM lotes_calle WHERE calle_id=? AND periodo_mes=? AND periodo_anio=?", [$calle_id, $mes, $anio])->fetchColumn();
                
                // Vincular TODOS los cobros (pagados y pendientes) al lote
                $this->ejecutarConsulta(
                    "UPDATE cobros c JOIN viviendas v ON c.vivienda_id = v.id 
                     SET c.lote_calle_id = ? 
                     WHERE v.calle_id = ? AND c.mes = ? AND c.anio = ? AND c.estado != 'Anulado'", 
                    [$lote_id, $calle_id, $mes, $anio]
                );

                $mensaje_exito = "Lote mensual enviado correctamente al Encargado de Barrio.";
            } catch (\Exception $e) {
                $mensaje_error = "Error al cerrar el lote: " . $e->getMessage();
            }
        }
    }

    private function obtenerCalleAsignada($usuario_id): array {
        $stmt = $this->ejecutarConsulta(
            "SELECT dc.calle_id, c.barrio_id FROM detalles_encargado_calle dc 
             JOIN calles c ON dc.calle_id = c.id 
             WHERE dc.usuario_id = ?",
            [$usuario_id]
        );
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$data) throw new \Exception("No tienes ninguna calle asignada en el sistema.");
        return $data;
    }

    private function calcularDeuda(int $vivienda_id): array {
        $deudaStmt = $this->ejecutarConsulta(
            "SELECT SUM(monto) as total, GROUP_CONCAT(CONCAT('Mes: ',mes,'/',anio) SEPARATOR ', ') as resumen
             FROM cobros WHERE vivienda_id = ? AND estado != 'Pagado'",
            [$vivienda_id]
        );
        $d = $deudaStmt->fetch(\PDO::FETCH_ASSOC);
        $base = $d['total'] ?? 0;
        $resumen = $d['resumen'] ?? 'Sin deudas previas';
        return [$base, $resumen];
    }
}