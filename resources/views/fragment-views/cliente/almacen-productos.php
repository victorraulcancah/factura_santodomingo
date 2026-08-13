<?php

require_once "app/models/Producto.php";

$c_producto = new Producto();
$c_producto->setIdEmpresa($_SESSION['id_empresa']);

?>
<style>
	.ui-widget.ui-widget-content {
		z-index: 10000 !important;
	}
</style>
<div class="page-title-box">
	<div class="row align-items-center">
		<div class="col-md-8">
			<h6 class="page-title">Productos</h6>
			<ol class="breadcrumb m-0">
				<li class="breadcrumb-item"><a href="javascript: void(0);">Almacen </a></li>
			</ol>
		</div>
		<div class="col-md-4">
			<div class="float-end d-none d-md-block">
				<div hidden class="dropdown">
					<button class="btn btn-primary  dropdown-toggle" type="button" id="dropdownMenuButton"
						data-bs-toggle="dropdown" aria-expanded="false">
						<i class="mdi mdi-cog me-2"></i> Settings
					</button>
					<div class="dropdown-menu dropdown-menu-end">
						<a class="dropdown-item" href="#">Action</a>
						<a class="dropdown-item" href="#">Another action</a>
						<a class="dropdown-item" href="#">Something else here</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item" href="#">Separated link</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="card card-default">
			<div class="card-body">
				<div class="alert alert-warning" role="alert">
					<strong>ALERTA DE ACTUALIZACION!</strong> a partir del año 2021, sunat exige el codigo SUNAT (Codigo
					de productos y servicios estándar de las Naciones Unidas - UNSPSC v14_0801, a que hace referencia el
					catálogo N° 25 del Anexo V de la Resolución de Superintendencia N° 340-2017/SUNAT y
					modificatorias.). Modifique el valor en Productos
				</div>
			</div>
		</div>
	</div>
	<!--col-md-6-->
