<?php

require_once "utils/lib/exel/vendor/autoload.php";

require_once "app/models/Producto.php";

class ProductosController extends Controller
{
    private $conexion;
    private $c_producto;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
        $this->modelo = new models();

        /*   $c_producto->setIdEmpresa($_SESSION['id_empresa']); */
    }

    public function listaProductoServerSide()
    {
        require_once "app/clases/serverside.php";
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $almacen = $_GET['almacenId'] ?? null;
        $stockF = $_GET['stockF'] ?? null;

        $start = $_GET['iDisplayStart'] ?? 0;
        $length = $_GET['iDisplayLength'] ?? 10;
        $orderColumnIndex = $_GET['iSortCol_0'] ?? 0;
        $orderDirection = $_GET['sSortDir_0'] ?? 'desc';
        $searchValue = $_GET['sSearch'] ?? ''; // b�squeda global
        $searchColumns = $_GET['bSearchable_0'] ?? false; // �est�n habilitadas las b�squedas?

        // Mapea los índices de columnas de DataTables con los nombres en la base de datos
        $columns = [
            'p.id_producto',
            'p.cod_barra',
            'p.descripcion',
            'p.sucursal',
            'p.precio',
            'pc.nombre',
            'p.cantidad',
            'p.costo',
            'p.ultima_salida',
            'p.codsunat',
            'p.razon_social',
            'p.precio_mayor',
            'p.precio_menor',
            'p.id_producto'
        ];

        // Asegura que la columna de ordenamiento existe en el array
        $orderColumn = $columns[$orderColumnIndex] ?? 'p.id_producto';

        $whereClauses = [];
        $searchQuery = '';
        if ($searchColumns && $searchValue) {
            // Buscar en todas las columnas que son texto
            $textColumns = ['descripcion', 'cod_barra', 'razon_social'];

            foreach ($columns as $column) {
                $columnName = explode('.', $column);
                $columnName = end($columnName);

                if (in_array($columnName, $textColumns)) {
                    $whereClauses[] = "$column LIKE ?";
                }
            }

            // Construir la cl�usula WHERE para b�squeda
            if (!empty($whereClauses)) {
                $searchQuery = " AND (" . implode(" OR ", $whereClauses) . ")";
            }
        }

        $sqlf = ($stockF === '-1') ? " AND p.cantidad < 0 "
            : (($stockF === '1') ? " AND p.cantidad > 0 "
                : (($stockF === '0') ? " AND p.cantidad = 0 "
                    : ""));

        $sql = "SELECT 
            p.id_producto, 
            p.cod_barra, 
            p.descripcion, 
            p.costo, 
            p.precio, 
            p.cantidad, 
            p.iscbp, 
            p.id_empresa, 
            p.sucursal, 
            p.ultima_salida, 
            p.codsunat, 
            p.usar_barra, 
            p.precio_mayor, 
            p.precio_menor, 
            p.razon_social, 
            p.ruc, 
            p.estado, 
            p.almacen, 
            p.precio2, 
            p.precio3, 
            p.precio4, 
            p.precio_unidad, 
            p.codigo, 
            pc.nombre AS categoria
        FROM productos p 
        LEFT JOIN productos_categorias pc ON p.codsunat = pc.id_categoria 
        WHERE p.id_empresa = 12 
        AND p.sucursal = '1' 
        AND p.estado = '1' 
        AND p.almacen = ? " . $searchQuery . $sqlf . " 
        ORDER BY $orderColumn $orderDirection
        LIMIT ?, ?";



        //echo $sql;


        $stmt = $this->conexion->prepare($sql);
        /*
        $stmt->bind_param("iii", $almacen, $start, $length );
        $stmt->execute();
        $result = $stmt->get_result();
	*/
        $params = [$almacen];
        if (!empty($whereClauses)) {
            foreach ($whereClauses as $clause) {
                $params[] = "%{$searchValue}%";
            }
        }



        $params[] = $start;
        $params[] = $length;

        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();


        $data = [];
        $recordsFiltered = 0;
        while ($row = $result->fetch_assoc()) {
            $recordsFiltered++;
            $data[] = [
                $row['id_producto'],
                $row['cod_barra'],
                $row['descripcion'],
                $row['id_producto'],
                $row['id_producto'],
                $row['categoria'],
                $row['cantidad'],
                $row['costo'],
                $row['id_producto'],
                $row['ultima_salida'],
                $row['razon_social'],

                $row['id_empresa'],
                $row['sucursal'],
                $row['codsunat'],
                $row['usar_barra'],
                $row['precio_mayor'],
                $row['precio_menor'],

            ];
        }


        // Obtener el total de registros sin filtros
        $totalRecordsQuery = "SELECT COUNT(*) as total FROM productos WHERE id_empresa = 12 AND sucursal = '1' AND estado = '1' AND almacen = ?";
        $totalStmt = $this->conexion->prepare($totalRecordsQuery);
        $totalStmt->bind_param("i", $almacen);
        $totalStmt->execute();
        $totalResult = $totalStmt->get_result();
        $totalRecords = $totalResult->fetch_assoc()['total'];

        // Obtener el total de registros con filtros
        $sqlpag = "SELECT COUNT(*) AS total FROM productos
                 WHERE id_empresa = 12 AND sucursal = '1'  AND estado = '1'  AND almacen = ?
		AND descripcion LIKE ?";

        $params2 = [$almacen, "%{$searchValue}%"];
        $filteredStmt = $this->conexion->prepare($sqlpag);
        $filteredStmt->bind_param("is", ...$params2);
        $filteredStmt->execute();
        $totalResult2 = $filteredStmt->get_result();
        $totalRecords2 = $totalResult2->fetch_assoc()['total'];






        $response = [
            "sEcho" => intval($_GET['sEcho'] ?? 1),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecords2,
            "aaData" => $data,
            "tcosto" => 0,
        ];
        header('Content-Type: application/json');
        echo json_encode($response);

        // $table_data = new TableData();
        // if ($almacen == '1') {
        //     $table_data->get("view_productos_1", "id_producto", [
        //         "id_producto",
        //         "codigo",
        //         "descripcion",
        //         "serie_producto",
        //         "categoria",
        //         "cantidad",
        //         "costo",
        //         "precio2",
        //         "ultima_salida",
        //         "razon_social",
        //         "id_producto",
        //         "id_producto",
        //         "id_producto",
        //         "id_producto"
        //     ]);
        // } else {
        //     $table_data->get("view_productos_2", "id_producto", [
        //         "id_producto",
        //         "codigo",
        //         "descripcion",
        //         "serie_producto",
        //         "categoria",
        //         "cantidad",
        //         "costo",
        //         "precio2",
        //         "ultima_salida",
        //         "razon_social",
        //         "id_producto",
        //         "id_producto",
        //         "id_producto",
        //         "id_producto",
        //         "serie_producto"
        //     ]);
        // }

    }

    public function listaProducto()
    {
        $c_producto = new Producto();
        $c_producto->setIdEmpresa($_SESSION['id_empresa']);
        $a_productos = $c_producto->verFilas($_POST['almacenId']);
        /*    $metodosPago = $this->consulta->exeSQL($a_productos); */
        $lista = [];
        foreach ($a_productos as $rowT) {
            $lista[] = $rowT;
        }
        return json_encode($lista);
        /*   echo json_encode($data); */

        /*     echo json_encode($_POST); */
    }

    public function agregarPorLista()
    {

        $url = 'https://xn--viasantodomingo-zqb.com/public_html/testapi/RestProducto.php';
        $curl = curl_init();
        $fields = array(
            'listado' => $_POST['lista'],
            'opc' => 'agregar-masivo'
        );
        $fields_string = http_build_query($fields);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        $data = curl_exec($curl);
        curl_close($curl);
        $listado = json_decode($data, true);

        $respuesta = ["res" => false];
        foreach ($listado as $item) {
            $afect = $item['afecto'] ? '1' : '0';

            $descripcion = $item['descripcicon'];
            $codigoProd = $item['codigoProd'];

            $sql = "insert into productos set descripcion=?,
		  precio='{$item['precio']}',
		  precio2='{$item['precio2']}',
		  precio3='{$item['precio3']}',
		  precio4='{$item['precio4']}',
		  almacen='{$item['almacen']}',
		  precio_unidad='{$item['precio_unidad']}',
		  costo='{$item['costo']}',
		  cantidad='{$item['cantidad']}',
		  iscbp='$afect',
		  id_empresa='{$_SESSION['id_empresa']}',
		  ultima_salida='1000-01-01',
		  sucursal='{$_SESSION['sucursal']}',
		  codsunat='{$item['codSunat']}',
		  prod_cod='{$item['resp']}',
		  codigo=?";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param('ss', $descripcion, $codigoProd);
            /*   $stmt->bind_param('s', $codigoProd); */

            if ($stmt->execute()) {
                $respuesta["res"] = true;
                $respuesta["msj"] = $this->conexion->error;
                $respuesta["sql"] = $sql;
            }
        }
        return json_encode($respuesta);
    }

    public function importarExel()
    {
        $respuesta = ["res" => false];
        $filename = $_FILES['file']['name'];

        $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
        $newName = Tools::getToken(80);
        /* Location */
        $loc_ruta = "files/temp";
        if (!file_exists($loc_ruta)) {
            mkdir($loc_ruta, 0777, true);
        }
        $location = $loc_ruta . "/" . $newName . '.' . $path_parts;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
            $nombre_logo = $newName . "." . $path_parts;

            $respuesta["res"] = true;
            $type = $path_parts;

            if ($type == "xlsx") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } elseif ($type == "xls") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } elseif ($type == "csv") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            }

            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load("files/temp/" . $nombre_logo);

            $schdeules = $spreadsheet->getActiveSheet()->toArray();
            // array_shift($schdeules);
            $respuesta["data"] = $schdeules;

            unlink($location);
            //return $schdeules;
        }

        return json_encode($respuesta);
    }

    public function restock()
    {
        $respuesta = ["res" => false];
        $sqlfil = "SELECT prod_cod, (cantidad+{$_POST['cantidad']}) as total FROM productos  WHERE id_producto='{$_POST['cod']}'";
        $row = $this->conexion->query($sqlfil)->fetch_array();
        $prod_code = $row["prod_cod"];
        $ncantidad = $row["total"];
        ///envio de datos
        $url = 'https://xn--viasantodomingo-zqb.com/public_html/testapi/RestProducto.php';
        $curl = curl_init();
        $fields = array(
            'codpro' => $prod_code,
            'prostock' => $ncantidad,
            'opc' => 'editar-stock'
        );
        $fields_string = http_build_query($fields);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        $data = curl_exec($curl);
        curl_close($curl);
        $decodificado = json_decode($data, true);
        $rest = $decodificado[0]['resp'];


        if ($prod_code != '') {
            $sql = "update productos set cantidad=cantidad+{$_POST['cantidad']} where id_producto='{$_POST['cod']}'";
            if ($this->conexion->query($sql)) {
                $respuesta["res"] = true;
            }
        }
        return json_encode($respuesta);
    }

    public function informacionPorCodigo()
    {
        $respuesta = ["res" => false];
        $sql = "SELECT *


	 FROM productos where trim(codigo)='{$_POST['code']}' AND almacen = '{$_POST['almacen']}' and sucursal='{$_SESSION['sucursal']}'";
        if ($row = $this->conexion->query($sql)->fetch_assoc()) {
            $respuesta["res"] = true;
            $respuesta["data"] = $row;
        }
        return json_encode($respuesta);
    }


    public function informacionHistorial()
    {
        $respuesta = ["res" => false];

        /* ventas */
        $sqlv = "SELECT 
	v.fecha_emision, CONCAT(v.serie,'-',v.numero) as nfactura,
	CONCAT(c.datos,' ',c.documento) as client, 
	pv.cantidad, FORMAT(pv.precio,2) AS pprecio,FORMAT((pv.cantidad * pv.precio),2) AS total,
	pv.id_producto
	FROM productos_ventas pv, ventas v, clientes c 
	WHERE v.id_venta = pv.id_venta 
	AND v.id_cliente = c.id_cliente  AND pv.id_producto ='{$_POST['cod']}'";
        $resultado = $this->conexion->query($sqlv);
        while ($row2 = $resultado->fetch_assoc()) {
            $respuesta["venta"][] = $row2;
        }



        /* compras */
        $sqlc = "SELECT cc.id_compra, cc.id_proveedor, cc.fecha_emision,  CONCAT(cc.serie,'-',cc.numero) as nfactura,
	CONCAT (pr.razon_social,' ',pr.ruc) AS provee,
	pc.cantidad, FORMAT(pc.costo,2) AS pprecio, FORMAT((pc.cantidad * pc.costo),2) AS total,
	pc.id_producto
	FROM compras cc, proveedores pr, productos_compras pc 
	WHERE pr.proveedor_id = cc.id_proveedor
	AND pc.id_compra = cc.id_compra AND pc.id_producto='{$_POST['cod']}'";
        $resultado2 = $this->conexion->query($sqlc);
        while ($row3 = $resultado2->fetch_assoc()) {
            $respuesta["compra"][] = $row3;
        }

        $sql = "SELECT * FROM productos where id_producto='{$_POST['cod']}'";
        if ($row = $this->conexion->query($sql)->fetch_assoc()) {
            $respuesta["res"] = true;
            $respuesta["data"] = $row;
        }
        return json_encode($respuesta);
    }

    public function informacionPorSerie()
    {
        $respuesta = ["res" => false];
        $sql = "SELECT * FROM productos where id_producto='{$_POST['cod']}'";
        if ($row = $this->conexion->query($sql)->fetch_assoc()) {
            $sql = "SELECT id_producto,serie_producto, id_compra
		FROM `productos_serie` 
		WHERE estado = 1 AND id_producto = '{$_POST['cod']}'
		UNION 
		SELECT id_producto, serie_producto, 0 AS id_compra
		FROM productos WHERE id_producto = '{$_POST['cod']}'";
            $result = $this->conexion->query($sql);
            foreach ($result as $depro) {
                $row["detalles"][] = $depro;
            }


            $respuesta["res"] = true;
            $respuesta["data"] = $row;
        }
        return json_encode($respuesta);
    }

    public function categoprod()
    {
        $respuesta = ["res" => false];
        $sql = "SELECT * FROM productos_categorias WHERE estado = '1'";
        $result = $this->conexion->query($sql);
        foreach ($result as $depro) {
            $row["detalles"][] = $depro;
        }
        $respuesta["res"] = true;
        $respuesta["data"] = $row;

        return json_encode($respuesta);
    }

    public function costoinv()
    {

        $respuesta = ["res" => false];
        $stockF = $_POST['stock'] ?? null;
        $sqlf = ($stockF === '-1') ? " AND cantidad < 0 "
            : (($stockF === '1') ? " AND cantidad > 0 "
                : (($stockF === '0') ? " AND cantidad = 0 "
                    : ""));

        $sql = "SELECT FORMAT(SUM(costo * cantidad),2) as totald FROM productos  WHERE almacen='{$_POST['tipo']}' AND estado='1'";
        $sql = $sql . " " . $sqlf;
        $result = $this->conexion->query($sql);
        foreach ($result as $depro) {
            $row = $depro;
        }
        $respuesta["res"] = true;
        $respuesta["data"] = $row;

        return json_encode($respuesta);
    }


    public function informacion()
    {
        $respuesta = ["res" => false];
        $sql = "SELECT *, costo AS costult	

	 FROM productos p where p.id_producto='{$_POST['cod']}'";
        if ($row = $this->conexion->query($sql)->fetch_assoc()) {
            $catego = $row["codsunat"];
            $sql = "SELECT id_categoria, nombre FROM `productos_categorias`  WHERE id_categoria IN ('$catego')
		UNION 
		SELECT id_categoria, nombre FROM `productos_categorias`  WHERE id_categoria NOT IN ('$catego')";
            $result = $this->conexion->query($sql);
            foreach ($result as $depro) {
                $row["detalles"][] = $depro;
            }
            $respuesta["res"] = true;
            $respuesta["data"] = $row;
        }
        return json_encode($respuesta);
    }

    public function agregar()
    {
        // 1. Iniciar búfer y forzar cabecera JSON
        ob_start();
        header('Content-Type: application/json');

        try {
            $respuesta = ["res" => false];

            // Recopilación de datos
            $descripcion = $_POST['descripcicon'] ?? '';
            $ruc = $_POST['ruc'] ?? '';
            $razon = $_POST['razon'] ?? '';
            $precio = floatval($_POST['precio'] ?? 0);
            $costo = floatval($_POST['costo'] ?? 0);
            $cantidad = floatval($_POST['cantidad'] ?? 0);
            $precioMayor = floatval($_POST['precioMayor'] ?? 0);
            $precioMenor = floatval($_POST['precioMenor'] ?? 0);
            $marca = $_POST['marca'] ?? '';
            $codSunat = $_POST['codSunat'] ?? '';

            // 2. Llamada a la API Externa
            $url = 'https://xn--viasantodomingo-zqb.com/public_html/testapi/RestProducto.php';
            $curl = curl_init();
            $fields = [
                'pronmbre' => $descripcion,
                'promarca' => 'null',
                'proprecio' => $precio,
                'prostock' => $cantidad,
                'procosto' => $costo,
                'procate' => $codSunat,
                'opc' => 'agregar-producto'
            ];

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);

            $data = curl_exec($curl);
            curl_close($curl);

            if ($data === false) {
                throw new Exception("Fallo de red cURL");
            }

            $decodificado = json_decode($data, true);
            $rest = $decodificado[0]['resp'] ?? '';

            if (empty($rest)) {
                throw new Exception("API no devolvió código válido");
            }

            // 3. Registro de Proveedor y Lógica de Categoría
            if (intval(onlyNumber($ruc)) <= 0) {
                throw new Exception("Ingrese un RUC válido");
            }

            $proveedor = new stdClass();
            $proveedor->id_empresa = $_SESSION['id_empresa'];
            $proveedor->ruc = $ruc;
            $proveedor->razon_social = $razon;
            $this->modelo->setTable('proveedores')->insert($proveedor);

            // Lógica de Categoría simplificada
            $sqlf = "SELECT id_categoria AS idfiltro FROM productos_categorias WHERE nombre='$codSunat' LIMIT 1";
            $row = $this->conexion->query($sqlf)->fetch_assoc();
            $idfiltro = $row['idfiltro'] ?? 0;

            if ($idfiltro == 0 && !empty($codSunat)) {
                $rowMAX = $this->conexion->query("SELECT (MAX(id_categoria) + 1) AS maximo FROM productos_categorias")->fetch_assoc();
                $idmax = $rowMAX['maximo'] ?? 1;
                $this->conexion->query("INSERT INTO productos_categorias SET codigo ='$idmax', nombre = UPPER('$codSunat'), estado = '1', resp_api ='$idmax'");
                $idfiltro = $idmax;
            }

            // 4. Inserción Final
            $requestData = getVarsPost($_POST);
            $desc_esc = $this->conexion->real_escape_string($requestData->descripcicon);
            $razon_esc = $this->conexion->real_escape_string($razon);

            $sql = "INSERT INTO productos SET 
                descripcion = '$desc_esc', precio = '$precio', precio_unidad = '$precio', costo = '$costo',
                almacen = '{$_POST['almacen']}', cantidad = '$cantidad', iscbp = '{$_POST['afecto']}',
                sucursal = '{$_SESSION['sucursal']}', id_empresa = '{$_SESSION['id_empresa']}',
                ultima_salida = '1000-01-01', codsunat = '$idfiltro', prod_cod = '$rest',
                marca = '$marca', precio_mayor = $precioMayor, precio_menor = $precioMenor,
                razon_social = '$razon_esc', ruc = '$ruc', serie_producto = '{$_POST['serie_producto']}',
                codigo = '$desc_esc'";

            $resultado = $this->modelo->insert($sql);

            if ($resultado && $resultado->estatus) {
                $respuesta["res"] = true;
            } else {
                throw new Exception("Error al insertar en BD local");
            }
        } catch (Exception $e) {
            $respuesta = ["res" => false, "error" => $e->getMessage()];
        }

        // Limpiar cualquier salida accidental (warnings) y enviar JSON limpio
        ob_end_clean();
        echo json_encode($respuesta);
        exit;
    }

    public function actualizar()
    {
        $respuesta = ["res" => false];
        $descripcion = $_POST['descripcicon'] ?? '';
        $codigoProd = $_POST['codigo'];
        $_POST['marca'] = $_POST['marca'] ?? '';
        $sql = "select * from productos where id_producto='{$_POST['cod']}'";
        $result = $this->conexion->query($sql);
        if ($row = $result->fetch_assoc()) {
            $prod_cod = $row["prod_cod"];
            $almacenTemp = $row["almacen"] == "1" ? 2 : 1;
            $codigo = $row['descripcion'] ?? '';

            $sql = "update productos set descripcion='$descripcion', cod_barra='', serie_producto='{$_POST["serie_producto"]}', usar_barra='{$_POST['usar_barra']}',
                  precio='{$_POST['precio']}', costo='{$_POST['costo']}', iscbp='{$_POST['afecto']}', codsunat='{$_POST['codSunat']}',
                  precio_mayor='{$_POST['precioMayor']}',
                  precio_menor='{$_POST['precioMenor']}',
                  razon_social='{$_POST['razon']}',
                  ruc='{$_POST['ruc']}',
                   codigo='$descripcion'
                  where id_producto='{$_POST['cod']}'";
            #console($descripcion);
            #console($codigoProd);
            #console($_POST);
            #die();
            #$stmt = $this->conexion->prepare($sql);
            #$stmt->bind_param('ss', $descripcion, $codigoProd, $row['descripcion']);
            #/*   $stmt->bind_param('s', $codigoProd); */
            #
            #if (!$stmt->execute()) {
            #    var_dump($stmt->error);
            #}


            #$resultado = $this->modelo->insert($sql);
            #console($resultado);
            #die();
            #if ($resultado->estatus == false) {
            #    console($resultado);
            #}
        }

        /*   $sql = "insert into productos set descripcion=?, */

        $sql = "update productos set descripcion=?,
                     cod_barra='',
                     usar_barra='{$_POST['usar_barra']}',  precio_unidad='{$_POST['precio']}',   costo='{$_POST['costo']}',   iscbp='{$_POST['afecto']}',  cantidad='{$_POST['cantidad']}',
             codsunat='{$_POST['codSunat']}' ,razon_social='{$_POST['razon']}',ruc='{$_POST['ruc']}',
             marca='{$_POST['marca']}',  codigo=?   where id_producto='{$_POST['cod']}'";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('ss', $descripcion, $codigoProd);
        /*   $stmt->bind_param('s', $codigoProd); */

        if ($prod_cod != '') {
            //enviar solicitud api
            $url = 'https://xn--viasantodomingo-zqb.com/public_html/testapi/RestProducto.php';
            $curl = curl_init();
            $fields = array(
                'codpro' => $prod_cod,
                'pronmbre' => $descripcion,
                'proprecio' => $_POST['precio'],
                'prostock' => $_POST['cantidad'],
                'procosto' => $_POST['costo'],
                'opc' => 'editar-producto'
            );
            $fields_string = http_build_query($fields);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
            $data = curl_exec($curl);
            curl_close($curl);
            $decodificado = json_decode($data, true);
            $rest = $decodificado[0]['resp'];
        }

        if ($rest == $prod_cod) {
            if ($stmt->execute()) {
                $respuesta["res"] = true;
            } else {
                $respuesta = ["res" => $prod_cod];
            }
        }


        return json_encode($respuesta);
    }

    public function actualizarPrecios()
    {
        $respuesta = ["res" => false];
        $sqlfil = "SELECT prod_cod, descripcion FROM productos  WHERE id_producto='{$_POST['cod_prod']}'";
        $row = $this->conexion->query($sqlfil)->fetch_array();
        $prod_code = $row["prod_cod"];
        $pronombre = $row["descripcion"];

        $url = 'https://xn--viasantodomingo-zqb.com/public_html/testapi/RestProducto.php';
        $curl = curl_init();
        $fields = array(
            'codpro' => $prod_code,
            'pronombre' => $pronombre,
            'proprecio' => $_POST['precio_unidad'],
            'precio' => $_POST['precio'],
            'precio2' => $_POST['precio2'],
            'precio3' => $_POST['precio3'],
            'precio4' => $_POST['precio4'],
            'opc' => 'editar-precio'
        );
        $fields_string = http_build_query($fields);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        $data = curl_exec($curl);
        curl_close($curl);
        $decodificado = json_decode($data, true);
        $rest = $decodificado[0]['resp'];


        $sql = "update productos set precio='{$_POST['precio']}',precio_unidad='{$_POST['precio_unidad']}', precio2='{$_POST['precio2']}', precio3='{$_POST['precio3']}', precio4='{$_POST['precio4']}' where id_producto='{$_POST['cod_prod']}'";
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
            $sql = "select * from productos where id_producto='{$_POST['cod_prod']}'";
            $result = $this->conexion->query($sql);
            if ($row = $result->fetch_assoc()) {
                $almacenTemp = $row["almacen"] == "1" ? 2 : 1;
                $sql = "update productos set 
                     precio='{$_POST['precio']}',precio_unidad='{$_POST['precio_unidad']}', 
                     precio2='{$_POST['precio2']}', precio3='{$_POST['precio3']}', 
                     precio4='{$_POST['precio4']}'
                  where descripcion=? and almacen='$almacenTemp'";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param('s', $row['descripcion']);
                /*   $stmt->bind_param('s', $codigoProd); */

                if (!$stmt->execute()) {
                }
            }
        }
        return json_encode($respuesta);
    }

    public function confirmarTraslado()
    {
        $respuesta['res'] = false;
        $sql = "SELECT id_producto,almacen_ingreso,almacen_egreso,cantidad FROM ingreso_egreso WHERE intercambio_id ='{$_POST['cod']}'";
        $result = $this->conexion->query($sql)->fetch_assoc();

        $almacen = $result['almacen_ingreso'];
        $id_producto = $result['id_producto'];
        $cantidad = $result['cantidad'];

        $sql = "SELECT * FROM productos WHERE id_producto = '{$result['id_producto']}'";
        $result = $this->conexion->query($sql)->fetch_assoc();


        $sql = "SELECT * FROM productos WHERE descripcion = '{$result['descripcion']}' AND almacen = '$almacen'";
        $result2 = $this->conexion->query($sql)->fetch_assoc();


        if (is_null($result2)) {
            $sql = "INSERT INTO productos 
            (cod_barra, descripcion, precio, costo,cantidad,iscbp,id_empresa,sucursal,ultima_salida,codsunat,usar_barra,precio_mayor,precio_menor,razon_social,ruc,estado,almacen,precio2,precio3)
            SELECT cod_barra, descripcion, precio, costo,$cantidad,iscbp,id_empresa,sucursal,ultima_salida,codsunat,usar_barra,precio_mayor,precio_menor,razon_social,ruc,estado, $almacen,precio2,precio3
            FROM productos
            WHERE id_producto = $id_producto";
            if ($this->conexion->query($sql)) {
                $sql = "UPDATE productos set cantidad = cantidad - $cantidad   WHERE id_producto = $id_producto";
                if ($this->conexion->query($sql)) {
                    $respuesta['res'] = true;
                }
            }
        } else {
            $idExistente = $result2['id_producto'];
            $sql2 = "UPDATE  productos set cantidad =  cantidad - $cantidad  WHERE id_producto = $id_producto";
            if ($this->conexion->query($sql2)) {
                $sql = "UPDATE  productos set cantidad = cantidad + $cantidad   WHERE id_producto = $idExistente";
                if ($this->conexion->query($sql)) {
                    $respuesta['res'] = true;
                }
            }
        }
        if ($respuesta['res']) {
            $sql = "UPDATE  ingreso_egreso set estado = 1   WHERE intercambio_id = '{$_POST['cod']}'";
            if ($this->conexion->query($sql)) {
                $respuesta['res'] = true;
            }
        }
        echo json_encode($respuesta);
    }

    public function delete()
    {
        $respuesta["res"] = true;
        $respuesta["data"] = $_POST;
        $sql = '';
        foreach ($respuesta["data"]['arrayId'] as $ids) {
            /*   $sql .= $ids; */

            $sql = "UPDATE   productos set estado=0 where id_producto = '{$ids['id']}'";
            if ($this->conexion->query($sql)) {
                $respuesta["res"] = true;
            }
        }
        return json_encode($respuesta);
    }
}
