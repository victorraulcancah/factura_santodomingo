<?php

    Route::get('/login', "ViewController@login");
    Route::get('/logout', "UsuarioController@logout");
    Route::get('/ge/bar/code', "ConsultaDelcontroller@generarBarCode");
    Route::get('/ge/bar/code2', "ConsultaDelcontroller@generarBarCode2");


    #buscar proveedors
    Route::get("/proveedores/search", "ProveedoresController@search");


    Route::get('/venta/comprobante/pdf/ma4/:venta', "ReportesVentaController@comprobanteVentaMa4");
    Route::get('/venta/comprobante/pdf/ma4/:venta/:nombre', "ReportesVentaController@comprobanteVentaMa4");
    Route::get('/venta/comprobante/pdf/:venta', "ReportesVentaController@comprobanteVenta");
    Route::get('/venta/comprobante/pdf/:venta/:nombre', "ReportesVentaController@comprobanteVenta");
    Route::get('/venta/comprobante/pdfd/:venta/:nombre', "ReportesVentaController@comprobanteVentaBinario");
    Route::get('/guia/remision/pdf/:guia', 'ReportesVentaController@guiaRemision');
    Route::get('/nota/electronica/pdf/:nota', 'ReportesVentaController@comprobanteNotaE');
    Route::get('/nota/electronica/pdf/:nota/:nombre', 'ReportesVentaController@comprobanteNotaE');
    Route::get('/guia/remision/pdf/:guia/:nombre', 'ReportesVentaController@guiaRemision');


//pdf para voucher de venta
    /* Route::get('/venta/comprobante/pdf/:voucher',"ReportesVentaController@comprobanteVenta"); */
    Route::get("/r/cotizaciones/reporte/:coti", "ReportesVentaController@comprobanteCotizacion");
    Route::get("/reporte/ventas/pdf/:periodo", "GeneradoresController@reportePeriodoVenta");
    Route::get("/reporte/ventas/producto/lista/pdf/", "ReportesVentaController@reporteVentaPorProducto");

    Route::get('/venta/pdf/voucher/8cm/:voucher', "ReportesVentaController@imprimirvoucher8cm");
    Route::get('/venta/pdf/voucher/8cm/:voucher/:nom', "ReportesVentaController@imprimirvoucher8cm");
    Route::get('/venta/pdf/voucher/5.6cm/:voucher', "ReportesVentaController@imprimirvoucher5_6cm");
    Route::get('/venta/pdf/voucher/5.6cm/:voucher/:nom', "ReportesVentaController@imprimirvoucher5_6cm");


    Route::get("/escanear/codigobarra/:empresa/:sucursal", "ViewController@escanearBarra");


    Route::baseStatic("ViewController@index", [ValidarTokenMiddleware::class]);

    Route::postBase("/", "FragmentController@home");
    Route::postBase("/administrarempresas", "FragmentController@adminEmpresas");
    Route::postBase("/administrarempresas/ventas/:empresa", "FragmentController@adminEmpresasVentas");
    Route::postBase("/pagos", "FragmentController@pagos");

    Route::postBase("/caja/flujo", "FragmentController@cajaFlujo");
    Route::postBase("/cajaRegistros", "FragmentController@cajaRegistros");
    Route::postBase("/utilidadVen", "FragmentController@utilidadVen");

    Route::postBase("/generarPDF", "FragmentController@generarPDF");
	

    Route::postBase("/compras", "FragmentController@compras");
    Route::postBase("/compras/add", "FragmentController@comprasAdd");

    Route::postBase("/cobranzas", "FragmentController@cobranzas");

    #USUARIOS
    Route::postBase("/usuarios", "FragmentController@usuarios");


    Route::postBase("/cotizaciones", "FragmentController@cotizaciones");
    Route::postBase("/cotizaciones/add", "FragmentController@cotizacionesAdd");
    Route::postBase("/cotizaciones/edt/:coti", "FragmentController@cotizacionesEdt");

    Route::postBase("/nota/electronica", "FragmentController@notaElectronica");
    Route::postBase("/nota/electronica/lista", "FragmentController@notaElectronicaLista");

    Route::postBase("/almacen/productos", "FragmentController@almacenProductos");
    Route::postBase("/almacen/productos/add", "FragmentController@productoAdd");
    Route::postBase("/test", "FragmentController@test");

    Route::postBase("/almacen/intercambio/productos", "FragmentController@almacenIntercambioProductos");
    /* Route::postBase("/almacen/intercambio/productos/add","FragmentController@productoAdd"); */

    Route::postBase("/calendario", "FragmentController@calendarioCliente");
    Route::postBase("/clientes", "FragmentController@clientesLista");
    Route::postBase("/proveedores", "FragmentController@proveedoresLista");
    Route::postBase("/ventas", "FragmentController@ventas");
    Route::postBase("/guias/remision", "FragmentController@guiaRemision");
    Route::postBase("/ventas/productos", "FragmentController@ventasProductos");
    Route::postBase("/ventas/servicios", "FragmentController@ventasServicios");
    Route::postBase("/guia/remision/registrar", "FragmentController@guiaRemisionAdd");

    /* Route::postBase("/guia/remision/registrar/coti","FragmentController@guiaRemisionAddByCoti"); */
    Route::postBase("/cuentas/cobrar", "FragmentController@cuentasPorCobrar");


    Route::postBase("/editar-venta-producto/:idVenta", "FragmentController@editarVentaProducto");
    Route::postBase("/editar-venta-servicio/:idVenta", "FragmentController@editarVentaServicio");


    #KArdex
    Route::get('/reporte/balance', 'GenerarReporte@balance');
    Route::get('/reporte/kardex_balance', 'GenerarReporte@kardex_balance');
    Route::get('/reporte/compras_balance', 'GenerarReporte@compras_balance');
    Route::get('/reporte/ventas_balance', 'GenerarReporte@ventas_balance');

    Route::get("/reporte/excel/:fecha", "GenerarReporte@generarExcel");
    Route::get("/reporte/producto/excel", "GenerarReporte@generarExcelProducto");

    Route::get("/reporte/rvta/excel/:fecha", "GenerarReporte@generarExcelRVTA");

    /* Route::get("/reporte/excel/test2","GenerarReporte@testExcel"); */

    Route::get("/reporte/ingresos/egresos/:id", "GenerarReporte@ingresosEgresos");


    Route::get("/reporte/cliente/:id", "ReportesVentaController@reporteCliente");


    Route::get("/reporte/compras/pdf/:id", "ReportesVentaController@reporteCompra");


    Route::get("/reporte/productos/pdf/:id", "ReportesVentaController@reporteProductos");
    Route::get("/reporte/ventasganancias/pdf/:id", "GeneradoresController@reportePeriodoVentaGanancias");