</div>
<div id="conte-vue-modals">

	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<div class="row">
						<div class="col-md-6 d-flex align-items-center">
							<h4 class="card-title mb-0">Lista de Productos</h4>

							<select id="selectAlmacenes" class="form-control mx-2 w-50" @change="changeAlmacen($event)">
								<option v-for="item in listaAlmacenes" :value="item.id" :selected="item.id == 1">{{item.name}}</option>
							</select>
							<h4 class="card-title mb-0">Stock:</h4>
							<select id="selectStock" class="form-control mx-2 w-50" @change="changeStock($event)">
								<option value=''> -- </option>
								<option value='-1'>
									<0 </option>
								<option value='0'> =0 </option>
								<option value='1'> >0 </option>
							</select>


						</div>
						<div class="col-md-6 text-end">
							<button onclick="descarFunccc()" class="btn btn-success"><i class="fa fa-file-excel"></i>
								Descargar Exel por busqueda
							</button>
							<button data-bs-toggle="modal" data-bs-target="#importarModal" class="btn btn-success"><i
									class="fa fa-file-excel"></i> Importar
							</button>
							<button data-bs-toggle="modal" data-bs-target="#modal-add-prod" class="btn btn-primary"><i
									class="fa fa-plus"></i> Agregar Producto
							</button>
							<button class="btn btn-danger btnBorrar"><i class="fa fa-times"></i> Borrar</button>
							<button hidden class="btn btn-danger" @click="agregarIds"><i class="fa fa-times"></i>
								Seleccionar Todos
							</button>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="col-md-12 text-center">
						<h4 class="card-title mb-0">TOTAL DE COSTOS: <span style="color: red;" class="tcost"></span></h4>
					</div>
					<table id="datatable_p" class="table table-sm table-bordered text-center" cellspacing="0"
						width="100%">

						<thead>
							<tr>
								<th>Id</th>
								<th>Codigo</th>
								<th>Descripcion</th>
								<th>Serie</th>
								<th>Historial</th>
								<th>Categoria</th>
								<th>stock</th>

								<th>Costo</th>
								<th>Precios</th>
								<th>Ultima Venta</th>
								<th>Proveedor</th>
								<th>Aumentar Stock</th>
								<th>Editar</th>
								<th>Eliminar <input type="checkbox" class='btnSeleccionarTodos'></th>
								<th>Detalles</th>
							</tr>
						</thead>
						<tbody id='tbodyProductos'>
							<?php
							/*$a_productos = $c_producto->verFilas($almacenProducto);

foreach ($a_productos as $fila) {
    if ($fila['ultima_salida'] == '1000-01-01' || $fila['ultima_salida'] == '0000-00-00') {
        $label_estado = '<span class="label label-warning">Sin Movimiento</span>';
    } else {
        $label_estado = '<span class="label label-success">' . $fila['ultima_salida'] . '</span>';
    }
    ?>
                                <tr>
                                    <td class="text-center"><?php echo $fila['id_producto'] ?></td>
                                    <td class="text-center"><?php echo $fila['codigo'] ?></td>
                                    <td><a href="javascript:abrirModalBarras(<?php echo $fila['id_producto'] ?>)"><?php echo $fila['descripcion'] ?></a></td>
                                    <td><?php echo $fila['codsunat'] ?></td>
                                    <td class="text-right"><?php echo $fila['cantidad'] ?></td>

                                    <td class="text-right"><?php echo number_format($fila['costo'], 2, ".", "") ?></td>
                                    <td class="text-right"> <button data-item="<?=$fila['id_producto']?>" class="btn-ver-precios btn btn-sm btn-info"> <i class="fas fa-eye"></i></button></td>
                                    <td class="text-center"><?php echo $label_estado ?></td>
                                    <td class="text-center"><?php echo $fila['razon_social'] ?></td>
                                    <td class="text-center">
                                        <button data-item="<?=$fila['id_producto']?>" class="btn-re-stock btn btn-sm btn-warning"> <i class="fas fa-sync"></i></button>
                                    </td>
                                    <td class="text-center">
                                        <button data-item="<?=$fila['id_producto']?>" class="btn-edt btn btn-sm btn-info"> <i class="fa fa-edit"></i></button>
                                    </td>
                                    <td>
                                        <input type="checkbox" class='btnCheckEliminar' data-id="<?=$fila['id_producto']?>">
                                    </td>
                                    <td class="text-center">
                                        <button data-item="<?=$fila['id_producto']?>" class="btn-reporte btn btn-sm btn-info"> <i class="fa fa-file"></i></button>
                                    </td>
                                </tr>
                            <?php

}*/
							?>
						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>


	<div class="modal fade" id="modal-precios" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Precios</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<form @submit.prevent="agregarPrecios">

					<div class="modal-body">
						<div class="row">
							<div class="form-group col-md-12">
								<label>Precio Unidad: </label>
								<input v-model="edt.precio_unidad" id="precio_unidad" class="form-control">
							</div>
							<div class="form-group col-md-12">
								<label>Precio Distribuci&oacute;n: </label>
								<input v-model="edt.precio4" id="precio4" class="form-control">
							</div>
							<div class="form-group col-md-12">
								<label>Precio VIP: </label>
								<input v-model="edt.precio" id="precio1" class="form-control">
							</div>
							<div class="form-group col-md-12">
								<label>Precio OFERTA: </label>
								<input v-model="edt.precio2" id="precio2" class="form-control">
							</div>
							<div class="form-group col-md-12">
								<label>Precio REMATE: </label>
								<input v-model="edt.precio3" id="precio3" class="form-control">
							</div>

						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Guardar</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
					</div>
				</form>

			</div>
		</div>
	</div>
	<div class="modal fade" id="modal-add-prod" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Nuevo Producto</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form @submit.prevent="agregarProd">
					<div class="modal-body">
						<div class="row">
							<div class="form-group col-md-8 mt-2">
								<label>Descripcion de producto</label>
								<input v-model="reg.descripcicon" required type="text" class="form-control">
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Codigo</label>
								<input v-model="reg.codigo" required type="text" class="form-control">
							</div>
							<div class="form-group col-md-3 mt-2">
								<label>Serie P.</label>
								<input v-model="reg.serie_producto" required type="text" class="form-control">
							</div>

							<div class="form-group col-md-3 mt-2">
								<label>
									Precio Venta
									<span v-if="reg.id_unidad_derivada" class="text-info">
										(por {{ nombreUnidadDerivada(reg.id_unidad_derivada) }})
									</span>
								</label>
								<input v-model="reg.precio" @keypress="onlyNumber" required value="0" type="text"
									class="form-control">
								<small v-if="reg.id_unidad_derivada && reg.unidades_por_caja > 1 && reg.precio > 0"
									class="form-text text-muted">
									= {{(reg.precio / reg.unidades_por_caja).toFixed(4)}} por unidad
								</small>
							</div>
							<div class="form-group col-md-3 mt-2">
								<label>
									Costo
									<span v-if="reg.id_unidad_derivada" class="text-info">
										(por {{ nombreUnidadDerivada(reg.id_unidad_derivada) }})
									</span>
								</label>
								<input v-model="reg.costo" @keypress="onlyNumber" required value="0" type="text"
									class="form-control">
								<small v-if="reg.id_unidad_derivada && reg.unidades_por_caja > 1 && reg.costo > 0"
									class="form-text text-muted">
									= {{(reg.costo / reg.unidades_por_caja).toFixed(4)}} por unidad
								</small>
							</div>
							<div class="form-group col-md-3 mt-2">
								<label>Cantidad</label>
								<input v-model="reg.cantidad" @keypress="onlyNumber" required type="text"
									class="form-control">
								<small class="form-text text-muted">Stock en unidades base</small>
							</div>
							<div class="form-group col-md-3 mt-2">
								<label>Unidad Derivada</label>
								<div class="input-group">
									<select v-model="reg.id_unidad_derivada" class="form-control">
										<option :value="null">-- Solo unidad --</option>
										<option v-for="item in listaUnidades" :value="item.id_unidad" :key="item.id_unidad">
											{{ item.nombre }}
										</option>
									</select>
									<button type="button" @click="abrirNuevaUnidad" class="btn btn-primary">
										<i class="fa fa-plus"></i>
									</button>
								</div>
								<small class="form-text text-muted">Caja, Docena, Pack, etc.</small>
							</div>
							<div class="form-group col-md-2 mt-2">
								<label>Multiplicador</label>
								<input v-model="reg.unidades_por_caja" @keypress="onlyNumber" type="text"
									class="form-control" placeholder="1"
									:disabled="!reg.id_unidad_derivada">
								<small class="form-text text-muted">Unid. por caja</small>
							</div>
							<div class="form-group col-md-2 mt-2">
								<label>Volumen Unidad</label>
								<input v-model="reg.volumen_unidad" type="text" class="form-control"
									placeholder="Ej: 1L, 500ml">
								<small class="form-text text-muted">Decorativo</small>
							</div>
							<div class="form-group col-md-5 mt-2">
								<label>Categoria</label>
								<div class="input-group">
									<select v-model="reg.codSunat" id="selecCatego" class="form-control">
										<option v-for="item in listaCatego" :value="item.nombre">{{item.nombre}}</option>
									</select>
									<div class="input-group-prepend">
										<button type="button" @click="AggCatego" class="btn btn-primary">
											<i class="fa fa-plus"></i></button>
									</div>
								</div>


							</div>
							<div class="form-group col-md-5 mt-2">
								<label>Almacen</label>
								<div class="input-group">
									<select v-model="reg.almacen" class="form-control">
										<option v-for="item in listaAlmacenes" :value="item.id">{{item.name}}</option>
									</select>
								</div>
							</div>
							<div class="form-group col-md-2 mt-2">
								<label>Afecto ICBP</label>
								<select v-model="reg.afecto" class="form-control">
									<option value="0">No</option>
									<option value="1">Si</option>
								</select>
							</div>
							<div class="form-group col-md-4 mt-2" style="display: none !important;">
								<label>Precio por Mayor</label>
								<input v-model="reg.precioMayor" @keypress="onlyNumber" value="0" type="text"
									class="form-control">
							</div>
							<div class="form-group col-md-4 mt-2" style="display: none !important;">
								<label>Precio por Menor</label>
								<input v-model="reg.precioMenor" @keypress="onlyNumber" value="0" type="text"
									class="form-control">
							</div>
							<div class="col-md-12 mt-2">
								<div class="row">
									<div class="form-group col-md-4">
										<label><span class="rojo"></span>RUC: </label>
										<div class="input-group">
											<input @change="ChangeconsultarDocRUC" v-model="reg.ruc" required
												@keypress="onlyNumber" @keyup="buscarProveedor('insert')" type="text"
												class="form-control inputSearch"
												maxlength="11">
											<div class="input-group-prepend">
												<button type="button" @click="consultarDocRUC" class="btn btn-primary">
													<i class="fa fa-search"></i></button>
											</div>
										</div>
									</div>
									<div class="form-group col-md-8">
										<label>Razon Social: </label>
										<input v-model="reg.razon" required type="text"
											class="form-control razon_social">
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Guardar</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal-categoria" tabindex="-1" aria-labelledby="exampleSerie" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleSerie">Nueva Categoria</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"
						aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">

						<div class="col-lg-12">

							<input v-model="addcatego" id="addcatego" type="text" class="form-control text-center">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button @click="GuardarCatego(addcatego)" type="button" class="btn btn-info" data-bs-dismiss="modal">
						Guardar
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal Nueva Unidad Derivada -->
	<div class="modal fade" id="modal-nueva-unidad" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Nueva Unidad Derivada</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form @submit.prevent="guardarNuevaUnidad">
					<div class="modal-body">
						<div class="form-group mb-3">
							<label>Nombre <span class="text-danger">*</span></label>
							<input v-model="nuevaUnidad.nombre" required type="text" class="form-control"
								placeholder="Ej: Caja, Docena, Pack">
						</div>
						<div class="form-group">
							<label>Descripción</label>
							<input v-model="nuevaUnidad.descripcion" type="text" class="form-control" placeholder="Opcional">
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Guardar</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal-edt-prod" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Producto</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form @submit.prevent="actualizarProd">
					<div class="modal-body">
						<div class="row">
							<input v-model="edt.cod_prod" type="hidden" class="form-control id_proveedor_update">
							<div class="form-group col-md-8 mt-2">
								<label>Descripcion de producto</label>
								<input v-model="edt.descripcicon" required type="text" class="form-control">
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Codigo</label>
								<input v-model="edt.codigo" required type="text" class="form-control">
							</div>
							<div class="form-group col-md-3 mt-2">
								<label>Serie P.</label>
								<input v-model="edt.serie_producto" required type="text" class="form-control">
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>
									Precio Venta
									<span v-if="edt.id_unidad_derivada" class="text-info">
										(por {{ nombreUnidadDerivada(edt.id_unidad_derivada) }})
									</span>
								</label>
								<input v-model="edt.precio" @keypress="onlyNumber" required value="0" type="text"
									class="form-control">
								<small v-if="edt.id_unidad_derivada && edt.unidades_por_caja > 1 && edt.precio > 0"
									class="form-text text-muted">
									= {{(edt.precio / edt.unidades_por_caja).toFixed(4)}} por unidad
								</small>
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>
									Costo
									<span v-if="edt.id_unidad_derivada" class="text-info">
										(por {{ nombreUnidadDerivada(edt.id_unidad_derivada) }})
									</span>
								</label>
								<input v-model="edt.costo" @keypress="onlyNumber" required value="0" type="text"
									class="form-control">
								<small v-if="edt.id_unidad_derivada && edt.unidades_por_caja > 1 && edt.costo > 0"
									class="form-text text-muted">
									= {{(edt.costo / edt.unidades_por_caja).toFixed(4)}} por unidad
								</small>
							</div>
							<div class="form-group col-md-12 mt-2">
								<label>Categoria</label>
								<select v-model="edt.codSunat" id="selecCatego" class="form-control">
									<option v-for="item in listaEDT" :value="item.id">{{item.nombre}}</option>
								</select>
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Afecto ICBP</label>
								<select v-model="edt.afecto" class="form-control">
									<option value="0">No</option>
									<option value="1">Si</option>
								</select>
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Usar Codigo Barra</label>

								<div class="input-group mb-3">
									<select v-model="edt.usar_barra" class="form-control">
										<option value="0">No</option>
										<option value="1">Si</option>
									</select>
									<div v-if="edt.usar_barra=='1'" class="input-group-btn">
										<button @click="edtGenerarCodeBarra" type="button" class="btn btn-primary">
											Generar
										</button>
									</div>
								</div>

							</div>
							<div class="form-group col-md-4 mt-2" style="display: none !important;">
								<label>Precio por Mayor</label>
								<input v-model="edt.precioMayor" @keypress="onlyNumber" value="0" type="text"
									class="form-control">
							</div>
							<div class="form-group col-md-4" style="display: none !important;">
								<label>Precio por Menor</label>
								<input v-model="edt.precioMenor" @keypress="onlyNumber" value="0" type="text"
									class="form-control">
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Cantidad</label>
								<input type="text" style="background-color: #d2d6de;" v-model="edt.cantidad" @keypress="onlyNumber" value="0" class="form-control" disabled>
								<small class="form-text text-muted">Stock en unidades base</small>
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Unidad Derivada</label>
								<div class="input-group">
									<select v-model="edt.id_unidad_derivada" class="form-control">
										<option :value="null">-- Solo unidad --</option>
										<option v-for="item in listaUnidades" :value="item.id_unidad" :key="item.id_unidad">
											{{ item.nombre }}
										</option>
									</select>
									<button type="button" @click="abrirNuevaUnidad" class="btn btn-primary">
										<i class="fa fa-plus"></i>
									</button>
								</div>
								<small class="form-text text-muted">Caja, Docena, Pack...</small>
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Multiplicador</label>
								<input v-model="edt.unidades_por_caja" @keypress="onlyNumber" type="text"
									class="form-control" placeholder="1"
									:disabled="!edt.id_unidad_derivada">
								<small class="form-text text-muted">Unid. por caja</small>
							</div>
							<div class="form-group col-md-4 mt-2">
								<label>Volumen Unidad</label>
								<input v-model="edt.volumen_unidad" type="text" class="form-control"
									placeholder="Ej: 1L, 500ml">
								<small class="form-text text-muted">Decorativo</small>
							</div>
							<div class="form-group col-md-12">
								<div class="row">
									<div class="form-group col-md-4">
										<label><span class="rojo"></span>RUC: </label>
										<div class="input-group">
											<input @change="ChangeconsultarDocRUC" v-model="edt.ruc" required
												@keypress="onlyNumber" @keyup="buscarProveedor('update')" type="text"
												class="form-control inputSearch"
												maxlength="11">
											<div class="input-group-prepend">
												<button type="button" @click="consultarDocRUC" class="btn btn-primary">
													<i class="fa fa-search"></i></button>
											</div>
										</div>
									</div>
									<div class="form-group col-md-8">
										<label>Razon Social: </label>
										<input v-model="edt.razon" required type="text"
											class="form-control razon_social">
									</div>
								</div>
							</div>
							<div class="col-md-12 mt-3 text-center">
								<img id="barcode" />
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Actualizar</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal-restock" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form @submit.prevent="agregarStock">
					<div class="modal-body">
						<div class="form-group">
							<label>Cantidad</label>
							<input v-model="restock.cantidad" required type="text" class="form-control">
							<small class="form-text text-muted">La cantidad ingresada se sumara a la cantidad
								actual</small>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Guardar</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
					</div>
				</form>

			</div>
		</div>
	</div>

	<div class="modal fade" id="importarModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Importar Productos con EXCEL</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form enctype='multipart/form-data'>
						<div class="mb-3">
							<p>Descargue el modelo en <span class="fw-bold">EXCEL</span> para importar, no
								modifique los campos en el archivo, <span class="fw-bold">click para
									descargar</span> <a href="<?= URL::to('public/plantillaproductoss.xlsx') ?>">plantilla.xlsx</a>
							</p>
						</div>
						<div class="mb-3">
							<label class="col-form-label">Importar Excel:</label>

						</div>
						<input id="file-import-exel"
							accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
							type="file">
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal-lista-productos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
		aria-labelledby="staticBackdropLabel" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-scrollable modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="staticBackdropLabel">Lista de productos</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<table class="table table-sm table-bordered text-center">
						<thead>
							<tr>
								<th>Producto</th>
								<th>Cnt</th>
								<th>Costo</th>
								<th>Precio Unidad</th>
								<th>Precio Club</th>
								<th>Precio 1</th>
								<th>Precio 2</th>
								<th>Precio 3</th>

								<th>Cod.Sunat</th>
								<th>Almacen</th>
								<th>Codigo</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(item,index) in listaProd">
								<td>{{item.descripcicon}}</td>
								<td> {{item.cantidad}}</td>
								<td>{{item.costo}}</td>
								<td>{{item.precio_unidad}}</td>
								<td>{{item.precio4}}</td>
								<td>{{item.precio}}</td>
								<td>{{item.precio2}}</td>
								<td>{{item.precio3}}</td>
								<td>{{item.codSunat}}</td>
								<td>{{item.almacen}}</td>
								<td>{{item.codigoProd}}</td>

								<td>
									<button @click="eliminarItemTablaPro(index)" class="btn-sm btn btn-danger"><i
											class="fa fa-times"></i></button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="modal-footer">
					<button @click="agregarListaImport" type="button" class="btn btn-primary">Guardar</button>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modalCodigoBarras" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Codigo de Barras</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<div class="mb-3 text-center">
						<img id="idCodigoBarras">
					</div>
					<div class="mb-3">
						<label class="form-label">Escalar</label>
						<select id="scalimg" class="form-control">
							<option value="1">NO</option>
							<option value="2">SI</option>
						</select>
					</div>
					<div class="text-center">
						<button class="btn btn-primary" id="btnImprimir" onclick="imprimir()">Imprimir</button>
						<button class="btn btn-primary" id="btnImprimir2" onclick="imprimir2()">Imprimir 2</button>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="modal fade" id="modal-prodEreport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Reporte De Producto</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="col-md-12 mb-3">
					<label class="form-label">AÃ±o</label>
					<select id='anioreporEFG' class="form-control">
						<?php
						$anio = date("Y");
						for ($i = 0; $i < 10; $i++) {
							echo "<option value='$anio'>$anio</option>";
							$anio--;
						}
						?>
					</select>
				</div>
				<div class="col-md-12 mb-3">
					<label class="form-label">Mes</label>
					<select id='mesreprEFG' class="form-control">
						<?php
						$contador = 1;
						$meses = array('ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE');
						foreach ($meses as $mes) {
							echo "<option  " . ($contador == date('m') ? 'selected' : '') . " value='" . ($contador < 10 ? '0' . $contador : $contador) . "'>$mes</option>";
							$contador++;
						}
						?>
					</select>
				</div>
				<div class="col-md-12 mb-3">
					<label class="form-label">Dia</label>
					<input id='diareporEfghg' class="form-control">
				</div>

			</div>
			<div class="modal-footer">
				<button id="generarreporteProd" type="button" class="btn btn-primary">Generar</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>


