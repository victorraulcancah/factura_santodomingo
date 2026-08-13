<?php

    require_once "app/models/Cliente.php";

    $c_cliente = new Cliente();
    $c_cliente->setIdEmpresa($_SESSION['id_empresa']);
?>
<style>
    .text-left {
        text-align: left;
    }

    #tabla_usuarios {
        width: 100% !important;
    }
</style>
<div class="page-title-box" style="padding: 12px 0;">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h6 class="page-title text-center">DATOS DE USUARIOS</h6>

        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#agregarModal"
                                class="btn btn-primary"><i class="fa fa-plus"></i> Agregar
                        </button>
                        <!--   <button type="button" data-bs-toggle="modal" data-bs-target="#editarModal" class="btn btn-warning">Editar</button> -->
                    </div>

                    <!--<div class="col-md-6 text-end">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#importarModal"
                                class="btn btn-success"><i class="fa fa-file-excel"></i> Importar
                        </button>
                    </div>-->
                </div>
            </div>
            <div id="conte-vue-modals">
                <div class="card-body">
                    <!-- MODAL CONFIRMAR DATOS -->
                    <div class="modal fade" id="modal-lista-clientes" data-bs-backdrop="static" data-bs-keyboard="false"
                         tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog  modal-dialog-scrollable modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">Lista de Proveedores</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-sm table-bordered text-center" id="tablaImportarCliente">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Empresa</th>
                                            <th>Rol</th>
                                            <th>Numero de Documento</th>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Nombres</th>
                                            <th>Apellidos</th>
                                            <th>Rubro</th>
                                            <th>Sucursal</th>
                                            <th>Telefono</th>
                                            <th>Token de Reset</th>
                                            <th>Estado</th>
                                            <th>Mensaje</th>
                                        </tr>
                                        </thead>
                                        <tbody id="tbodyImportar">
                                        <!--  <tr id="trImportar"></tr> -->
                                        <tr id="trImportar" v-for="(item,index) in listaClientes">
                                            <td>{{ usuario.usuario_id }}</td>
                                            <td>{{ usuario.id_empresa }}</td>
                                            <td>{{ usuario.id_rol }}</td>
                                            <td>{{ usuario.num_doc }}</td>
                                            <td>{{ usuario.usuario }}</td>
                                            <td>{{ usuario.email }}</td>
                                            <td>{{ usuario.nombres }}</td>
                                            <td>{{ usuario.apellidos }}</td>
                                            <td>{{ usuario.rubro }}</td>
                                            <td>{{ usuario.sucursal }}</td>
                                            <td>{{ usuario.telefono }}</td>
                                            <td>{{ usuario.token_reset }}</td>
                                            <td>{{ usuario.estado }}</td>
                                            <td>{{ usuario.mensaje }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <!--  <button id="agregarClientesImport" type="button" class="btn btn-primary">Guardar</button> -->
                                    <button @click="agregarListaImport" type="button" class="btn btn-primary">Guardar
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL DE IMPORTAR XLS -->
                    <div class="modal fade" id="importarModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                         aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Importar Proveedores con EXCEL</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form enctype='multipart/form-data'>
                                        <div class="mb-3">
                                            <p>Descargue el modelo en <span class="fw-bold">EXCEL</span> para importar,
                                                no
                                                modifique los campos en el archivo, <span class="fw-bold">click para
                                                    descargar</span> <a
                                                        href="<?=URL::to("public/plantillaproveedores.xlsx")?>">template.xlsx</a>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="col-form-label">Importar Excel:</label>

                                        </div>
                                        <input type="file" id="nuevoExcel" name="nuevoExcel"
                                               accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL DE AGREGAR CLIENTE -->
                    <div class="modal fade" id="agregarModal" tabindex="-1" role="dialog"
                         aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                                    <input type="text" class="form-control" required
                                                           id="documentoAgregar" name="num_doc">
                                                    <div class="input-group-prepend">
                                                        <button id="btnBuscarInfo" class="btn btn-primary"><i
                                                                    class="fa fa-search"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label for="nombresAgregar">Nombres <span
                                                            style="color: red;"></span></label>
                                                <input type="text" class="form-control" id="nombresAgregar"
                                                       name="nombres">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label for="apellidosAgregar">Apellidos <span
                                                            style="color: red;"></span></label>
                                                <input type="text" class="form-control" id="apellidosAgregar"
                                                       name="apellidos">
                                            </div>
                                            <!--<div class="col-md-4 form-group">
                                                <label for="usuarioAgregar">Usuario <span
                                                            style="color: red;"></span></label>
                                                <input type="text" class="form-control" id="usuarioAgregar"
                                                       name="usuario">
                                            </div>-->

                                            <div class="col-md-4 mt-3">
                                                <label for="usuarioAgregar">Usuario</label>
                                                <input required type="text" class="form-control" id="usuarioAgregar"
                                                       name="usuario">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="claveAgregar">Clave</label>
                                                <input required type="text" class="form-control" id="claveAgregar"
                                                       name="clave">
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label for="id_rolAgregar">Rol Usuario</label>
                                                <select class="form-select" id="id_rolAgregar" name="id_rol">
                                                    <option value="1">Administrador</option>
                                                    <option value="2">Vendedor</option>
							<option value="3">Cajero</option>
							<option value="4">Almacenista</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="telefonoAgregar">Telefono</label>
                                                <input type="number" class="form-control" id="telefonoAgregar"
                                                       name="telefono">
                                            </div>
                                            <div class="col-md-8 mt-3">
                                                <label for="emailAgregar">Email</label>
                                                <input required type="text" class="form-control" id="emailAgregar"
                                                       name="email">
                                            </div>
                                            <div class="col-md-12 mt-3">
                                                <label for="direccionAgregar">Direccion</label>
                                                <input type="text" class="form-control" id="direccionAgregar"
                                                       name="direccion">
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="nuevoUsuario" type="button" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- EDITAR MODAL -->
                    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog"
                         aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Editar</h5>

                                </div>
                                <div class="modal-body">
                                    <form id="usuarioEditar">
                                        <div class="row">
                                            <input type="hidden" name="usuario_id" id="iDusuario" value="">

                                            <div class="col-md-6 mt-3">
                                                <label for="usuariosAgregar" class="col-form-label">Usuario <span
                                                            style="color: red;">(*)</span></label>
                                                <input type="text" class="form-control" id="usuariosAgregar"
                                                       name="usuario">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="id_rolEditar" class="col-form-label">Rol Usuario</label>
                                                <!-- <input type="text" class="form-control" id="emailEditar"
                                                        name="email">-->
                                                <select class="form-select" id="id_rolEditar" name="id_rol">
                                                    <option value="1">Administrador</option>
                                                    <option value="2">Vendedor</option>
							<option value="3">Cajero</option>
							<option value="4">Almacenista</option>
                                                </select>
                                            </div>
                                            <!--<div class="col-md-6 mt-3">
                                                <label for="claveAgregar" class="col-form-label">Clave <span
                                                            style="color: red;">(*)</span></label>
                                                <input type="text" class="form-control" id="claveAgregar"
                                                       name="usuario">
                                            </div>-->

                                            <div class="col-md-6 mt-3">
                                                <label for="emailEditar" class="col-form-label">Email</label>
                                                <input type="text" class="form-control" id="emailEditar"
                                                       name="email">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="nombresEditar" class="col-form-label">Nombres</label>
                                                <input type="text" class="form-control" id="nombresEditar"
                                                       name="nombres">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="apellidosEditar" class="col-form-label">Apellidos</label>
                                                <input type="text" class="form-control" id="apellidosEditar"
                                                       name="apellidos">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="num_docEditar" class="col-form-label">DNI</label>
                                                <input type="number" class="form-control" id="num_docEditar"
                                                       name="num_doc">
                                            </div>

                                            <div class="col-md-6 mt-3">
                                                <label for="direccionEditar" class="col-form-label">Direccion</label>
                                                <input type="text" class="form-control" id="direccionEditar"
                                                       name="direccion">
                                            </div>


                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoEditar" class="col-form-label">Telefono</label>
                                                <input type="number" class="form-control" id="telefonoEditar"
                                                       name="telefono">
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="updateUsuario" type="button" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-title-desc">
                        <div class="table-responsive">
                            <table id="tabla_usuarios"
                                   class="table table-bordered dt-responsive nowrap text-center table-sm dataTable no-footer">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Rol</th>
                                    <th>DNI</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Rubro</th>
                                    <!--<th>Sucursal</th>-->
                                    <th>Telefono</th>
                                    <th>Creado</th>
                                    <th>Estado</th>
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
	$(document).ready(function () {

		const app = new Vue({
			el: "#conte-vue-modals",
			data: {
				listaClientes: []
			},
			methods: {
				agregarListaImport() {

					if (this.listaClientes.length > 0) {

						_ajax("/ajs/proveedores/set/group", "POST", {
								lista: JSON.stringify(this.listaClientes)
							},
							function (resp) {
								console.log(resp);
								/* return */
								if (resp.res) {
									alertExito("Agregado")
										.then(function () {
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

		tabla_usuarios = $("#tabla_usuarios").DataTable({
			paging: true,
			bFilter: true,
			ordering: false,
			searching: true,
			destroy: true,
			ajax: {
				url: _URL + "/ajs/usuarios/render",
				method: "POST", //usamos el metodo POST
				dataSrc: "",
			},
			language: {
				url: "ServerSide/Spanish.json",
			},
			columns: [{
				data: "usuario_id",
				class: "text-center",
			},
				{
					data: "id_rol",
					class: "text-left",
					render: function (data, type, row) {
						return `<div class="text-left" style="font-weight: 500">
                                  			${Number(row.id_rol) === 1 ? 'Admin' : 
    							    Number(row.id_rol) === 2 ? 'Vendedor' : 
							    Number(row.id_rol) === 3 ? 'Cajero': 'Almacen' }
                                		</div>`;
					}
				},
				{
					data: "num_doc",
					class: "text-left",
				},
				{
					data: "usuario",
					class: "text-left",
				},
				{
					data: "email",
					class: "text-left",
				},
				{
					data: "nombres",
					class: "text-left",
					render: function (data, type, row) {
						return `<div class="text-left" style="font-weight: 500">
                                   ${row.nombres}
                                </div>`;
					}
				},
				{
					data: "apellidos",
					class: "text-center",
					render: function (data, type, row) {
						return `<div class="text-left" style="font-weight: 500">
                                   ${row.apellidos}
                                </div>`;
					}
				},
				{
					data: "rubro",
					class: "text-center",
				},
				/*{
					data: "sucursal",
					class: "text-center",
				},*/
				{
					data: "telefono",
					class: "text-center",
				},
				{
					data: "fecha_create",
					class: "text-center",
				},
				{
					data: "estado",
					class: "text-center",
					render: function (data, type, row) {
						var estado = Number(row.estado) === 1 ? 'Activado' : 'Inactivo';
						var clase = Number(row.estado) === 1 ? 'btn btn-sm btn-success' : 'btn btn-sm btn-warning';

						return `<div class="text-center">
                                    <div class="btn-group btn-sm">
                                        <span class="${clase}">${estado}</span>
                                    </div>
                                </div>`;
					}
				},
				{
					data: null,
					class: "text-center",
					render: function (data, type, row) {
						return `<div class="text-center">
                                    <div class="btn-group btn-sm">
                                        <button  data-id="${Number(row.usuario_id)}" class="btn btn-sm btn-warning btnEditar"><i class="fa fa-edit"></i> </button>
                                        <button btn-sm  data-id="${Number(row.usuario_id)}" class="btn btn-sm  btn-danger btnBorrar"><i class="fa fa-trash"></i> </button>
                                    </div>
                                </div>`;
					},
				},
			],
		});
		$("#nuevoUsuario").click(function () {
			$("#loader-menor").show();
			let data = $("#frmClientesAgregar").serializeArray();
			$.ajax({
				type: "POST",
				url: _URL + "/ajs/usuarios/set",
				data: data,
				success: function (resp) {
					$("#loader-menor").hide();
					let data = JSON.parse(resp);
					if (typeof data === "object") {
						tabla_usuarios.ajax.reload(null, false);
						Swal.fire("Buen trabajo!", "Registro Exitoso", "success");
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

		$("#tabla_usuarios").on("click", ".btnEditar ", function (event) {
			$("#loader-menor").show();
			var table = $("#tabla_usuarios").DataTable();
			var trid = $(this).closest("tr").attr("id");
			var id = $(this).data("id");
			$("#editarModal").modal("show");
			$("#editarModal")
				.find(".modal-title")
				.text("Editar Usuario No" + id);
			$.ajax({
				url: _URL + "/ajs/usuarios/get",
				data: {
					id: id,
				},
				type: "post",
				success: function (data) {

					$("#loader-menor").hide();
					let datos = JSON.parse(data);
					console.log(datos);
					$("#usuariosAgregar").val(datos.usuario);
					$("#emailEditar").val(datos.email);
					$("#direccionEditar").val(datos.direccion);
					$("#nombresEditar").val(datos.nombres);
					$("#apellidosEditar").val(datos.apellidos);
					$("#num_docEditar").val(datos.num_doc);
					$("#telefonoEditar").val(datos.telefono);

					$("#id_rolEditar").prop("selectedIndex", (datos.id_rol - 1));

					$("#iDusuario").val(id);
					$("#trid").val(trid);
				},
			});
		});

		$("#updateUsuario").click(function () {
			$("#loader-menor").show();
			let data = $("#usuarioEditar").serializeArray();
			let id = $("#iDusuario").val();
			let idData = {
				name: "idPre",
				value: id,
			};
			$.ajax({
				url: _URL + "/ajs/usuarios/update",
				type: "POST",
				data: data,
				success: function (resp) {
					$("#loader-menor").hide();
					console.log(resp);
					let data = JSON.parse(resp);
					console.log(data);
					if (Array.isArray(data)) {
						tabla_usuarios.ajax.reload(null, false);
						Swal.fire("Buen trabajo!", "Actualizacion exitosa", "success");
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

		$("#tabla_usuarios").on("click", ".btnBorrar", function () {
			var id = $(this).data("id");
			let idData = {
				id: id,
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
						url: _URL + "/ajs/usuarios/delete",
						type: "post",
						data: idData,
						success: function (resp) {
							/* console.log(resp); */
							tabla_usuarios.ajax.reload(null, false);
							Swal.fire(
								"Buen trabajo!",
								"Registro Borrado Exitosamente",
								"success"
							);
						},
					});
				} else {
				}
			});
		});
		$("#btnBuscarInfo").click(function (e) {
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
						success: function (resp) {
							$("#loader-menor").hide();
							let datos = JSON.parse(resp);
							console.log(datos.data);
							/*  console.log(resp); */
							if (datos.data.nombre) {
								$("#nombresAgregar").val(datos.data.nombres);
								$("#apellidosAgregar").val(datos.data.apellido_paterno + ' ' + datos.data.apellido_materno);
							} else if (datos.data.razon_social) {
								$("#nombresAgregar").val(datos.data.razon_social);
							} else {
								alertAdvertencia("Documento no encontrado");
							}
							console.log(datos.data.direccion)
							$("#direccionAgregar").val(datos.data.direccion || '');
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
		$("#btnBuscarInfoEditar").click(function (e) {
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
						success: function (resp) {
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
							$("#direccionEditar").val(datos.data.direccion || '');
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


		$("#nuevoExcel").change(function () {
			console.log("aaaaaaaa")
			if ($("#nuevoExcel").val().length > 0) {
				var fd = new FormData();
				fd.append('file', $("#nuevoExcel")[0].files[0]);
				$.ajax({
					type: 'POST',
					url: _URL + "/ajs/proveedores/set/exel",
					data: fd,
					contentType: false,
					cache: false,
					processData: false,
					beforeSend: function () {
						console.log('inicio');
						$("#loader-menor").show();
					},
					error: function (err) {
						$("#loader-menor").hide();
						console.log(err);
					},
					success: function (resp) {
						$("#loader-menor").hide();
						console.log(resp);
						/* return */
						resp = JSON.parse(resp)
						if (resp.res) {
							var bloc = true;
							var listaTemp = [];
							resp.data.forEach(function (el) {
								if (!bloc) {
									listaTemp.push({
										ruc: el[0],
										razon_social: el[1],
										direccion: el[2],
										telefono: el[3],
										email: el[4],
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
