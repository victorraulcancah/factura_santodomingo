<?php
$conexion = (new Conexion())->getConexion();
$empresa = $_SESSION['id_empresa'];

?>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Ventas</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Utilidad</li>
            </ol>
        </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">

            </div>
        </div>
    </div>
</div>

<div class="row">
     <input type="hidden" id="emp"  value="<?=$empresa;?>">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
		<div class="row">
		    <div class="col-8">
	                <h4 class="card-title">Utilidades detallado</h4>
                   </div>
		    <div class="col-4 text-center">
	                <h4 class="card-title totalv" style="color:black; font-weight: bold;"></h4>
                   </div>

               </div>

                <div class="card-title-desc">
			<div class="row">
				 <div class="form-group col-lg-3 col-xs-12 col-md-3">
					&nbsp;
				</div>

				  <div class="form-group col-lg-3 col-xs-12 col-md-3">
		                 <label>DESDE</label>
               		  <input type="date" class="form-control" name="fecin" id="fecin" required>
	                        </div>
				  <div class="form-group col-lg-3 col-xs-12 col-md-3">
		                 <label>HASTA</label>
               		  <input type="date" class="form-control" name="fefin" id="fefin" required>
	                        </div>

				 <div class="form-group col-lg-3 col-xs-12 col-md-3">
					<button id="btnFil" class="btn btm-sm btn-primary mt-3 ">Buscar</button>
			               <button id="btnReset" class="btn btm-sm btn-danger mt-3">Cancelar</button>
				</div>


			</div>
			
                </div>
                <div class="">
                    <table id="tabla-registros" class="table table-sm table-bordered text-center" >
                        <thead>
                        <tr>
                            <th></th>
                            <th>Fecha</th>
                            <th>Comprobante</th>
                            <th>Serie</th>
                            <th style="width: 15%;">Cliente</th>
                            <th style="width: 25%;">Detalle</th>
			      <th>Cantidad</th>	
			      <th>Precio Compra</th>	
			      <th>Precio Venta</th>	
			      <th>Ganancia Unidad</th>	
			      <th>Ganancia Total</th>	
                        </tr>
                        </thead>
                        <tbody></tbody>
			  <tfoot align="center">
                            <tr>  
                            <td colspan="9" align="right" style="color:black; font-weight: bold;">MARGEN DE UTILIDAD TOTAL:</td>
                            <td colspan="1" class="totalu" style="color:black; font-weight: bold;"><?='0';?></td>
                            <td colspan="1" class="totalt" style="color:black; font-weight: bold;"><?='0';?></td>
                            </tr>
                        </tfoot>  
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
     var emp, fecin, fefin ;
     emp = $.trim($('#emp').val());


    $(document).on("click", "#btnReset", function(e){
    	e.preventDefault();
	$('#fecin').val("");
	$('#fefin').val("");           
    }); 
	

     $(document).on("click", "#btnFil", function(e){
    	e.preventDefault();
       fecin = $.trim($('#fecin').val());
       fefin = $.trim($('#fefin').val());

	if(fecin !='' && fefin !='') {
	 	CargarDatos();
         } else {
		alert('Debe seleccionar las fechas');
      	 }
    }); 
	
      
	
     CargarDatos();	
     function CargarDatos(){
       fecin = $.trim($('#fecin').val());
       fefin = $.trim($('#fefin').val());

        $('#tabla-registros').DataTable().clear().destroy();
		_ajax("/ajs/ventas/filtro", "POST", {
					fecin:fecin, 
					fefin:fefin, 
					emp:emp 
				},
				function(resp) {
					if (resp.res) {
						
						let resultados = [];
						for (let itemm of resp.data.detalles) {
							let resultado = {
								id: itemm.id_venta,
								fechae: itemm.fecha_emision,
								comprob: itemm.comprobante,
								nseries: itemm.series,
								nruc: itemm.ruc,
								ndescri: itemm.descripcion,
								ncanti: itemm.cantidad,
								mcosto: itemm.costo,			
								mpreci: itemm.precio,
								munida: itemm.unidad,
								mtotal: itemm.total							
							};
							resultados.push(resultado);
						}

						   $(".totalv").text(" T VENTA:"+resp.venta);
					          $(".totalt").text(" "+resp.total);
					          $(".totalu").text(" "+resp.util);
						
						let jsonDetalles = JSON.stringify(resultados);
						let otroJ = JSON.parse(jsonDetalles);
						let listaEDT = otroJ;
						datatableProductoDetalle = $("#tabla-registros").DataTable({
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
									data: "fechae",
									class: "text-center",
								},
								
								{
									data: "comprob",
									class: "text-center",
								},
								{
									data: "nseries",
									class: "text-center",
								},
								{
									data: "nruc",
									class: "text-center",
								},
								{
									data: "ndescri",
									class: "text-center",
								},
								{
									data: "ncanti",
									class: "text-center",
								}, 
{
									data: "mcosto",
									class: "text-center",
								}, 

{
									data: "mpreci",
									class: "text-center",
								}, 
{
									data: "munida",
									class: "text-center",
								}, 

{
									data: "mtotal",
									class: "text-center",
								}, 



							],
						});



					} else {
						alertAdvertencia("Informacion no encontrada")
					}
	   });
	
    }
	
    })


</script>