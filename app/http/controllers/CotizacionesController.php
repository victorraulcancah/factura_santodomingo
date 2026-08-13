<?php

class CotizacionesController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }
    public function eliminarCotizacion()
    {
        //var_dump($_POST);
        $sql = "update cotizaciones set estado = '2' where cotizacion_id = '{$_POST['cod']}';";
        $this->conexion->query($sql);
        echo "{}";
    }

    public function actualizar()
    {
        $respuesta = ["res" => false];
        /*  return json_encode($_POST);
        return; */
        $esc = function ($valor) {
            return $this->conexion->real_escape_string(trim($valor ?? ''));
        };

        $numDoc     = $esc($_POST['num_doc'] ?? '');
        $tipoDocCli = $esc($_POST['tipo_doc_cli'] ?? '1');
        $nomCli     = $esc($_POST['nom_cli'] ?? '');
        $dirCli     = $esc($_POST['dir_cli'] ?? '');
        $dir2Cli    = $esc($_POST['dir2_cli'] ?? '');
        $telCli     = $esc($_POST['tel_cli'] ?? '');

        if ($nomCli === '') {
            $respuesta['msg'] = 'El nombre del cliente es obligatorio';
            return json_encode($respuesta);
        }

        $idCli = '';
        $rowCl = null;
        if ($numDoc !== '') {
            $sql = "SELECT id_cliente from clientes
                    where documento ='$numDoc' and id_empresa='{$_SESSION['id_empresa']}'";
            $rowCl = $this->conexion->query($sql)->fetch_assoc();
        }

        if ($rowCl) {
            $idCli = $rowCl['id_cliente'];

            // Cliente existente: guardar lo que se haya escrito ahora.
            // Lo que se deje en blanco no pisa lo que ya estaba guardado.
            $sets = ["datos='$nomCli'", "tipo_documento='$tipoDocCli'"];
            if ($dirCli !== '')  { $sets[] = "direccion='$dirCli'"; }
            if ($dir2Cli !== '') { $sets[] = "direccion2='$dir2Cli'"; }
            if ($telCli !== '')  { $sets[] = "telefono='$telCli'"; }
            $this->conexion->query("update clientes set " . implode(', ', $sets) . " where id_cliente='$idCli'");
        } else {
            $sql = "insert into clientes set documento='$numDoc',
            tipo_documento='$tipoDocCli',
            datos='$nomCli',
            direccion='$dirCli',
            direccion2='$dir2Cli',
            telefono='$telCli',
            id_usuario='" . (int) ($_SESSION['usuario_fac'] ?? 0) . "',
            id_empresa='{$_SESSION['id_empresa']}'";
            $this->conexion->query($sql);
            $idCli = $this->conexion->insert_id;
        }

        $sql = "update cotizaciones set id_tipo_pago='{$_POST['tipo_pago']}',
  fecha='{$_POST['fecha']}',
  dias_pagos='{$_POST['dias_pago']}',
  direccion='{$_POST['dir_pos']}',
  id_cliente='$idCli',
  usar_precio='{$_POST['usar_precio']}',
  total='{$_POST['total']}' where cotizacion_id='{$_POST['cotiId']}'";

        if ($this->conexion->query($sql)) {
            $respuesta['res'] = true;

            $productos = json_decode($_POST['listaPro'], true);

            $cuotas = json_decode($_POST['dias_lista'], true);

            $sql = "delete from productos_cotis where id_coti='{$_POST['cotiId']}'";
            $this->conexion->query($sql);

            $sql = "delete from cuotas_cotizacion where id_coti='{$_POST['cotiId']}'";
            $this->conexion->query($sql);

            foreach ($cuotas as $cuota) {
                $sql = "insert into cuotas_cotizacion set monto='{$cuota['monto']}',fecha='{$cuota['fecha']}',id_coti='{$_POST['cotiId']}'";
                $this->conexion->query($sql);
            }
            foreach ($productos as $prod) {
                // Se conserva la marca de obsequio al reescribir el detalle
                $esRegalo = !empty($prod['regalo']) ? 1 : 0;
                $precioProd = $esRegalo ? 0 : $prod['precioVenta'];

                $sql = "insert into productos_cotis set id_coti='{$_POST['cotiId']}',
              id_producto='{$prod['productoid']}',
              cantidad='{$prod['cantidad']}',
              precio='$precioProd',
              es_regalo='$esRegalo',
              costo='{$prod['costo']}',serie='{$prod['serie']}'";
                $this->conexion->query($sql);
            }
        }

        return json_encode($respuesta);
    }

    public function getInformacion()
    {
        $cotizacion = $_POST['coti'];
        $data = [];

        $sql = "SELECT * FROM cotizaciones where cotizacion_id='$cotizacion'";
        $data = $this->conexion->query($sql)->fetch_assoc();

        $sql = "select * from cuotas_cotizacion where id_coti = '$cotizacion'";
        $cuotasR = $this->conexion->query($sql);
        $data["cuotas"] = [];
        foreach ($cuotasR as $cuota) {
            $data["cuotas"][] = [
                'cuotaid' => $cuota['cuota_coti_id'],
                'fecha' => $cuota['fecha'],
                'monto' => $cuota['monto']
            ];
        }

        $sql = "select * from clientes where id_cliente = '{$data['id_cliente']}'";
        //echo $sql;
        $clienteR = $this->conexion->query($sql)->fetch_assoc();

        $data["cliente_doc"] = $clienteR['documento'];
        $data["cliente_nom"] = $clienteR['datos'];
        $data["cliente_dir1"] = $clienteR['direccion'];
        $data["cliente_dir2"] = $clienteR['direccion2'] ? $clienteR['direccion2'] : '';
        $data["cliente_tel"] = $clienteR['telefono'] ? $clienteR['telefono'] : '';
        $data["cliente_tipo_doc"] = $clienteR['tipo_documento'] ? $clienteR['tipo_documento'] : '1';

        $data["productos"] = [];
        $sql = "SELECT p.codigo,pc.id_producto,pc.cantidad,p.descripcion,p.codsunat,p.precio,p.precio2,p.precio3,p.costo,pc.precio AS precioVenta,p.precio4,p.precio_unidad,pc.serie,pc.es_regalo,
                p.unidades_por_caja, p.volumen_unidad, p.id_unidad_derivada,
                ud.nombre AS unidad_derivada_nombre
        FROM productos_cotis pc
        JOIN productos p ON p.id_producto = pc.id_producto
        LEFT JOIN unidades_derivadas ud ON p.id_unidad_derivada = ud.id_unidad
        WHERE pc.id_coti = '$cotizacion'";
        $productosR = $this->conexion->query($sql);

        foreach ($productosR as $pro) {
            $data["productos"][] = [
                "codigo_pp" => $pro['codigo'],
                "productoid" => $pro['id_producto'],
                "descripcion" => $pro['descripcion'],
                "nom_prod" => $pro['descripcion'],
                "cantidad" => $pro['cantidad'],
                "stock" => 0,
                "precioVenta" => number_format((float)$pro['precioVenta'], 2, '.', ''),
                "precio" => $pro['precio'],
                "precio2" => $pro['precio2'],
                "precio3" => $pro['precio3'],
                "precio4" => $pro['precio4'],
                "precio_unidad" => $pro['precio_unidad'],
                "codigo" => $pro['codsunat'],
                "precio" => $pro['precio'],
                "codsunat" => $pro['codsunat'],
                "costo" => $pro['costo'],
                "precio_usado" =>  $data['usar_precio'],
		    "serie" =>  $pro['serie'],
		    "serie_producto" =>  $pro['serie'],
                "unidades_por_caja" => intval($pro['unidades_por_caja'] ?? 1),
                "volumen_unidad" => $pro['volumen_unidad'] ?? '',
                "id_unidad_derivada" => $pro['id_unidad_derivada'] ?? null,
                "unidad_derivada_nombre" => $pro['unidad_derivada_nombre'] ?? '',
                "presentacion" => 'unidad',
                // Se conserva la marca de obsequio al pasar la cotizacion a venta
                "regalo" => !empty($pro['es_regalo']),

            ];
        }

        return json_encode($data);
    }

    public function agregar()
    {

        $respuesta = ["res" => false];

        $esc = function ($valor) {
            return $this->conexion->real_escape_string(trim($valor ?? ''));
        };

        // Solo el nombre es obligatorio, el resto de datos del cliente es opcional
        $numDoc     = $esc($_POST['num_doc'] ?? '');
        $tipoDocCli = $esc($_POST['tipo_doc_cli'] ?? '1');
        $nomCli     = $esc($_POST['nom_cli'] ?? '');
        $dirCli     = $esc($_POST['dir_cli'] ?? '');
        $dir2Cli    = $esc($_POST['dir2_cli'] ?? '');
        $telCli     = $esc($_POST['tel_cli'] ?? '');
        $idUsuario  = (int) ($_SESSION['usuario_fac'] ?? 0);

        if ($nomCli === '') {
            $respuesta['msg'] = 'El nombre del cliente es obligatorio';
            return json_encode($respuesta);
        }

        $idCli = '';

        // Sin documento no se puede reutilizar un cliente existente: siempre se crea uno nuevo
        if ($numDoc !== '') {
            $sql = "SELECT id_cliente from clientes
                    where documento ='$numDoc' and id_empresa='{$_SESSION['id_empresa']}'";
            if ($rowCl = $this->conexion->query($sql)->fetch_assoc()) {
                $idCli = $rowCl['id_cliente'];

                // Cliente existente: guardar los datos que se hayan escrito ahora.
                // Lo que se deje en blanco no pisa lo que ya estaba guardado.
                $sets = [
                    "datos='$nomCli'",
                    "tipo_documento='$tipoDocCli'",
                ];
                if ($dirCli !== '')  { $sets[] = "direccion='$dirCli'"; }
                if ($dir2Cli !== '') { $sets[] = "direccion2='$dir2Cli'"; }
                if ($telCli !== '')  { $sets[] = "telefono='$telCli'"; }
                $this->conexion->query("update clientes set " . implode(', ', $sets) . " where id_cliente='$idCli'");
            }
        }

        if ($idCli === '') {
            $sql = "insert into clientes set documento='$numDoc',
            tipo_documento='$tipoDocCli',
            datos='$nomCli',
            direccion='$dirCli',
            direccion2='$dir2Cli',
            telefono='$telCli',
            id_usuario='$idUsuario',
            id_empresa='{$_SESSION['id_empresa']}'";
            $this->conexion->query($sql);
            $idCli = $this->conexion->insert_id;
        }
        $sql = "select * from cotizaciones where id_empresa='{$_SESSION['id_empresa']}' order by numero desc limit 1";

        $numCoti = 1;

        if ($roTemp = $this->conexion->query($sql)->fetch_assoc()) {
            $numCoti = $roTemp["numero"] + 1;
        }

        $sql = "insert into cotizaciones set id_tido='{$_POST['tipo_doc']}',
                moneda='{$_POST['moneda']}',
                cm_tc='{$_POST['tc']}',
                id_tipo_pago='{$_POST['tipo_pago']}',
                fecha='{$_POST['fecha']}',
                dias_pagos='{$_POST['dias_pago']}',
                direccion='{$_POST['dir_pos']}',
                id_cliente='$idCli',
                total='{$_POST['total']}',
                    numero='$numCoti',
                estado='0',
                usar_precio='{$_POST['usar_precio']}',
                sucursal='{$_SESSION['sucursal']}',
                id_empresa='{$_SESSION['id_empresa']}',id_usuario='{$_SESSION['usuario_fac']}'";

        if ($this->conexion->query($sql)) {
            $idCoti = $this->conexion->insert_id;

            $listaCuotas = json_decode($_POST['dias_lista'], true);
            $listaProd = json_decode($_POST['listaPro'], true);

            foreach ($listaCuotas as $cuota) {
                $sql = "insert into cuotas_cotizacion set id_coti='$idCoti',
  monto='{$cuota['monto']}',
  fecha='{$cuota['fecha']}'";
                $this->conexion->query($sql);
            }
            foreach ($listaProd as $prod) {
                // Un regalo va siempre a precio 0, pero conserva su costo
                $esRegalo = !empty($prod['regalo']) ? 1 : 0;
                $precioProd = $esRegalo ? 0 : $prod['precioVenta'];

                $sql = "insert into productos_cotis set id_coti='$idCoti',
              id_producto='{$prod['productoid']}',
              cantidad='{$prod['cantidad']}',
              precio='$precioProd',
              es_regalo='$esRegalo',
              costo='{$prod['costo']}',serie='{$prod['serie']}'";
                $this->conexion->query($sql);
            }


            $respuesta['res'] = true;
        }
        return json_encode($respuesta);
    }

    public function listar()
    {
        $sql = "select v.cotizacion_id,v.numero, v.fecha,v.moneda,v.cm_tc, 
       v.id_tido, c.documento, c.datos, v.total, v.estado
        from cotizaciones as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
        where v.id_empresa = '12'  and v.sucursal ='{$_SESSION['sucursal']}' and v.estado<>'2'
        order by v.fecha asc ";
        //echo $sql;

        $rest = $this->conexion->query($sql);
        $lista = [];
        foreach ($rest as $row) {
            $lista[] = $row;
        }
        return json_encode($lista);
    }
}