<div id="modal_ver_detalle" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
	<div class="modal-dialog  modal-md">
		<div class="modal-content">
			<input type="hidden" name="idUsaVenta2" id="idPresu2" value="">
			<input type="hidden" name="trid2" id="trid2" value="">
			<div class="modal-header">
				<h5 class="modal-title" id="myModalLabel"><span class="titulodet"></span> </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<table id="datatableProductoDetalle" class="table table-bordered dt-responsive  text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

					<thead>
						<tr>
							<th style="text-align: center;">#</th>
							<th style="text-align: center;">Serie</th>

						</tr>
					</thead>

				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger waves-effect" data-bs-dismiss="modal">Cerrar</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>

<div id="modal_ver_detalle2" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
	<div class="modal-dialog  modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="myModalLabel"><span class="titulodett"></span> </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<ul class="nav nav-tabs mb-3">
					<li class="nav-item">
						<a href="#Hventas" data-bs-toggle="tab" aria-expanded="false" class="nav-link active">
							&Uacute;ltimas ventas
						</a>
					</li>
					<li class="nav-item">
						<a href="#Hcompras" data-bs-toggle="tab" aria-expanded="true" class="nav-link ">
							&Uacute;ltimas compras
						</a>
					</li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane show active" id="Hventas">
						<table id="Hproductoventa" class="table table-bordered dt-responsive  text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
							<thead>
								<tr>
									<th style="text-align: center;">#</th>
									<th style="text-align: center;">Fecha</th>
									<th style="text-align: center;">Documento</th>
									<th style="text-align: center;">Cliente</th>
									<th style="text-align: center;">Cantidad</th>
									<th style="text-align: center;">Precio</th>
									<th style="text-align: center;">Total</th>
								</tr>
							</thead>
						</table>
					</div>

					<div class="tab-pane" id="Hcompras">
						<table id="Hproductocompra" class="table table-bordered dt-responsive  text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
							<thead>
								<tr>
									<th style="text-align: center;">#</th>
									<th style="text-align: center;">Fecha</th>
									<th style="text-align: center;">Documento</th>
									<th style="text-align: center;">Proveedor</th>
									<th style="text-align: center;">Cantidad</th>
									<th style="text-align: center;">Precio</th>
									<th style="text-align: center;">Total</th>
								</tr>
							</thead>
						</table>

					</div>

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger waves-effect" data-bs-dismiss="modal">Cerrar</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>



