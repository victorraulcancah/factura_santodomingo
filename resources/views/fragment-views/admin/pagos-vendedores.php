<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Planilla de Vendedores</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Administracion</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pagos y Comisiones</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Filtros de Pago</h4>
                <div class="row">
                    <div class="col-md-3">
                        <label>Vendedor</label>
                        <select class="form-select" id="filtro_vendedor">
                            <option value="">Seleccione Vendedor</option>
                            <!-- Llenado por AJAX -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Periodo</label>
                        <select class="form-select" id="filtro_periodo">
                            <option value="mes">Mensual</option>
                            <option value="quincena">Quincenal</option>
                            <option value="semana">Semanal</option>
                            <option value="rango">Rango Manual</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Desde</label>
                        <input type="date" class="form-control" id="fecha_desde" value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-md-2">
                        <label>Hasta</label>
                        <input type="date" class="form-control" id="fecha_hasta" value="<?= date('Y-m-t') ?>">
                    </div>
                    <div class="col-md-2 mt-4 text-end">
                        <button class="btn btn-secondary" id="btn_configurar_vendedor" style="display:none;" title="Configurar Sueldos/Comisiones"><i class="fa fa-cog"></i> Configurar</button>
                        <button class="btn btn-primary" id="btn_calcular_planilla"><i class="fa fa-calculator"></i> Calcular</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Configuracion Esquema de Pago -->
<div class="modal fade" id="modalConfigurar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configurar Esquema de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formConfigurar">
                    <input type="hidden" id="conf_usuario_id" name="usuario_id">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Tipo de Sueldo Base</label>
                            <select class="form-select" id="conf_tipo_sueldo" name="tipo_sueldo">
                                <option value="1">Sueldo Fijo</option>
                                <option value="2">Sueldo por Comision (%)</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mt-2" id="conf_campo_fijo">
                            <label>Monto Sueldo Fijo (S/)</label>
                            <input type="number" step="0.01" class="form-control" id="conf_monto_sueldo_fijo" name="monto_sueldo_fijo" value="0.00">
                            <small class="form-text text-muted">Se paga este monto sin importar las ventas</small>
                        </div>
                        <div class="col-md-6 form-group mt-2" id="conf_campo_comision" style="display:none;">
                            <label>Comision Base (%)</label>
                            <input type="number" step="0.01" class="form-control" id="conf_porcentaje_sueldo_comision" name="porcentaje_sueldo_comision" value="0.00">
                            <small class="form-text text-muted">Este % se aplica a las ventas del periodo</small>
                        </div>
                        <div class="col-md-12 mt-3"><hr></div>
                        <div class="col-md-6 form-group">
                            <label>Meta de Ventas (S/)</label>
                            <input type="number" step="0.01" class="form-control" id="conf_meta_ventas" name="meta_ventas" value="0.00">
                            <small class="form-text text-muted">0 = sin meta, no aplica bono</small>
                        </div>
                        <div class="col-md-6 form-group" id="conf_campo_bono" style="display:none;">
                            <label>Bono por Meta (%)</label>
                            <input type="number" step="0.01" class="form-control" id="conf_porcentaje_comision_meta" name="porcentaje_comision_meta" value="0.00">
                            <small class="form-text text-muted">Se aplica sobre el EXCEDENTE de la meta</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn_guardar_config">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<div class="row" id="resultados_planilla" style="display:none;">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="font-size-16 text-uppercase text-white-50">Sueldo Base</h5>
                    <h4 class="fw-medium font-size-24" id="res_sueldo_base">S/ 0.00</h4>
                    <div class="mini-stat-label bg-success">
                        <p class="mb-0" id="lbl_tipo_sueldo">Fijo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="font-size-16 text-uppercase text-white-50">Ventas Totales</h5>
                    <h4 class="fw-medium font-size-24" id="res_ventas_totales">S/ 0.00</h4>
                    <div class="mini-stat-label bg-info">
                        <p class="mb-0">Rango</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-primary text-white">
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="font-size-16 text-uppercase text-white-50">Bono por Meta</h5>
                    <h4 class="fw-medium font-size-24" id="res_bono_meta">S/ 0.00</h4>
                    <div class="mini-stat-label bg-warning" id="lbl_estado_meta">
                        <p class="mb-0">No aplica</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-danger text-white">
            <div class="card-body">
                <div class="mb-4">
                    <h5 class="font-size-16 text-uppercase text-white-50">Total a Pagar</h5>
                    <h4 class="fw-medium font-size-24" id="res_total_pagar">S/ 0.00</h4>
                    <div class="mini-stat-label bg-dark">
                        <p class="mb-0">Liquidar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mt-3 text-end">
        <button class="btn btn-success btn-lg" id="btn_agregar_planilla"><i class="fa fa-check"></i> Agregar a Planilla (Aprobar Pago)</button>
    </div>
    <div class="col-12" id="aviso_pagos_previos"></div>
    <div class="col-12" id="detalle_cotizaciones"></div>
