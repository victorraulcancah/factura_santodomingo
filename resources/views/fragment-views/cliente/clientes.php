<?php

require_once "app/models/Cliente.php";

$c_cliente = new Cliente();
$c_cliente->setIdEmpresa($_SESSION['id_empresa']);

?>
<div class="page-title-box" style="padding: 12px 0;">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h6 class="page-title text-center">DATOS DE CLIENTES</h6>

        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#agregarModal" class="btn btn-primary"><i class="fa fa-plus"></i> Agregar</button>
                        <!--   <button type="button" data-bs-toggle="modal" data-bs-target="#editarModal" class="btn btn-warning">Editar</button> -->
                    </div>

                    <div class="col-md-6 text-end">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#importarModal" class="btn btn-success"><i class="fa fa-file-excel"></i> Importar</button>
                        <a href="<?= _URL ?>/ajs/clientes/exportar" target="_blank" class="btn btn-success"><i class="fa fa-file-excel"></i> Exportar</a>
                        <!-- <button class="btn btn-success"><i class="fa fa-file-excel"></i> Importar</button> -->
                    </div>
                </div>
            </div>
            <div id="conte-vue-modals">
                <div class="card-body">
                    <!-- MODAL CONFIRMAR DATOS -->
                    <div class="modal fade" id="modal-lista-clientes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog  modal-dialog-scrollable modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">Lista de clientes</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-sm table-bordered text-center" id="tablaImportarCliente">
                                        <thead>
                                            <tr>
                                                <th>Documento</th>
                                                <th>Nombres/Razon Social</th>
                                                <th>Direccion</th>
                                                <th>Direccion Llegada</th>
                                                <th>Telefono 1</th>
                                                <th>Telefono 2</th>
                                                <th>Email</th>
                                                <th>Departamento</th>
                                                <th>Provincia</th>
                                                <th>Distrito</th>
                                                <th>Fecha Nacimiento</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyImportar">
                                           <!--  <tr id="trImportar"></tr> -->
                                            <tr id="trImportar" v-for="(item,index) in listaClientes">
                                             <!--  -->
                                                <td>{{item.documento}}</td>
                                                <td> {{item.datos}}</td>
                                                <td>{{item.direccion}}</td>
                                                <td>{{item.direccion2}}</td>
                                                <td>{{item.telefono}}</td>
                                                <td>{{item.telefono2}}</td>
                                                <td>{{item.email}}</td>
                                                <td>{{item.departamento}}</td>
                                                <td>{{item.provincia}}</td>
                                                <td>{{item.distrito}}</td>
                                                <td>{{item.fecha_nacimiento}}</td>
                                               
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                   <!--  <button id="agregarClientesImport" type="button" class="btn btn-primary">Guardar</button> -->
                                    <button @click="agregarListaImport" type="button" class="btn btn-primary">Guardar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL DE IMPORTAR XLS -->
                    <div class="modal fade" id="importarModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Importar Cliente con EXCEL</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form enctype='multipart/form-data'>
                                        <div class="mb-3">
                                            <p>Descargue el modelo en <span class="fw-bold">EXCEL</span> para importar, no
                                                modifique los campos en el archivo, <span class="fw-bold">click para
                                                    descargar</span> <a href="<?= URL::to("public/templateExcelClientes.xlsx") ?>?v=<?= time() ?>">template.xlsx</a></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="col-form-label">Importar Excel:</label>

                                        </div>
                                        <input type="file" id="nuevoExcel" name="nuevoExcel" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL DE AGREGAR CLIENTE -->
                    <div class="modal fade" id="agregarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Agregar</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="frmClientesAgregar">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label>DNI<span style="color: red;"> (*)</span> </label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" required id="documentoAgregar" name="documentoAgregar">
                                                    <div class="input-group-prepend">
                                                        <button id="btnBuscarInfo" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8 form-group">
                                                <label for="datosAgregar">Nombre/Razon Social <span style="color: red;"> (*)</span></label>
                                                <input type="text" class="form-control" id="datosAgregar" name="datosAgregar">
                                            </div>


                                            <div class="col-md-6 mt-3">
                                                <label for="direccionAgregar">Direccion</label>
                                                <input type="text" class="form-control" id="direccionAgregar" name="direccionAgregar">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="direccionAgregar2">Direccion de Llegada</label>
                                                <input type="text" class="form-control" id="direccionAgregar2" name="direccionAgregar2">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoAgregar">Telefono</label>
                                                <input type="number" class="form-control" id="telefonoAgregar" name="telefonoAgregar">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoAgregar2">Telefono 2</label>
                                                <input type="number" class="form-control" id="telefonoAgregar2" name="telefonoAgregar2">
                                            </div>
                                            
                                            <div class="col-md-4 mt-3">
                                                <label for="departamentoAgregar">Departamento</label>
                                                <select class="form-control" id="departamentoAgregar" name="departamentoAgregar">
                                                    <option value="">-- Seleccionar --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="provinciaAgregar">Provincia</label>
                                                <select class="form-control" id="provinciaAgregar" name="provinciaAgregar">
                                                    <option value="">-- Seleccionar --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="distritoAgregar">Distrito</label>
                                                <select class="form-control" id="distritoAgregar" name="distritoAgregar">
                                                    <option value="">-- Seleccionar --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="fecha_nacimientoAgregar">F. Nacimiento</label>
                                                <input type="date" class="form-control" id="fecha_nacimientoAgregar" name="fecha_nacimientoAgregar">
                                            </div>


                                            <div class="col-md-8 mt-3">
                                                <label for="direccion">Email</label>
                                                <input required type="text" class="form-control" id="direccion" name="direccion">
                                            </div>

                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="nuevoCliente" type="button" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- EDITAR MODAL -->
                    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Editar</h5>

                                </div>
                                <div class="modal-body">
                                    <form id="clientesEditar">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label>DNI<span style="color: red;"> (*)</span> </label>
                                                <div class="input-group">
                                                    <input type="hidden" name="idCliente" id="idCliente" value="">
                                                    <input type="hidden" name="trid" id="trid" value="">
                                                    <input type="text" class="form-control" id="documentoEditar" name="documentoEditar">
                                                    <div class="input-group-prepend">
                                                        <button id="btnBuscarInfoEditar" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--    <div class="col-md-4 mt-3">
                                            <input type="hidden" name="idCliente" id="idCliente" value="">
                                            <input type="hidden" name="trid" id="trid" value="">
                                            <label for="documentoEditar" class="col-form-label">Documento <span style="color: red;">(*)</span></label>
                                            <input type="text" class="form-control" id="documentoEditar" name="documentoEditar">
                                        </div> -->
                                            <div class="col-md-8 form-group">
                                                <label for="datosAgregar">Nombre/Razon Social <span style="color: red;"> (*)</span></label>
                                                <input type="text" class="form-control" id="datosEditar" name="datosEditar">
                                            </div>


                                            <div class="col-md-6 mt-3">
                                                <label for="direccionEditar" class="col-form-label">Direccion</label>
                                                <input type="text" class="form-control" id="direccionEditar" name="direccionEditar">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="direccionEditar2" class="col-form-label">Direccion 2</label>
                                                <input type="text" class="form-control" id="direccionEditar2" name="direccionEditar2">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoEditar" class="col-form-label">Telefono</label>
                                                <input type="number" class="form-control" id="telefonoEditar" name="telefonoEditar">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoEditar2" class="col-form-label">Telefono 2</label>
                                                <input type="number" class="form-control" id="telefonoEditar2" name="telefonoEditar2">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="departamentoEditar" class="col-form-label">Departamento</label>
                                                <select class="form-control" id="departamentoEditar" name="departamentoEditar">
                                                    <option value="">-- Seleccionar --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="provinciaEditar" class="col-form-label">Provincia</label>
                                                <select class="form-control" id="provinciaEditar" name="provinciaEditar">
                                                    <option value="">-- Seleccionar --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="distritoEditar" class="col-form-label">Distrito</label>
                                                <select class="form-control" id="distritoEditar" name="distritoEditar">
                                                    <option value="">-- Seleccionar --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="fecha_nacimientoEditar" class="col-form-label">F. Nacimiento</label>
                                                <input type="date" class="form-control" id="fecha_nacimientoEditar" name="fecha_nacimientoEditar">
                                            </div>

                                            <div class="col-md-8 mt-3">
                                                <label for="emailEditar" class="col-form-label">Email</label>
                                                <input required type="text" class="form-control" id="emailEditar" name="emailEditar">
                                            </div>

                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="updateCliente" type="button" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-title-desc">
                        <div class="table-responsive">
                            <table id="tabla_clientes" class="table table-bordered dt-responsive nowrap text-center table-sm dataTable no-footer">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Documento</th>
                                        <th>Nombre/Razon Social</th>
                                        <th>Direccion</th>
                                        <th>Telefono</th>
                                        <th>Email</th>
                                        <th>Departamento</th>
                                        <th>Provincia</th>
                                        <th>Distrito</th>
                                        <th>F. Nacimiento</th>
                                        <th>S/ Venta</th>
                                        <th>Ultima Venta</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
    $(document).ready(function() {

        const app = new Vue({
            el: "#conte-vue-modals",
            data: {
                listaClientes: []
            },
            methods: {
                 agregarListaImport() {

                    if (this.listaClientes.length > 0) {
                      
                        _ajax("/ajs/clientes/add/por/lista", "POST", {
                                lista: JSON.stringify(this.listaClientes)
                            },
                            function(resp) {
                                console.log(resp);
                                /* return */
                                if (resp.res) {
                                    alertExito("Agregado")
                                        .then(function() {
                                            location.reload()
                                        })
                                } else {
                                    alertAdvertencia("No se pudo Agregar")
                                }
                            }
                        )
                    } else {
                        alertAdvertencia("La lista esta vacia")
                    }
                }, 


            }
        })

        tabla_clientes = $("#tabla_clientes").DataTable({
            paging: true,
            bFilter: true,
            ordering: true,
            searching: true,
            destroy: true,
            ajax: {
                url: _URL + "/ajs/clientes/render",
                method: "POST", //usamos el metodo POST
                dataSrc: "",
            },
            language: {
                url: "ServerSide/Spanish.json",
            },
            columns: [{
                    data: "id_cliente",
                    class: "text-center",
                },
                {
                    data: "documento",
                    class: "text-center",
                },
                {
                    data: "datos",
                    class: "text-center",
                },
                {
                    data: "direccion",
                    class: "text-center",
                },
                {
                    data: "telefono",
                    class: "text-center",
                },
                {
                    data: "email",
                    class: "text-center",
                },
                {
                    data: "departamento",
                    class: "text-center",
                },
                {
                    data: "provincia",
                    class: "text-center",
                },
                {
                    data: "distrito",
                    class: "text-center",
                },
                {
                    data: "fecha_nacimiento",
                    class: "text-center",
                },
                {
                    data: "total_venta",
                    class: "text-center",
                },
                {
                    data: "ultima_venta",
                    class: "text-center",
                },
                {

                    /* href="' + _URL + '/files/facturacion/xml/ */
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
            <div class="btn-group btn-sm"><button  data-id="${Number(row.id_cliente)}" class="btn btn-sm btn-warning btnEditar"
            ><i class="fa fa-edit"></i> </button>
            <button btn-sm  data-id="${Number(row.id_cliente)}" class="btn btn-sm  btn-danger btnBorrar"><i class="fa fa-trash"></i> </button>
            <a href="${_URL}/reporte/cliente/${Number(row.id_cliente)}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file"></i></a>
            </div></div>`;
                    },
                },
            ],
        });
        $("#nuevoCliente").click(function() {
            $("#loader-menor").show();
            let data = $("#frmClientesAgregar").serializeArray();
            data.forEach(function(item) {
                if (item.name == "departamentoAgregar") item.value = selectedText("#departamentoAgregar");
                if (item.name == "provinciaAgregar") item.value = selectedText("#provinciaAgregar");
                if (item.name == "distritoAgregar") item.value = selectedText("#distritoAgregar");
            });
            $.ajax({
                type: "POST",
                url: _URL + "/ajs/clientes/add",
                data: data,
                success: function(resp) {
                    $("#loader-menor").hide();
                    let data = JSON.parse(resp);
                    if (typeof data === "object") {
                        tabla_clientes.ajax.reload(null, false);
                        Swal.fire("Buen trabajo", "Registro Exitoso", "success");
                        $("#agregarModal").modal("hide");
                        $("body").removeClass("modal-open");
                        $("#frmClientesAgregar").trigger("reset");
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: JSON.parse(resp),
                        });
                    }
                },
            });
        });
  /*       $("#agregarClientesImport").click(function() {
            var tr = $("#trImportar");
            console.log('hola');
            $(tr).each(function() {
                var currentRow = $(this);

                var col1_value = currentRow.find("td:eq(0)").text();
                var col2_value = currentRow.find("td:eq(1)").text();
                var col3_value = currentRow.find("td:eq(2)").text();
                var col4_value = currentRow.find("td:eq(3)").text();
                var col5_value = currentRow.find("td:eq(4)").text();
                var col6_value = currentRow.find("td:eq(5)").text();
                var col7_value = currentRow.find("td:eq(6)").text();
                let datos = {
                    documentoAgregar: col1_value,
                    datosAgregar: col2_value,
                    direccionAgregar: col3_value,
                    direccionAgregar2: col4_value,
                    telefonoAgregar: col5_value,
                    telefonoAgregar2: col6_value,
                    direccion: col7_value,
                };
                $.ajax({
                    type: "POST",
                    url: _URL + "/ajs/clientes/add",
                    data: datos,
                    success: function(resp) {
                        console.log(resp);
                        $("#loader-menor").hide();
                        tabla_clientes.ajax.reload(null, false);
                        Swal.fire("Buen trabajo", "Registro Exitoso", "success");
                        $("#modal-lista-clientes").modal("hide");
                        $("body").removeClass("modal-open");
                    },
                });
            });
        }); */
        $("#tabla_clientes").on("click", ".btnEditar ", function(event) {
            $("#loader-menor").show();
            var table = $("#tabla_clientes").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            $("#editarModal").modal("show");
            $("#editarModal")
                .find(".modal-title")
                .text("Editar cliente N°" + id);
            $.ajax({
                url: _URL + "/ajs/clientes/getOne",
                data: {
                    id: id,
                },
                type: "post",
                success: function(data) {
                    $("#loader-menor").hide();
                    let json = JSON.parse(data);
                    let datos = json[0];
                    console.log(datos);
                    $("#documentoEditar").val(datos.documento);
                    $("#datosEditar").val(datos.datos);
                    $("#direccionEditar").val(datos.direccion);
                    $("#direccionEditar2").val(datos.direccion2);
                    var depCode = getCodeByText("#departamentoAgregar", datos.departamento) || datos.departamento;
                    selectByText("#departamentoEditar", datos.departamento);
                    $("#provinciaEditar").html('<option value="">-- Seleccionar --</option>');
                    $("#distritoEditar").html('<option value="">-- Seleccionar --</option>');
                    if (depCode) {
                        cargarProvincias(depCode, "#provinciaEditar", function() {
                            selectByText("#provinciaEditar", datos.provincia);
                            var provCode = getCodeByText("#provinciaEditar", datos.provincia) || datos.provincia;
                            if (provCode) {
                                cargarDistritos(depCode, provCode, "#distritoEditar", function() {
                                    selectByText("#distritoEditar", datos.distrito);
                                });
                            }
                        });
                    }
                    $("#fecha_nacimientoEditar").val(datos.fecha_nacimiento);
                    $("#telefonoEditar").val(datos.telefono);
                    $("#telefonoEditar2").val(datos.telefono2);
                    $("#emailEditar").val(datos.email);
                    $("#idCliente").val(id);
                    $("#trid").val(trid);
                },
            });
        });
        $("#updateCliente").click(function() {
            $("#loader-menor").show();
            let data = $("#clientesEditar").serializeArray();
            data.forEach(function(item) {
                if (item.name == "departamentoEditar") item.value = selectedText("#departamentoEditar");
                if (item.name == "provinciaEditar") item.value = selectedText("#provinciaEditar");
                if (item.name == "distritoEditar") item.value = selectedText("#distritoEditar");
            });
            let id = $("#idCliente").val();
            let idData = {
                name: "idPre",
                value: id,
            };
            $.ajax({
                url: _URL + "/ajs/clientes/editar",
                type: "POST",
                data: data,
                success: function(resp) {
                    $("#loader-menor").hide();
                    let data = JSON.parse(resp);
                    console.log(resp);
                    if (Array.isArray(data)) {
                        tabla_clientes.ajax.reload(null, false);
                        Swal.fire("Buen trabajo", "Actualizacion exitosa", "success");
                        $("#editarModal").modal("hide");
                        $("body").removeClass("modal-open");
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: JSON.parse(resp),
                        });
                    }
                },
            });
        });
        $("#tabla_clientes").on("click", ".btnBorrar", function() {
            var id = $(this).data("id");
            let idData = {
                name: "idDelete",
                value: id,
            };
            Swal.fire({
                title: "Deseas borrar el registro?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: _URL + "/ajs/clientes/borrar",
                        type: "post",
                        data: idData,
                        success: function(resp) {
                            /* console.log(resp); */
                            tabla_clientes.ajax.reload(null, false);
                            Swal.fire(
                                "Buen trabajo",
                                "Registro Borrado Exitosamente",
                                "success"
                            );
                        },
                    });
                } else {}
            });
        });
        $("#btnBuscarInfo").click(function(e) {
            e.preventDefault();
            if (!$("#documentoAgregar").val()) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Debe ingresar un DNI o RUC",
                });
            } else {
                if (
                    $("#documentoAgregar").val().length === 8 ||
                    $("#documentoAgregar").val().length === 11
                ) {
                    let docu = $("#documentoAgregar").val();
                    $("#loader-menor").show();
                    $.ajax({
                        url: _URL + "/ajs/consulta/doc/cliente",
                        type: "post",
                        data: {
                            doc: docu
                        },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            let datos = JSON.parse(resp);
                            console.log(datos.data);
                            /*  console.log(resp); */
                                         if (datos.data.nombre) {
                              $("#datosAgregar").val(datos.data.nombre);
                            } else if (datos.data.razon_social) {
                              $("#datosAgregar").val(datos.data.razon_social);
                            } else {
                              alertAdvertencia("Documento no encontrado");
                            }
                            console.log(datos.data.direccion)
                            $("#direccionAgregar").val(datos.data.direccion||'');
                            /* $("#datosAgregar").val(datos.data.dni);   */
                            //PRUEBA RUC 10427993120
                        },
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Debe ingresar un DNI o RUC",
                    });
                }
            }
        });
        $("#btnBuscarInfoEditar").click(function(e) {
            e.preventDefault();
            if (!$("#documentoEditar").val()) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Debe ingresar un DNI o RUC",
                });
            } else {
                if (
                    $("#documentoEditar").val().length === 8 ||
                    $("#documentoEditar").val().length === 11
                ) {
                    let docu = $("#documentoEditar").val();
                    $("#loader-menor").show();
                    $.ajax({
                        url: _URL + "/ajs/consulta/doc/cliente",
                        type: "post",
                        data: {
                            doc: docu
                        },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            let datos = JSON.parse(resp);
                            console.log(datos.data);
                            console.log(resp);
                            if (datos.data.nombre) {
                                $("#datosEditar").val(datos.data.nombre);
                            } else if (datos.data.razon_social) {
                                $("#datosEditar").val(datos.data.razon_social);
                            } else {
                                alertAdvertencia("Documento no encontrado");
                            }
                            $("#direccionEditar").val(datos.data.direccion||'');
                            /* $("#datosAgregar").val(datos.data.dni);   */
                            //PRUEBA RUC 10427993120
                        },
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Debe ingresar un DNI o RUC",
                    });
                }
            }
        });



        function cargarDepartamentos(selectId) {
            $.post(_URL + "/ajs/consulta/lista/departamentos", function(resp) {
                let data = JSON.parse(resp);
                let select = $(selectId);
                select.find("option:not(:first)").remove();
                data.forEach(function(item) {
                    select.append($("<option>").val(item.departamento).text(item.nombre));
                });
            });
        }

        function cargarProvincias(departamento, selectId) {
            if (!departamento) {
                $(selectId).html('<option value="">-- Seleccionar --</option>');
                return;
            }
            $.post(_URL + "/ajs/consulta/lista/provincias", { departamento: departamento }, function(resp) {
                let data = JSON.parse(resp);
                let select = $(selectId);
                select.find("option:not(:first)").remove();
                data.forEach(function(item) {
                    select.append($("<option>").val(item.provincia).text(item.nombre));
                });
            });
        }

        function cargarDistritos(departamento, provincia, selectId) {
            if (!departamento || !provincia) {
                $(selectId).html('<option value="">-- Seleccionar --</option>');
                return;
            }
            $.post(_URL + "/ajs/consulta/lista/distrito", { departamento: departamento, provincia: provincia }, function(resp) {
                let data = JSON.parse(resp);
                let select = $(selectId);
                select.find("option:not(:first)").remove();
                data.forEach(function(item) {
                    select.append($("<option>").val(item.ubigeo).text(item.nombre));
                });
            });
        }

        function selectedText(selectId) {
            return $(selectId + " option:selected").text();
        }

        function selectByText(selectId, text) {
            $(selectId + " option").each(function() {
                if ($(this).text() == text) {
                    $(selectId).val($(this).val());
                    return false;
                }
            });
        }

        function getCodeByText(selectId, text) {
            var code = "";
            $(selectId + " option").each(function() {
                if ($(this).text() == text) {
                    code = $(this).val();
                    return false;
                }
            });
            return code;
        }

        cargarDepartamentos("#departamentoAgregar");
        cargarDepartamentos("#departamentoEditar");

        $("#departamentoAgregar").change(function() {
            var dep = $(this).val();
            $("#provinciaAgregar").html('<option value="">-- Seleccionar --</option>');
            $("#distritoAgregar").html('<option value="">-- Seleccionar --</option>');
            cargarProvincias(dep, "#provinciaAgregar");
        });

        $("#provinciaAgregar").change(function() {
            var dep = $("#departamentoAgregar").val();
            var prov = $(this).val();
            $("#distritoAgregar").html('<option value="">-- Seleccionar --</option>');
            cargarDistritos(dep, prov, "#distritoAgregar");
        });

        $("#departamentoEditar").change(function() {
            var dep = $(this).val();
            $("#provinciaEditar").html('<option value="">-- Seleccionar --</option>');
            $("#distritoEditar").html('<option value="">-- Seleccionar --</option>');
            cargarProvincias(dep, "#provinciaEditar");
        });

        $("#provinciaEditar").change(function() {
            var dep = $("#departamentoEditar").val();
            var prov = $(this).val();
            $("#distritoEditar").html('<option value="">-- Seleccionar --</option>');
            cargarDistritos(dep, prov, "#distritoEditar");
        });

        $("#nuevoExcel").change(function() {
            console.log("aaaaaaaa")
            if ($("#nuevoExcel").val().length > 0) {
                var fd = new FormData();
                fd.append('file', $("#nuevoExcel")[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: _URL + "/ajs/clientes/add/exel",
                    data: fd,
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function() {
                        console.log('inicio');
                        $("#loader-menor").show();
                    },
                    error: function(err) {
                        $("#loader-menor").hide();
                        console.log(err);
                    },
                    success: function(resp) {
                        $("#loader-menor").hide();
                        console.log(resp);
                        /* return */
                        resp = JSON.parse(resp)
                        if (resp.res) {
                            var bloc = true;
                            var listaTemp = [];
                            resp.data.forEach(function(el) {
                                if (!bloc) {
                                    listaTemp.push({
                                        documento: el[0],
                                        datos: el[1],
                                        direccion: el[2],
                                        direccion2: el[3],
                                        telefono: el[4],
                                        telefono2: el[5],
                                        email: el[6],
                                        departamento: el[7],
                                        provincia: el[8],
                                        distrito: el[9],
                                        fecha_nacimiento: el[10],
                                        /* codSunat: el[8],
                                        almacen: el[9],
                                        afecto: false,
                                        precio_unidad: el[3],
                                        codigoProd: el[10] */
                                    })
                                }
                                bloc = false
                            })
                            app._data.listaClientes = listaTemp
                            $("#importarModal").modal("hide")
                            $("#modal-lista-clientes").modal("show")
                        } else {
                            alertAdvertencia("No se pudo subir el Archivo")
                        }
                        $("#nuevoExcel").val("")

                    }
                })
            }
        })

    });
</script>
