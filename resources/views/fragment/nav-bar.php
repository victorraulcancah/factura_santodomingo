<?php
/*

if (isset($_SESSION['rol'])){
    $id_role = $_SESSION['rol'];
} else {

echo "ERROR";
}

*/
?>

<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">Main</li>

                <li class="item-nv-menu">
                    <a href="/" class="waves-effect menu-link">
                        <i class="ti-home"></i><span hidden class="badge rounded-pill bg-primary float-end">2</span>
                        <span>DASHBOARD</span>
                    </a>
                </li>

                <li class="menu-title">Modulos</li>
                <?php if ($id_role == 1) : ?>
                    <li>
                        <a href="/usuarios" class=" waves-effect menu-link">
                            <i class="fa fa-users"></i>
                            <span>USUARIOS</span>
                        </a>
                    </li>
                    <li>
                        <a href="/pagos/vendedores" class=" waves-effect menu-link">
                            <i class="fa fa-calculator"></i>
                            <span>PLANILLA VENDEDORES</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="ti-package"></i>
                            <span>FACTURACION</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/ventas" class=" menu-link">Ventas</a></li>
                            <li><a href="/guias/remision" class=" menu-link">Guias Remision</a></li>
                            <li><a href="/nota/electronica/lista" class=" menu-link">Notas Electronicas</a></li>
                        </ul>
                    </li>


                    <li>
                        <a href="/cotizaciones" class=" waves-effect menu-link">
                            <i class="fa fa-align-justify"></i>
                            <span>COTIZACIONES</span>
                        </a>
                    </li>
                    <li>
                        <a href="/cobranzas" class=" waves-effect menu-link">
                            <i class="fa fa-money-bill"></i>
                            <span>CUENTAS POR COBRAR</span>
                        </a>
                    </li>
                    <li>
                        <a href="/pagos" class=" waves-effect menu-link">
                            <i class="fa fa-money-bill"></i>
                            <span>CUENTAS POR PAGAR</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="ti-package"></i>
                            <span>CAJA</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/cajaRegistros" class=" menu-link">Registros</a></li>
                            <li><a href="/caja/flujo" class=" menu-link">Caja Chica</a></li>

                        </ul>
                    </li>
                    <li>
                        <a href="/compras" class=" menu-link">
                            <i class="ti-calendar"></i>
                            <span>COMPRAS</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="ti-view-grid"></i>
                            <span>ALMACEN</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/almacen/productos" class=" menu-link">Kardex</a></li>
                            <li><a href="/almacen/unidades-derivadas" class=" menu-link">Unidades Derivadas</a></li>
                        </ul>
				<!--
				<ul class="sub-menu" aria-expanded="false">
                            <li><a href="/generarPDF" class=" menu-link">Generar PDF</a>
                            </li>
                        </ul>
				-->
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/almacen/intercambio/productos" class=" menu-link">Intecambio Productos</a>
                            </li>
                        </ul>
			      

                    </li>
                    <li>
                        <a href="/clientes" class=" waves-effect menu-link">
                            <i class="ti-calendar"></i>
                            <span>CLIENTES</span>
                        </a>
                    </li>
                    <li>
                        <a href="/proveedores" class=" waves-effect menu-link">
                            <i class="ti-calendar"></i>
                            <span>PROVEEDORES</span>
                        </a>
                    </li>
		     <li>
                        <a href="/utilidadVen" class=" waves-effect menu-link">
                            <i class="ti-calendar"></i>
                            <span>UTILIDAD</span>
                        </a>
                    </li>
	
                <?php endif; ?>

                <?php if ($id_role == 2) : ?>
                    <li>
                        <a href="/cotizaciones" class=" waves-effect menu-link">
                            <i class="fa fa-align-justify"></i>
                            <span>COTIZACIONES</span>
                        </a>
                    </li>
                <?php endif; ?>
		 <?php if ($id_role == 3) : ?>
		   
		      

		      <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="ti-package"></i>
                            <span>FACTURACION</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/ventas" class=" menu-link">Ventas</a></li>
                            <li><a href="/guias/remision" class=" menu-link">Guias Remision</a></li>
                            <li><a href="/nota/electronica/lista" class=" menu-link">Notas Electronicas</a></li>
                        </ul>
                    </li>
		      <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="ti-package"></i>
                            <span>CAJA</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/cajaRegistros" class=" menu-link">Registros</a></li>
                            <li><a href="/caja/flujo" class=" menu-link">Caja Chica</a></li>

                        </ul>
                    </li>

 
                <?php endif; ?>
	         <?php if ($id_role == 4) : ?>
		     <li>
                        <a href="/compras" class=" menu-link">
                            <i class="ti-calendar"></i>
                            <span>COMPRAS</span>
                        </a>
                    </li>
	
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="ti-view-grid"></i>
                            <span>ALMACEN</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/almacen/productos" class=" menu-link">Kardex</a></li>
                            <li><a href="/almacen/unidades-derivadas" class=" menu-link">Unidades Derivadas</a></li>
                        </ul>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="/almacen/intercambio/productos" class=" menu-link">Intecambio Productos</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>



            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>