</div>

<!-- Historial de Pagos Aprobados -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Historial de Pagos</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="tabla_historial_pagos">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendedor</th>
                                <th>Periodo</th>
                                <th>Dias</th>
                                <th>Sueldo Base</th>
                                <th>Bono Meta</th>
                                <th>Total Pagado</th>
                                <th>Ventas Periodo</th>
                                <th>Aprobado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function cargarHistorial() {
        _ajax("/ajs/planilla/historial", "POST", {}, function(resp) {
            if (!resp || !resp.res) return;
            let tbody = $("#tabla_historial_pagos tbody").empty();
            (resp.data || []).forEach(function(p) {
                tbody.append(`<tr>
                    <td>${p.id_pago}</td>
                    <td>${p.nombres ?? ''} ${p.apellidos ?? ''}</td>
                    <td>${p.periodo_desde} a ${p.periodo_hasta}</td>
                    <td>${p.dias_periodo}</td>
                    <td class="text-end">S/ ${parseFloat(p.sueldo_base).toFixed(2)}</td>
                    <td class="text-end">S/ ${parseFloat(p.bono_meta).toFixed(2)}</td>
                    <td class="text-end"><strong>S/ ${parseFloat(p.total_pagado).toFixed(2)}</strong></td>
                    <td class="text-end">S/ ${parseFloat(p.total_ventas_periodo).toFixed(2)}</td>
                    <td>${p.fecha_aprobacion ?? '-'}</td>
                    <td><button class="btn btn-danger btn-sm" onclick="eliminarPago(${p.id_pago})"><i class="fa fa-trash"></i></button></td>
                </tr>`);
            });
        });
    }

    function eliminarPago(id) {
        Swal.fire({
            title: "Eliminar pago?",
            text: "El registro se desactivara permanentemente.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Si, eliminar"
        }).then((result) => {
            if (!result.isConfirmed) return;
            _ajax("/ajs/planilla/eliminar", "POST", { id_pago: id }, function(resp) {
                if (resp.res) {
                    Swal.fire("Eliminado", "Pago eliminado correctamente.", "success");
                    cargarHistorial();
                } else {
                    Swal.fire("Error", resp.error ?? "No se pudo eliminar", "error");
                }
            });
        });
    }

    $(document).ready(function() {
        cargarHistorial();
        // Cargar vendedores
        $.post(_URL + "/ajs/usuarios/render", function(resp) {
            let usuarios = JSON.parse(resp);
            usuarios.forEach(function(u) {
                if (u.id_rol == 2) { // Vendedores
                    $("#filtro_vendedor").append(`<option value="${u.usuario_id}">${u.nombres} ${u.apellidos}</option>`);
                }
            });
        });

        $("#filtro_periodo").change(function() {
            // Logica para auto cambiar fechas segun periodo
            let hoy = new Date();
            let desde = "";
            let hasta = "";
            if ($(this).val() == 'mes') {
                desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                hasta = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];
                $("#fecha_desde").val(desde);
                $("#fecha_hasta").val(hasta);
            }
        });

        $("#filtro_vendedor").change(function() {
            if ($(this).val()) {
                $("#btn_configurar_vendedor").show();
            } else {
                $("#btn_configurar_vendedor").hide();
            }
        });

        $("#btn_configurar_vendedor").click(function() {
            let id = $("#filtro_vendedor").val();
            if (!id) return;
            $.post(_URL + "/ajs/usuarios/get", { id: id }, function(resp) {
                let user;
                try {
                    let parsed = JSON.parse(resp);
                    user = Array.isArray(parsed) ? parsed[0] : parsed;
                } catch(e) {
                    user = {};
                }

                $("#conf_usuario_id").val(id);
                $("#conf_tipo_sueldo").val(user.tipo_sueldo || 1);
                $("#conf_monto_sueldo_fijo").val(user.monto_sueldo_fijo || 0);
                $("#conf_porcentaje_sueldo_comision").val(user.porcentaje_sueldo_comision || 0);
                $("#conf_meta_ventas").val(user.meta_ventas || 0);
                $("#conf_porcentaje_comision_meta").val(user.porcentaje_comision_meta || 0);
                aplicarVisibilidadConfig();
                $("#modalConfigurar").modal('show');
            });
        });

        // Toggle de campos segun tipo de sueldo y meta
        function aplicarVisibilidadConfig() {
            const tipo = $("#conf_tipo_sueldo").val();
            if (tipo == "1") {
                $("#conf_campo_fijo").show();
                $("#conf_campo_comision").hide();
                $("#conf_porcentaje_sueldo_comision").val(0);
            } else {
                $("#conf_campo_fijo").hide();
                $("#conf_campo_comision").show();
                $("#conf_monto_sueldo_fijo").val(0);
            }
            const meta = parseFloat($("#conf_meta_ventas").val() || 0);
            if (meta > 0) {
                $("#conf_campo_bono").show();
            } else {
                $("#conf_campo_bono").hide();
                $("#conf_porcentaje_comision_meta").val(0);
            }
        }
        $("#conf_tipo_sueldo").on("change", aplicarVisibilidadConfig);
        $("#conf_meta_ventas").on("input change", aplicarVisibilidadConfig);

        $("#btn_guardar_config").click(function() {
            let data = $("#formConfigurar").serialize();
            // Actualizar usando la misma ruta de usuarios (pero solo mandara los campos del form + usuario_id)
            $.post(_URL + "/ajs/usuarios/update", data, function(resp) {
                try {
                    let r = JSON.parse(resp);
                    // Si el update es exitoso
                    $("#modalConfigurar").modal('hide');
                    Swal.fire("Exito", "Esquema de pago guardado correctamente.", "success");
                    // Recalcular si ya estaban visibles los resultados
                    if ($("#resultados_planilla").is(":visible")) {
                        $("#btn_calcular_planilla").click();
                    }
                } catch (e) {
                    Swal.fire("Error", "No se pudo guardar la configuracion", "error");
                }
            });
        });

        // Calculo REAL desde el backend (PlanillaController@calcular)
        $("#btn_calcular_planilla").click(function() {
            let id = $("#filtro_vendedor").val();
            if (!id) {
                alertAdvertencia("Seleccione un vendedor");
                return;
            }
            const desde = $("#fecha_desde").val();
            const hasta = $("#fecha_hasta").val();
            if (!desde || !hasta) {
                alertAdvertencia("Indica el rango de fechas");
                return;
            }

            $("#loader-menor").show();
            _ajax("/ajs/planilla/calcular", "POST",
                { id_usuario: id, desde: desde, hasta: hasta },
                function(resp) {
                    $("#loader-menor").hide();
                    if (!resp || !resp.res) {
                        alertAdvertencia(resp && resp.error ? resp.error : "No se pudo calcular");
                        return;
                    }

                    const cfg = resp.configuracion;
                    const calc = resp.calculo;
                    const per = resp.periodo;
                    const tipoSueldo = parseInt(cfg.tipo_sueldo);
                    const lblSueldo = tipoSueldo === 1
                        ? `Fijo (${per.dias} dias)`
                        : `Comision ${cfg.porcentaje_sueldo_comision}%`;
                    const lblMeta = cfg.meta_ventas <= 0
                        ? "Sin meta"
                        : (calc.meta_cumplida
                            ? `Meta cumplida (excede S/${calc.excedente_meta.toFixed(2)})`
                            : `No llego a meta S/${cfg.meta_ventas}`);

                    $("#lbl_tipo_sueldo").text(lblSueldo);
                    $("#res_sueldo_base").text("S/ " + calc.sueldo_base.toFixed(2));
                    $("#res_ventas_totales").text("S/ " + calc.total_ventas_periodo.toFixed(2));
                    $("#res_bono_meta").text("S/ " + calc.bono_meta.toFixed(2));
                    $("#lbl_estado_meta").find('p').text(lblMeta);
                    $("#res_total_pagar").text("S/ " + calc.total_pagar.toFixed(2));

                    // Guardar contexto para aprobar
                    $("#btn_agregar_planilla")
                        .data("id_usuario", id)
                        .data("desde", desde)
                        .data("hasta", hasta)
                        .data("total", calc.total_pagar);

                    // Mostrar avisos de pagos previos solapados (anulaciones post-pago)
                    let avisoHtml = "";
                    if ((resp.pagos_previos_solapados || []).length > 0) {
                        avisoHtml = `<div class="alert alert-warning mt-3">
                            <strong>? Atencion:</strong> ya hay pagos aprobados que solapan este periodo:<ul>`;
                        resp.pagos_previos_solapados.forEach(p => {
                            const diffTxt = p.diferencia === 0
                                ? "Sin cambios"
                                : (p.diferencia < 0
                                    ? `Se anularon cotizaciones por S/${Math.abs(p.diferencia).toFixed(2)} despues del pago`
                                    : `Se agregaron cotizaciones por S/${p.diferencia.toFixed(2)} despues del pago`);
                            avisoHtml += `<li>Pago #${p.id_pago} (${p.periodo_desde} a ${p.periodo_hasta}) - total pagado S/${p.total_pagado.toFixed(2)} - ${diffTxt}</li>`;
                        });
                        avisoHtml += `</ul></div>`;
                    }
                    $("#aviso_pagos_previos").html(avisoHtml);

                    // Mostrar cotizaciones incluidas
                    let cotHtml = "";
                    if ((resp.cotizaciones || []).length > 0) {
                        cotHtml = `<div class="card mt-3"><div class="card-body">
                            <h5>Cotizaciones incluidas (${resp.cotizaciones.length})</h5>
                            <table class="table table-sm">
                                <thead><tr><th>#</th><th>Fecha</th><th class="text-end">Total</th></tr></thead>
                                <tbody>`;
                        resp.cotizaciones.forEach(c => {
                            cotHtml += `<tr><td>${c.numero}</td><td>${c.fecha}</td><td class="text-end">S/ ${c.total.toFixed(2)}</td></tr>`;
                        });
                        cotHtml += `</tbody></table></div></div>`;
                    } else {
                        cotHtml = `<div class="alert alert-info mt-3">No hay cotizaciones del vendedor en este periodo.</div>`;
                    }
                    $("#detalle_cotizaciones").html(cotHtml);

                    $("#resultados_planilla").show();
                }
            );
        });

        // Aprobar pago: graba en planilla_pagos via PlanillaController@aprobar
        $("#btn_agregar_planilla").click(function() {
            const id = $(this).data("id_usuario");
            const desde = $(this).data("desde");
            const hasta = $(this).data("hasta");
            const total = $(this).data("total");
            if (!id || !desde || !hasta) {
                alertAdvertencia("Calcula primero");
                return;
            }
            Swal.fire({
                title: "Aprobar Pago de S/ " + parseFloat(total).toFixed(2) + "?",
                text: "Se registrara el pago en planilla (snapshot del calculo actual).",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si, registrar"
            }).then((result) => {
                if (!result.isConfirmed) return;
                _ajax("/ajs/planilla/aprobar", "POST",
                    { id_usuario: id, desde: desde, hasta: hasta },
                    function(resp) {
                        if (resp && resp.res) {
                            Swal.fire("Exito", "Pago #" + resp.id_pago + " registrado correctamente.", "success");
                            $("#resultados_planilla").hide();
                            $("#detalle_cotizaciones").html("");
                            $("#aviso_pagos_previos").html("");
                            cargarHistorial();
                        } else {
                            Swal.fire("Error", resp && resp.error ? resp.error : "No se pudo registrar", "error");
                        }
                    }
                );
            });
        });
    });
</script>