<!-- <style>
    input[type=file].hidden {
        color: transparent;
    }
</style> -->
<script src="
https://cdn.jsdelivr.net/npm/@pokusew/escpos@3.0.8/dist/index.min.js
"></script>
<script>
	function descarFunccc() {
		window.open(_URL +
			`/reporte/producto/excel?texto=${$("#datatable_filter input").val()}`)
	}

	var codProdT = ''

	async function printBarcode() {
		try {
			const printer = await EscPosPrinter.requestPrinter();

			// Conectar a la impresora
			await printer.connect();

			// Configurar el tamaÃ±o del ticket (50 mm x 25 mm)
			await printer.setPageFormat(50, 25);

			// Imprimir el tÃ­tulo
			await printer.printText('Barcode Title\n');

			// Generar el cÃ³digo de barras utilizando JsBarcode
			const svgData = JsBarcode.generateSvg('123456789', {
				format: 'CODE128',
				displayValue: true,
			});

			// Imprimir el cÃ³digo de barras
			await printer.printImage(svgData);

			// Cortar el ticket
			await printer.cut();

			// Desconectar la impresora
			await printer.disconnect();
		} catch (error) {
			console.error(error);
		}
	}

	function imprimir2() {
		window.open(_URL + "/ge/bar/code2?code=" + codeBarraTemps + "&nombre=" + nombreBarraTemps + "&scal=" + $("#scalimg").val(), "_blank");


	}

	function imprimir() {
		window.open(_URL + "/ge/bar/code?code=" + codeBarraTemps + "&nombre=" + nombreBarraTemps + "&scal=" + $("#scalimg").val(), "_blank");

		/*   let printA4 = $(this).attr('href') */
		//printBarcode()
		/*let imgCodigo = $('#idCodigoBarras').attr('src');
        var myWindow = window.open("", "Image", "_blank");
        myWindow.document.write("<html><head><title></title></head><body style='width: 5cm; height: 2.5cm; padding: 0; margin: 0;'>");
        myWindow.document.write("<h3 style='font-size: 12px;text-align: center; margin: 0; padding: 0;'>"+nombreBarraTemps+"</h3>");
        myWindow.document.write("<img src='" + imgCodigo + "' style='width: 100%; height:   display: block; margin: 0 auto;'>");
        myWindow.document.write("</body></html>");
        myWindow.document.close();
        myWindow.focus();
        myWindow.print();
        myWindow.close();*/

		/* let imgCodigo = $('#idCodigoBarras').attr('src');
        let ticketContent = `
        <html>
        <head><title>Ticket de impresiÃ³n</title></head>
        <body style="width: 5cm; height: 2.5cm; padding: 0; margin: 0;">
          <h3 style="font-size: 12px;text-align: center; margin: 0; padding: 0;">"+nombreBarraTemps+"</h3>
          <img src="${imgCodigo}" style="width: 100%; height: calc(100% - 1em); display: block; margin: 0 auto;">
        </body>
        </html>
      `;

        qz.websocket.connect().then(function() {
            return qz.printers.find("XP-350B"); // Nombre de la impresora XPRINTER XP-350B
        }).then(function(printer) {
            let config = qz.configs.create(printer);
            return qz.print(config, [{ type: 'html', format: 'plain', data: ticketContent }]);
        }).then(function() {
            qz.websocket.disconnect();
        }).catch(function(err) {
            console.error(err);
        });*/

	}

	function abrirModalBarras(e, n = '') {
		e = e.trim();
		console.log(e);
		nombreBarraTemps = n
		codeBarraTemps = e
		/*  let  */
		//let codigoCompleto = e.toString().padStart(10, '0');
		JsBarcode("#idCodigoBarras", e);
		$('#modalCodigoBarras').modal('show')

	}

	var nombreBarraTemps = ''
	var codeBarraTemps = ''
	var datatable = ''
	var almacenCod = '1'
	var tcost = ''
	var stockFIL = 0
	$(document).ready(function() {


		const app = new Vue({
			el: "#conte-vue-modals",
			data: {
				t: 0,
				listaProd: [],
				Costotot: '',
				restock: {
					cod: '',
					cantidad: '',
				},
				reg: {
					descripcicon: "",
					precio: '0',
					costo: '0',
					cantidad: '0',
					codSunat: '',
					afecto: '0',
					ruc: '',
					razon: '',
					precioMayor: '',
					precioMenor: '',
					codigo: '',
					almacen: 1,
					unidades_por_caja: '1',
					volumen_unidad: '',
					id_unidad_derivada: null,
				},
				edt: {
					cod_prod: '',
					cod: '',
					descripcicon: "",
					precio: '0',
					costo: '0',
					codSunat: '',
					afecto: '0',
					usar_barra: '0',
					ruc: '',
					razon: '',
					precioMayor: '',
					precioMenor: '',
					precio2: '',
					precio3: '',
					precio4: '',
					precio_unidad: '',
					codigo: '',
					cantidad: '',
					unidades_por_caja: '1',
					volumen_unidad: '',
					id_unidad_derivada: null,
				},
				listaUnidades: [],
				nuevaUnidad: { nombre: '', descripcion: '' },
				listaIdsss: [],
				listaCatego: [],
				listaAlmacenes: [],
				listaEDT: [],
				addcatego: ''
			},
			methods: {
				nombreUnidadDerivada(id) {
					if (!id) return '';
					const u = this.listaUnidades.find(x => parseInt(x.id_unidad) === parseInt(id));
					return u ? u.nombre : '';
				},
				cargarUnidadesDerivadas() {
					const self = this;
					_ajax("/ajs/unidades-derivadas/listar", "POST", {}, function(resp) {
						if (resp.res) self.listaUnidades = resp.data;
					});
				},
				abrirNuevaUnidad() {
					this.nuevaUnidad = { nombre: '', descripcion: '' };
					$("#modal-nueva-unidad").modal("show");
				},
				guardarNuevaUnidad() {
					if (!this.nuevaUnidad.nombre.trim()) return;
					const self = this;
					_ajax("/ajs/unidades-derivadas/add", "POST", this.nuevaUnidad, function(resp) {
						if (resp.res) {
							$("#modal-nueva-unidad").modal("hide");
							self.cargarUnidadesDerivadas();
							// auto-seleccionar la unidad recién creada en el form activo
							self.$nextTick(() => {
								if ($("#modal-add-prod").hasClass("show")) {
									self.reg.id_unidad_derivada = resp.id_unidad;
								}
								if ($("#modal-edt-prod").hasClass("show")) {
									self.edt.id_unidad_derivada = resp.id_unidad;
								}
							});
							alertExito("Unidad creada");
						} else {
							alertAdvertencia(resp.error || "No se pudo crear");
						}
					});
				},
				AggCatego() {
					this.addcatego = "";
					$("#modal-categoria").modal("show")
				},
				GuardarCatego(nombre) {
					this.addcatego = nombre;
					const nuevaCategoria = {
						id: app._data.listaCatego.length + 1,
						nombre: this.addcatego
					};
					app._data.listaCatego.push(nuevaCategoria);
					$("#modal-categoria").modal("hide")
					console.log(nombre);
				},
				getCosto() {
					//alert('filtrar costo'+almacenCod);
					const data = {
						tipo: almacenCod,
						stock: ''
					}

					_ajax("/ajs/data/producto/costost", "POST", data,
						function(resp) {
							/*app._data.Costotot = resp.data.totald;						*/
							tcost = resp.data.totald;
							$(".tcost").text(tcost);
						}
					)

				},
				getCategoliasList() {
					const data = {
						tipo: 'lis'
					}
					_ajax("/ajs/data/producto/categorias", "POST", data,
						function(resp) {
							let resultados = [];
							for (let itemm of resp.data.detalles) {
								let resultado = {
									id: itemm.id_categoria,
									nombre: itemm.nombre
								};
								resultados.push(resultado);
							}

							let jsonDetalles = JSON.stringify(resultados);
							let otroJ = JSON.parse(jsonDetalles);
							console.log(resultados)
							app._data.listaCatego = otroJ;



						}
					)
				},
				getAllAlmacenes() {
					_ajax("/ajs/data/almacenes", "GET", null, function(resp) {
						let resultados = resp.data.map(item => ({
							id: item.id,
							name: item.name
						}));
						app._data.listaAlmacenes = resultados;
					});
				},
				agregarIds() {
					/*  console.log('nice'); */
					this.t = 5
					console.log(this.listaIdsss);
					this.listaIdsss.push({
						id: 20
					})
					console.log(this.listaIdsss);
				},
				agregarPrecios() {
					const data = {
						...this.edt
					}
					_ajax("/ajs/data/producto/edt/precios", "POST", data,
						function(resp) {
							console.log(resp);
							if (resp.res) {
								alertExito("Actualizado")
									.then(function() {
										location.reload()
									})
							} else {
								alertAdvertencia("No se pudo actualizar")
							}
						}
					)
				},
				changeStock(event) {
					$('.btnSeleccionarTodos').prop('checked', false);
					localStorage.removeItem('idChecks');
					console.log(event.target.value)
					datatable.destroy();

					stockFIL = event.target.value;
					datatable = $("#datatable_p").DataTable({
						order: [
							[0, 'desc']
						],
						"processing": true,
						"serverSide": true,
						"sAjaxSource": _URL + "/ajs/server/sider/productos?almacenId=" + almacenCod + "&stockF=" + stockFIL,

						columnDefs: [{
								"targets": 2,
								"render": function(data, type, row, meta) {
									return '<a href="javascript:abrirModalBarras(\'' + row[1] + '\',\'' + row[0] + '\')">' + row[2] + '</a>';
								}
							},
							{
								"targets": 8,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-ver-precios btn btn-sm btn-info"><i class="fas fa-eye"></i></button>`;
								}
							},
							{
								"targets": 3,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-ver-detalle btn btn-sm btn-primary"><i class="fas fa-eye"></i></button>`;
								}
							},
							{
								"targets": 4,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-vr btn btn-sm btn-primary"><i class="fas fa-list"></i></button>`;
								}
							},
							{
								"targets": 11,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-re-stock btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>`;
								}
							},
							{
								"targets": 12,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-edt btn btn-sm btn-info"><i class="fa fa-edit"></i></button>`;
								}
							},
							{
								"targets": 13,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<input type="checkbox" data-id="${row[0]}" class="btnCheckEliminar">`;
								}
							},
							{
								"targets": 14,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-reporte btn btn-sm btn-info"><i class="fa fa-file"></i></button>`;
								}
							},
						]
					})

					const data2 = {
						tipo: almacenCod,
						stock: stockFIL
					}

					_ajax("/ajs/data/producto/costost", "POST", data2,
						function(resp) {
							tcost = resp.data.totald;
							$(".tcost").text(tcost);
						})

					//alert(almacenCod+' '+stockFIL);

				},
				changeAlmacen(event) {
					/*    datatable.rows().remove().draw(true) */
					/*     $('.btnSeleccionarTodos').prop('checked', false); */
					/*   localStorage.removeItem('idChecks'); */

					$('.btnSeleccionarTodos').prop('checked', false);
					localStorage.removeItem('idChecks');
					console.log(event.target.value)
					/*    var data
                    				datatable.rows().remove().draw(true)*/
					datatable.destroy();
					/*   return */
					almacenCod = event.target.value;




					datatable = $("#datatable_p").DataTable({
						order: [
							[0, 'desc']
						],
						"processing": true,
						"serverSide": true,
						"sAjaxSource": _URL + "/ajs/server/sider/productos?almacenId=" + almacenCod,

						columnDefs: [{
								"targets": 2,
								"render": function(data, type, row, meta) {
									return '<a href="javascript:abrirModalBarras(\'' + row[1] + '\',\'' + row[0] + '\')">' + row[2] + '</a>';
								}
							},
							{
								"targets": 8,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-ver-precios btn btn-sm btn-info"><i class="fas fa-eye"></i></button>`;
								}
							},
							{
								"targets": 3,
								orderable: true,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-ver-detalle btn btn-sm btn-primary"><i class="fas fa-eye"></i></button>`;
								}
							},
							{
								"targets": 4,
								orderable: true,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-vr btn btn-sm btn-primary"><i class="fas fa-list"></i></button>`;
								}
							},
							{
								"targets": 11,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-re-stock btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>`;
								}
							},
							{
								"targets": 12,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-edt btn btn-sm btn-info"><i class="fa fa-edit"></i></button>`;
								}
							},
							{
								"targets": 13,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<input type="checkbox" data-id="${row[0]}" class="btnCheckEliminar">`;
								}
							},
							{
								"targets": 14,
								orderable: false,
								searchable: false,
								"render": function(data, type, row, meta) {
									return `<button data-item="${row[0]}" class="btn-reporte btn btn-sm btn-info"><i class="fa fa-file"></i></button>`;
								}
							},
						]
					})

					const data2 = {
						tipo: almacenCod,
						stock: ''
					}
					_ajax("/ajs/data/producto/costost", "POST", data2,
						function(resp) {
							tcost = resp.data.totald;
							$(".tcost").text(tcost);
						})



				},
				edtGenerarCodeBarra() {
					JsBarcode("#barcode", this.edt.cod_prod);
				},
				agregarListaImport() {
					//alert(JSON.stringify(this.listaProd))
					if (this.listaProd.length > 0) {
						_ajax("/ajs/data/producto/add/lista", "POST", {
								lista: JSON.stringify(this.listaProd)
							},
							function(resp) {
								console.log(resp);
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
				ChangeconsultarDocRUC() {
					console.log('buscando')
					if (this.reg.ruc.length == 11) {
						this.getInfoDoc2();
					} else {
						this.reg.ruc = ''
					}
				},

				consultarDocRUC() {
					if (this.reg.ruc.length == 11) {

						this.getInfoDoc2();
					} else if (this.edt.ruc.length == 11) {
						this.getInfoDoc3();
					} else {
						alertAdvertencia("El RUC es de 11 dÃ­gitos")
					}
				},
				getInfoDoc2() {
					$("#loader-menor").show();
					_ajax("/ajs/consulta/doc/cliente", "POST", {
							doc: this.reg.ruc
						},
						function(resp) {
							console.log(resp);
							if (resp.res) {
								app._data.reg.razon = resp.data.razon_social;
								app._data.reg.direccion = resp.data.direccion;
								app._data.reg.distrito = resp.data.distrito;
								app._data.reg.provincia = resp.data.provincia;
								app._data.reg.departamento = resp.data.departamento;
								app._data.reg.ubigeo = resp.data.ubigeo;
							} else {
								alertAdvertencia("Documento no encontrado")
							}
						}
					)
				},
				getInfoDoc3() {
					$("#loader-menor").show();
					_ajax("/ajs/consulta/doc/cliente", "POST", {
							doc: this.edt.ruc
						},
						function(resp) {
							console.log(resp);
							if (resp.res) {
								app._data.edt.razon = resp.data.razon_social;
								app._data.edt.direccion = resp.data.direccion;
								app._data.edt.distrito = resp.data.distrito;
								app._data.edt.provincia = resp.data.provincia;
								app._data.edt.departamento = resp.data.departamento;
								app._data.edt.ubigeo = resp.data.ubigeo;
							} else {
								alertAdvertencia("Documento no encontrado")
							}
						}
					)
				},
				eliminarItemTablaPro(index) {
					this.listaProd.splice(index, 1)
				},
				agregarStock() {
					const data = {
						...this.restock
					}
					_ajax("/ajs/data/producto/restock", "POST", data,
						function(resp) {
							console.log(resp);
							if (resp.res) {
								alertExito("Actualizado")
									.then(function() {
										location.reload()
									})
							} else {
								alertAdvertencia("No se pudo actualizar")
							}
						}
					)
				},
				actualizarProd() {
					const data = {
						...this.edt
					}

					_ajax("/ajs/data/producto/edt", "POST", data,
						function(resp) {
							console.log(resp);
							if (resp.res) {
								alertExito("Actualizado")
									.then(function() {
										location.reload()
									})
							} else {
								alertAdvertencia("No se pudo actualizar")
							}
						}
					)
				},
				buscarProveedor(tipo) {
					console.log('geanmarco')
					$(".inputSearch").autocomplete({
						source: _URL + "/proveedores/search",
						minLength: 1,
						select: function(event, ui) {
							var label = ui.item.label.split(' | ')[1];
							$(".inputSearch").val(ui.item.value)
							$(".razon_social").val(label)
							if (tipo === 'insert') {
								app._data.reg.razon = label;
								app._data.reg.ruc = ui.item.value;

								app._data.edt.razon = '';
								app._data.edt.ruc = '';
							}
							if (tipo === 'update') {
								app._data.edt.razon = label;
								app._data.edt.ruc = ui.item.value;

								app._data.reg.razon = '';
								app._data.reg.ruc = '';
							}
						},
						response: function(event, ui) {}
					});
				},
				agregarProd() {
					const data = {
						...this.reg
					};

					console.log("?? ~ file: almacen-productos.php:1150 ~ agregarProd data: ", data);

					$.ajax({
						url: _URL + "/ajs/data/producto/add",
						type: "POST",
						data: data,
						dataType: "json", // Fuerza a jQuery a interpretar la respuesta como JSON
						success: function(resp) {
							console.log("Respuesta del servidor:", resp);

							if (resp.res) {
								alertExito("Agregado")
									.then(function() {
										location.reload();
									});
							} else if (resp.error) {
								alertAdvertencia(resp.error);
							} else {
								alertAdvertencia("No se pudo agregar el producto.");
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							// Si el servidor devuelve un error 500, aquí podrás ver el mensaje real
							console.error("Error técnico detectado:", textStatus, errorThrown);
							console.log("Cuerpo de la respuesta fallida:", jqXHR.responseText);

							alertAdvertencia("Error crítico del servidor. Revisa la consola para más detalles.");
						}
					});
				},
				setInfo(data) {
					$("#modal-edt-prod").modal("show");

					this.edt.cod_prod = data.cod_barra
					this.edt.usar_barra = data.usar_barra
					this.edt.cod = data.id_producto
					this.edt.descripcicon = data.descripcion
					this.edt.precio = data.precio_unidad
					this.edt.costo = parseFloat(data.costult + "").toFixed(2)
					this.edt.codSunat = data.codsunat
					this.edt.afecto = data.iscbp
					this.edt.precioMayor = data.precio_mayor
					this.edt.precioMenor = data.precio_menor
					this.edt.razon = data.razon_social
					this.edt.ruc = data.ruc
					this.edt.codigo = data.codigo
					this.edt.cantidad = data.cantidad
					this.edt.serie_producto = data.serie_producto
					this.edt.unidades_por_caja = data.unidades_por_caja ?? '1'
					this.edt.volumen_unidad = data.volumen_unidad ?? ''
					this.edt.id_unidad_derivada = data.id_unidad_derivada ? parseInt(data.id_unidad_derivada) : null

				},
				onlyNumber($event) {
					//console.log($event.keyCode); //keyCodes value
					let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
					if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
						$event.preventDefault();
					}
				}
			}
		})
		/*  localStorage.removeItem('idChecks'); */
		datatable = $("#datatable_p").DataTable({
			order: [
				[0, 'desc']
			],
			"processing": true,

			"serverSide": true,
			"sAjaxSource": _URL + "/ajs/server/sider/productos?almacenId=" + almacenCod,

			columnDefs: [{
					"targets": 2,
					"render": function(data, type, row, meta) {
						return '<a href="javascript:abrirModalBarras(\'' + row[1] + '\',\'' + row[0] + '\')">' + row[2] + '</a>';
					}
				},
				{
					"targets": 8,
					orderable: false,
					searchable: false,
					"render": function(data, type, row, meta) {
						return `<button data-item="${row[0]}" class="btn-ver-precios btn btn-sm btn-info"><i class="fas fa-eye"></i></button>`;
					}
				},
				{
					"targets": 3,
					orderable: true,
					searchable: false,
					"render": function(data, type, row, meta) {
						return `<button data-item="${row[0]}" class="btn-ver-detalle btn btn-sm btn-primary"><i class="fas fa-eye"></i></button>`;
					}
				},
				{
					"targets": 4,
					orderable: true,
					searchable: false,

					"render": function(data, type, row, meta) {
						return `<button data-item="${row[0]}" class="btn-vr btn btn-sm btn-primary"><i class="fas fa-list"></i></button>`;
					}
				},

				{
					"targets": 11,
					orderable: false,
					searchable: false,
					"render": function(data, type, row, meta) {
						return `<button data-item="${row[0]}" class="btn-re-stock btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>`;
					}
				},
				{
					"targets": 12,
					orderable: false,
					searchable: false,
					"render": function(data, type, row, meta) {
						return `<button data-item="${row[0]}" class="btn-edt btn btn-sm btn-info"><i class="fa fa-edit"></i></button>`;
					}
				},
				{
					"targets": 13,
					orderable: false,
					searchable: false,
					"render": function(data, type, row, meta) {
						return `<input type="checkbox" data-id="${row[0]}" class="btnCheckEliminar">`;
					}
				},
				{
					"targets": 14,
					orderable: false,
					searchable: false,
					"render": function(data, type, row, meta) {
						return `<button data-item="${row[0]}" class="btn-reporte btn btn-sm btn-info"><i class="fa fa-file"></i></button>`;
					}
				},

			]


		})



		$("#file-import-exel").change(function() {
			console.log("aaaaaaaa")
			if ($("#file-import-exel").val().length > 0) {
				var fd = new FormData();
				fd.append('file', $("#file-import-exel")[0].files[0]);
				$.ajax({
					type: 'POST',
					url: _URL + '/ajs/data/producto/add/exel',
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
						resp = JSON.parse(resp)
						if (resp.res) {
							var bloc = true;
							var listaTemp = [];
							resp.data.forEach(function(el) {
								if (!bloc) {
									listaTemp.push({
										descripcicon: el[0],
										cantidad: el[1],
										precio: el[5],
										precio2: el[6],
										precio3: el[7],
										precio4: el[4],
										costo: el[2],
										codSunat: el[8],
										almacen: el[9],
										afecto: false,
										precio_unidad: el[3],
										codigoProd: el[10]
									})
								}
								bloc = false
							})
							app._data.listaProd = listaTemp

							$("#importarModal").modal("hide")
							$("#modal-lista-productos").modal("show")

						} else {
							alertAdvertencia("No se pudo subir el Archivo")
						}
						$("#file-import-exel").val("")

					}
				})
			}
		})

		app.getCategoliasList();
		app.cargarUnidadesDerivadas();
		app.getAllAlmacenes();
		app.getCosto();
		var arrayIdsOkUsar = []


		$("#datatable_p").on("click", ".btn-re-stock", function(evt) {
			const cod = $(evt.currentTarget).attr("data-item");
			app._data.restock.cod = cod
			app._data.restock.cantidad = ''
			$("#modal-restock").modal("show");

		})
		$("#generarreporteProd").click(() => {
			console.log("---------------------------------------")
			const anioREd = $("#anioreporEFG").val()
			const messREd = parseInt($("#mesreprEFG").val())
			const diaRed = $("#diareporEfghg").val().length > 0 ? parseInt($("#diareporEfghg").val()) : 'nn'
			window.open(_URL +
				`/reporte/productos/pdf/${codProdT}?fecha=${anioREd}${messREd}-${diaRed}`)
			$("#modal-prodEreport").modal("hide");
		})

		$("#datatable_p").on("click", ".btn-reporte", function(evt) {
			const cod = $(evt.currentTarget).attr("data-item");

			codProdT = cod
			$("#modal-prodEreport").modal("show");
			//console.log(cod);

			//window.open(_URL + `/reporte/productos/pdf/${cod}`)
			/*  app._data.restock.cod = cod
	             app._data.restock.cantidad = ''
      	       $("#modal-restock").modal("show"); */
		})


		$("#datatable_p").on("click", ".btn-ver-precios", function(evt) {
			const cod = $(evt.currentTarget).attr("data-item");
			console.log(cod);
			$("#modal-precios").modal("show");
			_ajax("/ajs/cargar/productos/precios", "POST", {
					cod
				},
				function(resp) {
					console.log(resp);
					$("#modal-precios").modal("show");
					/*  $('#precio1').val(resp.precio)
                     $('#precio2').val(resp.precio2)
                     $('#precio3').val(resp.precio3)
                     isNaN(resp.precio4) ? $('#precio4').val('') : parseFloat(resp.precio4 + "").toFixed(2) */

					app._data.edt.precio = resp.pvip == null ? parseFloat(0 + "").toFixed(0) : resp.pvip
					app._data.edt.precio2 = resp.precio2 == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.precio2 + "").toFixed(0)
					/*    .toFixed(2) */
					app._data.edt.precio3 = resp.precio3 == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.precio3 + "").toFixed(0)
					app._data.edt.precio4 = resp.pdistri == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.pdistri + "").toFixed(0)
					app._data.edt.precio_unidad = resp.punidad == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.punidad + "").toFixed(0)

					$('#precio1').val(resp.pvip == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.pvip + "").toFixed(0))
					$('#precio2').val(resp.precio2 == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.precio2 + "").toFixed(0))
					$('#precio3').val(resp.precio3 == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.precio3 + "").toFixed(0))
					$('#precio4').val(resp.pdistri == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.pdistri + "").toFixed(0))
					$('#precio_unidad').val(resp.punidad == null ? parseFloat(0 + "").toFixed(0) : parseFloat(resp.punidad + "").toFixed(0))
					app._data.edt.cod_prod = cod
					/* if (resp.res) {


                    } */
				}
			)

		})

		$("#datatable_p").on("click", ".btn-ver-detalle", function(evt) {
			const cod = $(evt.currentTarget).attr("data-item");
			/*   console.log(cod); */
			_ajax("/ajs/data/producto/infos", "POST", {
					cod
				},
				function(resp) {
					if (resp.res) {
						$("#modal_ver_detalle").modal("show")
						$(".titulodet").text(resp.data.descripcion)
						let resultados = [];
						for (let itemm of resp.data.detalles) {
							let resultado = {
								id: itemm.id_producto,
								detaserie: itemm.serie_producto
							};
							resultados.push(resultado);
						}
						let jsonDetalles = JSON.stringify(resultados);
						let otroJ = JSON.parse(jsonDetalles);
						datatableProductoDetalle = $("#datatableProductoDetalle").DataTable({
							paging: true,
							bFilter: true,
							ordering: true,
							searching: true,
							destroy: true,
							language: {
								url: "ServerSide/Spanish.json",
							},
							data: otroJ,
							columns: [{
									data: "id",
									class: "text-center",
								},
								{
									data: "detaserie",
									class: "text-center",
								}
							],
						});
						datatableProductoDetalle.on('order.dt search.dt', function() {
							datatableProductoDetalle.column(0, {
								search: 'applied',
								order: 'applied'
							}).nodes().each(function(cell, i) {
								cell.innerHTML = '<span style="display: block;margin: auto;text-align: center;" >' + (i + 1) + '</span>';
							});
						}).draw();
					}
				}
			)
		});

		$("#datatable_p").on("click", ".btn-vr", function(evt) {
			const cod = $(evt.currentTarget).attr("data-item");

			/*   console.log(cod);  */
			_ajax("/ajs/data/producto/histo", "POST", {
					cod
				},
				function(resp) {
					if (resp.res) {
						$(".titulodett").text(resp.data.descripcion)
						let resultados = [];

						try {
							if (!resp?.venta || !Array.isArray(resp.venta)) {
								console.log('Datos de venta inv lidos');
							}

							resultados = resp.venta.map(item => ({
								index: resp.venta.indexOf(item),
								fechae: item.fecha_emision,
								documento: item.nfactura,
								cliente: item.client,
								cantidad: item.cantidad,
								precio: item.pprecio,
								total: item.total
							}));

							if (resultados.length === 0) {
								throw new Error('No hay ventas para mostrar');
							}

							$('#Hproductoventa').DataTable({
								destroy: true,
								paging: true,
								bFilter: true,
								ordering: true,
								searching: true,
								language: {
									url: "ServerSide/Spanish.json"
								},
								data: resultados,
								columns: [{
										data: 'index',
										class: 'text-center',
										render: function(data) {
											return `<span style="display: block;margin: auto;text-align: center;">${data + 1}</span>`;
										}
									},
									{
										data: 'fechae',
										class: 'text-center'
									},
									{
										data: 'documento',
										class: 'text-center'
									},
									{
										data: 'cliente',
										class: 'text-center'
									},
									{
										data: 'cantidad',
										class: 'text-center'
									},
									{
										data: 'precio',
										class: 'text-center'
									},
									{
										data: 'total',
										class: 'text-center'
									}
								]
							});
						} catch (error) {
							console.error('Error:', error.message);
							// Manejar el error seg n necesites
						}

						let resultados2 = [];
						try {
							if (!resp?.compra || !Array.isArray(resp.compra)) {
								console.log('Datos de compra inv lidos');
							}

							resultados2 = resp.compra.map(item => ({
								index: resp.compra.indexOf(item),
								fechae: item.fecha_emision,
								documento: item.nfactura,
								cliente: item.provee,
								cantidad: item.cantidad,
								precio: item.pprecio,
								total: item.total
							}));

							if (resultados2.length === 0) {
								throw new Error('No hay compras para mostrar');
							}

							$('#Hproductocompra').DataTable({
								destroy: true,
								paging: true,
								bFilter: true,
								ordering: true,
								searching: true,
								language: {
									url: "ServerSide/Spanish.json"
								},
								data: resultados2,
								columns: [{
										data: 'index',
										class: 'text-center',
										render: function(data) {
											return `<span style="display: block;margin: auto;text-align: center;">${data + 1}</span>`;
										}
									},
									{
										data: 'fechae',
										class: 'text-center'
									},
									{
										data: 'documento',
										class: 'text-center'
									},
									{
										data: 'cliente',
										class: 'text-center'
									},
									{
										data: 'cantidad',
										class: 'text-center'
									},
									{
										data: 'precio',
										class: 'text-center'
									},
									{
										data: 'total',
										class: 'text-center'
									}
								]
							});
						} catch (error) {
							console.error('Error:', error.message);
							// Manejar el error seg n necesites
						}

						$("#modal_ver_detalle2").modal("show")

					}
				}
			)
		});


		$("#datatable_p").on("click", ".btn-edt", function(evt) {
			const cod = $(evt.currentTarget).attr("data-item");
			/*   console.log(cod); */
			_ajax("/ajs/data/producto/info", "POST", {
					cod
				},
				function(resp) {
					console.log(resp);
					if (resp.res) {
						app.setInfo(resp.data)
						let resultados = [];
						for (let itemm of resp.data.detalles) {
							let resultado = {
								id: itemm.id_categoria,
								nombre: itemm.nombre
							};
							resultados.push(resultado);
						}

						let jsonDetalles = JSON.stringify(resultados);
						let otroJ = JSON.parse(jsonDetalles);
						console.log(resultados)
						app._data.listaEDT = otroJ;
					} else {
						alertAdvertencia("Informacion no encontrada")
					}
				}
			)
		})


		$("#datatable_p").on("click", ".btnCheckEliminar", function(evt) {


			const cod = $(evt.currentTarget).attr("data-id");
			/*   console.log($('.btnCheckEliminar').checked); */
			let idCheckTrue = false
			/* console.log($(evt.currentTarget).is(":checked")); */
			if ($(evt.currentTarget).is(':checked')) {
				if (arrayIdsOkUsar.findIndex(e => e.id == cod) == -1) {
					arrayIdsOkUsar.push({
						id: cod
					});
				}
			}
			/*  let arrayCheck = [] */
			else {
				const indexArray = arrayIdsOkUsar.findIndex(e => e.id == cod)
				if (indexArray > -1) {
					arrayIdsOkUsar.splice(indexArray, 1)
				}
			}
			/*  $("input:checkbox[class=btnCheckEliminar]:checked").each(function() {

             }); */
			localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));
			/*    console.log(arrayCheck); */
		})


		$('.btnBorrar').click(function() {
			console.log(localStorage.getItem('idChecks'));
			let ids = localStorage.getItem('idChecks')

			let arrayId = JSON.parse(ids)
			Swal.fire({
				title: 'Desea borrar estos productos?',
				showDenyButton: true,

				confirmButtonText: 'Si',
				denyButtonText: `No`,
			}).then((result) => {
				/* Read more about isConfirmed, isDenied below */
				if (result.isConfirmed) {
					if (localStorage.getItem("idChecks") !== null) {
						_ajax("/ajs/data/producto/delete", "POST", {
								arrayId
							},
							function(resp) {
								console.log(resp);
								if (resp.res) {
									localStorage.removeItem('idChecks');
									Swal.fire('Buen trabajo',
										'Productos borrados exitosamente',
										'success', {}).then((result) => {

										location.reload();
									});
								} else {
									alertAdvertencia("Ocurrio un error")
								}
							}
						)
					} else {
						alertAdvertencia("Seleccione productos")
					}
					/*   */
				}
			})

		})


		$('.btnSeleccionarTodos').click(function() {

			/*   var p = datatable.rows({
                page: 'current'
            }).nodes();
            let array = []
            for (let index = 0; index < 10; index++) {
                array.push(p[index])
            }
            for (let index = 0; index < array.length; index++) {
                if (array[index] != undefined) {
                    let element = array[index];
                    let data = element.children[12].children[0]
                    $(data).prop('checked', true);
                    let ids = $(data).attr("data-id")
                    if (arrayIdsOkUsar.findIndex(e => e.id == ids) == -1) {
                        arrayIdsOkUsar.push({
                            id: ids
                        });
                    }
                    console.log(data);
                    console.log(ids);
                    console.log(arrayIdsOkUsar);
                    app._data.listaIds = arrayIdsOkUsar
                    console.log(app._data.listaIds);
                    localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));
                    console.log(localStorage.getItem('idChecks'));
                }
            }
 */
			/*   return */

			var p = datatable.rows({
				page: 'current'
			}).nodes();


			/*    console.log(p);
               return */
			arrayIdsOkUsar = []
			if (this.checked) {
				const listaChek = $('.btnCheckEliminar')
				for (let item of listaChek) {
					const itemE = $(item)
					arrayIdsOkUsar.push({
						id: itemE.attr("data-id")
					})
					itemE.prop('checked', true);
				}
			} else {
				const listaChek = $('.btnCheckEliminar')
				for (let item of listaChek) {
					const itemE = $(item)
					itemE.prop('checked', false);
				}
			}
			localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));

			return
			if (this.checked) {
				/*   localStorage.removeItem('idChecks'); */
				var p = datatable.rows({
					page: 'current'
				}).nodes();
				let array = []
				for (let index = 0; index < 10; index++) {
					array.push(p[index])
				}
				for (let index = 0; index < array.length; index++) {
					if (array[index] != undefined) {
						let element = array[index];
						let data = element.children[12].children[0]
						$(data).prop('checked', true);
						let ids = $(data).attr("data-id")
						if (arrayIdsOkUsar.findIndex(e => e.id == ids) == -1) {
							arrayIdsOkUsar.push({
								id: ids
							});
						}
						console.log(data);
						console.log(ids);
						app._data.listaIds = arrayIdsOkUsar
						localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));
						console.log(localStorage.getItem('idChecks'));
					}
				}

			} else {
				localStorage.removeItem('idChecks');
				var p = datatable.rows({
					page: 'current'
				}).nodes();
				let array = []
				for (let index = 0; index < 10; index++) {
					array.push(p[index])
				}
				for (let index = 0; index < array.length; index++) {
					if (array[index] != undefined) {
						let element = array[index];
						let data = element.children[12].children[0]
						$(data).prop('checked', false);
						/*   let ids = $(data).attr("data-id") */
					}
				}
				/*   localStorage.removeItem('idChecks'); */
			}


		})
	})
</script>