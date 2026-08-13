<?php
require_once "app/models/Consultas.php";
require_once "app/models/Ubigeo.php";
require_once "app/models/Venta.php";
require_once "app/models/Cliente.php";
require_once "app/models/ProductoVenta.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/clases/SunatApi.php";

require_once "app/clases/EnvioEmail.php";

class ConsultasController extends Controller
{
    private $consulta;
    private $sunatApi;

    public function __construct()
    {
        $this->consulta = new Consultas();
        $this->sunatApi = new SunatApi();
    }

    public function agregarTransportista()
    {
        $sql = "insert into tamsporte_persona set ruc='{$_POST['ruc']}'
             ,direccion='{$_POST['direccion']}',razon_social='{$_POST['razon']}'";
        $this->consulta->exeSQL($sql);
        echo "{}";
    }

    public function informacionVentaFb()
    {
        $venta = $_POST["venta"];


        $sql = "select c.*,vs.nombre_xml from ventas v join clientes c on v.id_cliente = c.id_cliente join ventas_sunat vs on v.id_venta = vs.id_venta where v.id_venta = $venta";

        $datos = $this->consulta->exeSQL($sql)->fetch_assoc();

        return json_encode([
            "link" => URL::to("/venta/comprobante/pdf/$venta/" . $datos['nombre_xml']),
            "linkd" => URL::to("/venta/comprobante/pdfd/$venta"),
            "file_name" => $datos['nombre_xml'] . '.pdf',
            "numero" => $datos['telefono'] ? $datos['telefono'] : '',
            "mail" => $datos['email'] ? $datos['email'] : '',
        ]);
    }

    public function buscarProdId()
    {
        $sql = "SELECT * from productos where id_empresa = '{$_SESSION['id_empresa']}' and sucursal = '{$_SESSION['sucursal']}' and estado = '1' AND  id_producto ='{$_POST['index']}' order by id_producto DESC";

        $result = $this->consulta->exeSQL($sql)->fetch_assoc();
        echo json_encode($result);
    }

    public function getMetodoPago()
    {

        $sql = "SELECT * FROM metodo_pago WHERE estado = 1";
        $metodosPago = $this->consulta->exeSQL($sql);
        $lista = [];
        foreach ($metodosPago as $rowT) {
            $lista[] = $rowT;
        }
        return json_encode($lista);
        /*   echo json_encode($data); */
    }

    public function enviarcomprobanteEmail()
    {
        $respuesta = ["res" => false];
        $empresa = $this->consulta->exeSQL("select * from empresas where id_empresa='{$_SESSION['id_empresa']}'")->fetch_assoc();

        $tock_temp = Tools::getToken(10);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $_POST['link'] . "/"
            . base64_encode("files/temp/" . $tock_temp . ".pdf"));
        $data = curl_exec($ch);
        curl_close($ch);

        ob_start();
        $sendEmail = (new EnvioEmail());
        $sendEmail->de(USER_SMTP, $empresa['razon_social'])
            ->addEmail($_POST['email'], 'Cliente')
            ->setasunto("Comprobante Electronico")
            ->cuerpo("<h1>Comproante: {$_POST['nombrefile']}</h1>")
            ->addArchivo("files/temp/" . $tock_temp . ".pdf", $_POST['nombrefile']);

        if (file_exists("files/facturacion/xml/" . $empresa['ruc'] . '/' . basename($_POST['nombrefile'], ".pdf") . ".xml")) {
            $sendEmail->addArchivo("files/facturacion/xml/" . $empresa['ruc'] . '/' . basename($_POST['nombrefile'], ".pdf") . ".xml", basename($_POST['nombrefile'], ".pdf") . ".xml");
        }

        $resul = $sendEmail->enviar();

        ob_end_clean();

        if ($resul) {
            unlink("files/temp/" . $tock_temp . ".pdf");
            $respuesta["res"] = true;
        }
        return json_encode($respuesta);
    }

    public function actualizarSucursal()
    {
        $respuesta = ["res" => false];
        $data = $_POST;
        $sql = "update usuarios set 
        num_doc='{$data['documento']}',
        usuario='{$data['usuario']}', 
        email='{$data['email']}',
        nombres='{$data['nombres']}', 
        telefono='{$data['telefono']}' where usuario_id='{$data['usuarioid']}' ";
        if ($this->consulta->exeSQL($sql)) {
            $respuesta["res"] = true;

            $sq2l = "UPDATE sucursales set direccion= '{$data['direccion']}',distrito = '{$data['distrito']}', provincia = '{$data['provincia']}' ,departamento = '{$data['departamento']}',ubigeo = '{$data['ubigeo']}',cod_sucursal = '{$data['sucursal']}' WHERE empresa_id = '{$data['empr']}'  AND cod_sucursal = '{$data['sucursal']}' ";
            if ($this->consulta->exeSQL($sq2l)) {
                $respuesta["res"] = true;
            }

            $sql = " update documentos_empresas set serie='{$data['serieF']}',numero='{$data['numeroF']}' where id_empresa='{$data['empr']}' and id_tido=2 and sucursal='{$data['sucursal']}'";
            $this->consulta->exeSQL($sql);
            $sql = " update documentos_empresas set serie='{$data['serieB']}',numero='{$data['numeroB']}' where id_empresa='{$data['empr']}' and id_tido=1  and sucursal='{$data['sucursal']}'";
            $this->consulta->exeSQL($sql);
            $sql = " update documentos_empresas set serie='{$data['serieNV']}',numero='{$data['numeroNV']}' where  id_empresa='{$data['empr']}' and id_tido=6 and sucursal='{$data['sucursal']}'";
            $this->consulta->exeSQL($sql);
            $sql = " update documentos_empresas set serie='{$data['serieNC']}',numero='{$data['numeroNC']}' where id_empresa='{$data['empr']}' and id_tido=3 and sucursal='{$data['sucursal']}'";
            $this->consulta->exeSQL($sql);
            $sql = " update documentos_empresas set serie='{$data['serieND']}',numero='{$data['numeroND']}' where id_empresa='{$data['empr']}' and id_tido=4 and sucursal='{$data['sucursal']}'";
            $this->consulta->exeSQL($sql);
            $sql = " update documentos_empresas set serie='{$data['serieGR']}',numero='{$data['numeroGR']}' where id_empresa='{$data['empr']}' and id_tido=11 and sucursal='{$data['sucursal']}'";
            $this->consulta->exeSQL($sql);
        }
        return json_encode($respuesta);
    }

    public function getInfoSucursal()
    {
        $dataR = [];
        $sql = "SELECT * from usuarios where usuario_id='{$_POST['user']}'";
        $user = $this->consulta->exeSQL($sql)->fetch_assoc();

        $sql = "select * from  documentos_empresas where  id_empresa='{$user['id_empresa']}' and sucursal='{$user['sucursal']}'";
        $temResp = $this->consulta->exeSQL($sql);
        $user["docEmp"] = [];
        foreach ($temResp as $rowT) {
            $user["docEmp"][] = $rowT;
        }
        return json_encode($user);
    }

    public function getInfoSucursalDetalle()
    {
        $empresa = $_POST['data']['empresa'];
        $sucursal = $_POST['data']['sucursal'];
        $sql = "SELECT * from sucursales where empresa_id='$empresa' AND cod_sucursal = '$sucursal'";
        $dataa = $this->consulta->exeSQL($sql)->fetch_assoc();

        /*  $sql = "select * from  documentos_empresas where  id_empresa='{$user['id_empresa']}' and sucursal='{$user['sucursal']}'";
            $temResp = $this->consulta->exeSQL($sql);
            $user["docEmp"] = [];
            foreach ($temResp as $rowT) {
                $user["docEmp"][] = $rowT;
            } */
        return json_encode($dataa);
    }

    public function cargarVentaServicios()
    {
        /*  $dataR =[]; */
        $sql = "SELECT id_item,descripcion,monto AS precio,LEFT(cantidad,CHAR_LENGTH(cantidad)-3) as cantidad ,codsunat FROM ventas_servicios where id_venta='{$_POST['idVenta']}'";
        $ventas = $this->consulta->exeSQL($sql);
        $lista = [];
        foreach ($ventas as $rowT) {
            $lista[] = $rowT;
        }
        return json_encode($lista);
    }

    public function cargarVentaDetalles()
    {
        /*  $dataR =[]; */
        $sql = "SELECT * FROM ventas where id_venta='{$_POST['idVenta']}'";
        $ventas = $this->consulta->exeSQL($sql);
        $lista = [];
        foreach ($ventas as $rowT) {
            $lista[] = $rowT;
        }
        return json_encode($lista);
    }

    public function cargarVentaProductos()
    {
        /*  $dataR =[]; */
        $sql = "SELECT pv.id_producto AS productoid,p.descripcion,LEFT(pv.cantidad,CHAR_LENGTH(pv.cantidad)-3) AS cantidad,pv.precio,pv.costo, pv.serie  FROM productos_ventas AS pv 
        JOIN productos AS p ON pv.id_producto=p.id_producto
        WHERE id_venta='{$_POST['idVenta']}'";
        $ventas = $this->consulta->exeSQL($sql);
        $lista = [];
        foreach ($ventas as $rowT) {
            $lista[] = $rowT;
        }
        return json_encode($lista);
    }

    public function agregarSusucursal()
    {
        $respuesta = ["res" => false];

        $sql = "select * from usuarios where id_empresa = '{$_POST['empr']}' order by  sucursal desc limit 1";
        $ultimoSuculsal = $this->consulta->exeSQL($sql)->fetch_assoc();

        $sigienteSucursal = $ultimoSuculsal['sucursal'] + 1;

        $sql = "insert into usuarios set id_empresa='{$_POST['empr']}',
  id_rol='2',
  num_doc='{$_POST['documento']}',
  usuario='{$_POST['usuario']}',
  clave=SHA1('{$_POST['clave']}'),
  email='{$_POST['email']}',
  nombres='{$_POST['nombres']}',
  apellidos='',
  rubro='{$ultimoSuculsal['rubro']}',
  sucursal='$sigienteSucursal',
  telefono='{$_POST['telefono']}',
  estado='1'";
        if ($this->consulta->exeSQLInsert($sql)) {
            $idUsuaio = $this->consulta->getUltimoId();
            $data = $_POST;
            $idEmpresa = $_POST['empr'];

            $sql = " insert into documentos_empresas set sucursal='$sigienteSucursal', id_empresa='$idEmpresa',id_tido=2,serie='{$data['serieF']}',numero='{$data['numeroF']}'";
            //echo $sql;
            $this->consulta->exeSQL($sql);
            $sql = " insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=1,serie='{$data['serieB']}',numero='{$data['numeroB']}'";
            $this->consulta->exeSQL($sql);
            $sql = " insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=6,serie='{$data['serieNV']}',numero='{$data['numeroNV']}'";
            $this->consulta->exeSQL($sql);
            $sql = " insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=3,serie='{$data['serieNC']}',numero='{$data['numeroNC']}'";
            $this->consulta->exeSQL($sql);
            $sql = " insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=4,serie='{$data['serieND']}',numero='{$data['numeroND']}'";
            $this->consulta->exeSQL($sql);

            $sql = " insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=11,serie='{$data['serieGR']}',numero='{$data['numeroGR']}'";
            $this->consulta->exeSQL($sql);

            $sql = "INSERT INTO sucursales set empresa_id = '{$_POST['empr']}', direccion = '{$_POST['direccion']}',distrito ='{$_POST['distrito']}',provincia = '{$_POST['provincia']}',
            departamento = '{$_POST['departamento']}',ubigeo ='{$_POST['ubigeo']}',cod_sucursal ='$sigienteSucursal'";
            $this->consulta->exeSQL($sql);
            $respuesta["res"] = true;
        }
        return json_encode($respuesta);
    }

    public function listasucursaleEmpresa()
    {
        $lista = [];
        $sql = "SELECT * from usuarios where id_empresa='{$_POST['cod']}' AND sucursal <> 1";
        $result = $this->consulta->exeSQL($sql);
        foreach ($result as $R) {
            $lista[] = $R;
        }
        return json_encode($lista);
    }

    public function verificadorToken()
    {
        $respuesta = ["res" => false];
        $save = $_POST['s'] ?? false;
        $sistema_id = "santodomingoapp";

	  $token_desencriptado = Tools::decryptText($_POST['token'] ?? '');
        $token_data = json_decode($token_desencriptado, true);

	  // Validar que el token sea valido
        if (!is_array($token_data) || !isset($token_data['sistema_id'])) {
		$respuesta = ["res" => false];
        } elseif ($token_data['sistema_id'] !== $sistema_id) {
		$respuesta = ["res" => false];
        }  elseif (time() - ($token_data['timestamp'] ?? 0) > 3600) {
		$respuesta = ["res" => false];
        } else {
		$respuesta["res"] = true;
            if ($save) {
                $_SESSION = $token_data;
            }
	  }
        return json_encode($respuesta);
    }

    public function enviarDocumentoSunatNE()
    {
        $sql = "select * from notas_electronicas_sunat where id_notas_electronicas = '{$_POST["cod"]}'";
        $resultado = ["res" => false];
        if ($row = $this->consulta->exeSQL($sql)->fetch_assoc()) {
            if ($this->sunatApi->envioIndividualDocumentoV($row["nombre_xml"])) {
                $sql = "update notas_electronicas set  estado_sunat='1' where nota_id = '{$_POST["cod"]}'";
                $this->consulta->exeSQL($sql);
                $resultado['res'] = true;
            } else {
                $resultado['msg'] = $this->sunatApi->getMensaje();
            }
        }
        return json_encode($resultado);
    }

    public function guardarNotaElectronica()
    {
        $c_tido = new DocumentoEmpresa();

        $c_tido->setIdEmpresa($_SESSION['id_empresa']);
        $c_tido->setIdTido($_POST['tipo_docNE']);
        $c_tido->obtenerDatos();
        $serieE = $c_tido->getSerie();
        $numeroE = $c_tido->getNumero();

        $sql = "insert into notas_electronicas set id_venta='{$_POST['ventacod']}',
  tido='{$_POST['tipo_docNE']}',
  fecha='{$_POST['fecha']}',
    id_empresa='{$_SESSION['id_empresa']}',
    sucursal='{$_SESSION['sucursal']}',
  serie='$serieE',
  numero='$numeroE',
  motivo='{$_POST['motivoNE']}',
  monto='{$_POST['total_NE']}',
  productos=?";
        $productos = $_POST['listaPro'];
        $stmt = $this->consulta->getConectar()->prepare($sql);
        $stmt->bind_param("s", $productos);
        $respuesta = ["res" => false];
        if ($stmt->execute()) {

            $idNotaElectronica = $stmt->insert_id;

            $respuesta["res"] = true;

            $empresa = $this->consulta->exeSQL("select * from empresas where id_empresa='{$_SESSION['id_empresa']}'")->fetch_assoc();
            $dataSend = [];
            if ($_POST['tipo_doc'] == '1') {
                $dataSend['tip_doc_afectado'] = '03';
            } elseif ($_POST['tipo_doc'] == '2') {
                $dataSend['tip_doc_afectado'] = '01';
            }

            if ($_POST['tipo_docNE'] == '3') {
                $dataSend['cod_notaE'] = '07';
            } else {
                $dataSend['cod_notaE'] = '08';
            }

            $sql = "SELECT * FROM motivo_documento where id_motivo = {$_POST['motivoNE']}";

            $motivoNEData = $this->consulta->exeSQL($sql)->fetch_assoc();


            $dataSend['productos'] = [];
            $dataSend["certGlobal"] = false;
            $dataSend["endpoints"] = $empresa['modo'];

            $listaProd = json_decode($productos, true);

            foreach ($listaProd as $prodd) {
                $dataSend['productos'][] = [
                    "precio" => $prodd['precio'],
                    "cantidad" => $prodd['cantidad'],
                    "cod_pro" => $prodd['productoid'],
                    "cod_sunat" => "",
                    "descripcion" => $prodd['descripcion']
                ];
            }

            $dataSend['cliente'] = json_encode([
                'doc_num' => $_POST['num_doc'],
                'nom_RS' => $_POST['nom_cli'],
                'direccion' => $_POST['dir_cli'],
            ]);

            $dataSend['total'] = $_POST['total_NE'];
            $dataSend['serie'] = $serieE;

            $dataSend['sn_afectado'] = $_POST['serie'] . '-' . $_POST['numero'];
            $dataSend['cod_motivo'] = $motivoNEData['codigo'];
            $dataSend['des_motivo'] = $motivoNEData['nombre']; //$_POST['motivodes'];
            $dataSend['numero'] = $numeroE;
            $dataSend['fecha'] = $_POST['fecha'];
            $dataSend['moneda'] = "PEN";
            $dataSend['empresa'] = json_encode([
                'ruc' => $empresa['ruc'],
                'razon_social' => $empresa['razon_social'],
                'direccion' => $empresa['direccion'],
                'ubigeo' => $empresa['ubigeo'],
                'distrito' => $empresa['distrito'],
                'provincia' => $empresa['provincia'],
                'departamento' => $empresa['departamento'],
                'clave_sol' => $empresa['clave_sol'],
                'usuario_sol' => $empresa['user_sol']
            ]);

            /*$file = fopen("archivo.txt", "w");
                fwrite($file, json_encode($dataSend) );
                fclose($file);*/

            $dataSend['productos'] = json_encode($dataSend['productos']);
            $dataResp = $this->sunatApi->genNotaElectronicaXML($dataSend);
            if ($dataResp["res"]) {
                $sql = "insert into notas_electronicas_sunat set 
id_notas_electronicas='$idNotaElectronica',
  hash='{$dataResp['data']['hash']}',
  nombre_xml='{$dataResp['data']['nombre_archivo']}',
  qr_data='{$dataResp['data']['qr']}'
";
                $this->consulta->exeSQL($sql);

                //$respuesta["err"]=$dataResp['data']["error"];
            }
        }
        //echo" ccc";
        return json_encode($respuesta);
    }

    public function functionbuscarDocumentoVentasSN()
    {
        $respuesta = ["res" => false];
        $sql = "select v.*,c.documento,c.datos from ventas v
                join clientes c on c.id_cliente = v.id_cliente
                        where v.serie='{$_POST['serie']}' 
                       and v.numero='{$_POST['numero']}' 
                       and v.id_tido='{$_POST['tidoc']}' and v.id_empresa='12' ";
        //echo $sql;
        $resul = $this->consulta->exeSQL($sql);
        if ($row = $resul->fetch_assoc()) {
            $sql = "SELECT 
pv.cantidad,
pv.precio,
pr.descripcion 
FROM productos_ventas pv 
JOIN productos pr ON pv.id_producto = pr.id_producto
WHERE pv.id_venta = '{$row['id_venta']}' ";

            $rowss = $this->consulta->exeSQL($sql);

            $row["prods"] = [];

            foreach ($rowss as $pod) {
                $row["prods"][] = $pod;
            }

            $respuesta["res"] = true;
            $respuesta["data"] = $row;
        }

        return json_encode($respuesta);
    }

    public function buscarDataProveedor()
    {

        $searchTerm = filter_input(INPUT_GET, 'term');

        $resultados = $this->consulta->buscarProveedor($searchTerm, $_SESSION['id_empresa']);

        $array_resultado = array();
        foreach ($resultados as $value) {
            $fila = array();
            $fila['value'] = $value['ruc'] . " | " . $value['razon_social'];
            $fila['codigo'] = $value['proveedor_id'];
            $fila['documento'] = $value['ruc'];
            $fila['datos'] = $value['razon_social'];
            array_push($array_resultado, $fila);
        }

        return json_encode($array_resultado);
    }

    public function buscarDataCliente()
    {

        $searchTerm = filter_input(INPUT_GET, 'term');

        $resultados = $this->consulta->buscarClientes($searchTerm, $_SESSION['id_empresa']);

        $array_resultado = array();
        foreach ($resultados as $value) {
            $fila = array();
            $fila['value'] = $value['documento'] . " | " . $value['datos'];
            $fila['codigo'] = $value['id_cliente'];
            $fila['documento'] = $value['documento'];
            $fila['direccion'] = $value['direccion'];
            $fila['datos'] = $value['datos'];
            array_push($array_resultado, $fila);
        }

        return json_encode($array_resultado);
    }

    public function buscarDocInfo()
    {
        //var_dump($_POST);
        if (strlen($_POST['doc']) == 8) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://magustechnologies.com:9091/consulta/dni2/' . $_POST['doc']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($data, true);
            $data["data"]["nombre"] = $data["data"]["nombres"] . " " . $data["data"]["apellido_paterno"] . " " . $data["data"]["apellido_materno"];
            echo json_encode($data);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://magustechnologies.com/api/consulta/ruc/' . $_POST['doc']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);
            echo $data;
        }
    }

    public function consultaRuc()
    {
        $ruc = $_POST['ruc'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://magustechnologies.com/api/consulta/ruc/" . $ruc);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'token: VK2BvcODHQtezAU3jZXkYLEifNVKpH8KDlbRn3VGzWqvP0YWfJtMQftu9QFKqcKPDB58WFMFNJT7NdN0UrB5NKTZU84TYKmsWHO1x0h4qZCQwlG53WS4lLrAnSn7I3NBPSfShjNXDfG8jFyY8fCU2kxj7jy4F31xrTboGAVZoWSskUphhKIA1oj8XsmetS7s5EkFo328'
        ));
        $datos = curl_exec($ch);
        curl_close($ch);
        var_dump($datos);
        return '1111';
    }

    public function consultvfb()
    {
        $_SESSION['ventaproductos'] = array();

        //obtener las variables
        $tido = filter_input(INPUT_POST, 'idtido');
        $serie = filter_input(INPUT_POST, 'serie');
        $numero = filter_input(INPUT_POST, 'numero');

        //iniciar clases
        $c_venta = new Venta();
        $c_cliente = new Cliente();
        $c_detalle = new ProductoVenta();

        //enviar datos para consultar detalle
        $c_venta->setIdTido($tido);
        $c_venta->setSerie($serie);
        $c_venta->setNumero($numero);
        $c_venta->validarVenta();

        //iniciar array resultado
        $resultado = [];

        //validar si existe venta
        if ($c_venta->getIdVenta() == null || $c_venta->getIdVenta() == "") {
            $resultado['res'] = false;
            $resultado['msg'] = "Documento no encontrado";
        } else {
            $c_venta->obtenerDatos();
            if ($c_venta->getSucursal() == $_SESSION["sucursal"]) {
                $c_cliente->setIdCliente($c_venta->getIdCliente());
                $c_cliente->obtenerDatos();

                $c_detalle->setIdVenta($c_venta->getIdVenta());
                $a_detalle = $c_detalle->verFilas();

                $resultado["productos"] = [];
                foreach ($a_detalle as $row) {
                    $fila = array();
                    $fila['idproducto'] = $row['id_producto'];
                    $fila['descripcion'] = $row['descripcion'];
                    $fila['cantidad'] = $row['cantidad'];
                    $fila['precio'] = $row['precio'];
                    $fila['costo'] = $row['costo'];
                    $resultado["productos"][] = $fila;
                }

                //iniciar array resultado con valores reales
                $resultado['res'] = true;
                $resultado['idventa'] = $c_venta->getIdVenta();
                $resultado['total'] = $c_venta->getTotal();
                $resultado['doc_cliente'] = $c_cliente->getDocumento();
                $resultado['nom_cliente'] = $c_cliente->getDatos();
                $resultado['dir_cliente'] = $c_cliente->getDireccion();
            } else {
                $resultado['res'] = false;
                $resultado['msg'] = "El documento Ingresado Pertenece a otra sucursal";
            }
        }

        echo json_encode($resultado);
    }

    public function listarDistri()
    {
        $c_ubigeo = new Ubigeo();

        $c_ubigeo->setDepartamento(filter_input(INPUT_POST, 'departamento'));
        $c_ubigeo->setProvincia(filter_input(INPUT_POST, 'provincia'));

        echo $c_ubigeo->verDistritos();
    }

    public function listarProvincias()
    {
        $c_ubigeo = new Ubigeo();

        $c_ubigeo->setDepartamento(filter_input(INPUT_POST, 'departamento'));
        echo $c_ubigeo->verProvincias();
    }

    function buscarSNdoc()
    {
        return json_encode($this->consulta->buscarSNdoc($_SESSION['id_empresa'], $_REQUEST['doc']));
    }

    function buscarTransporteGui()
    {
        $searchTerm = filter_input(INPUT_GET, 'term');
        $sql = "select * from tamsporte_persona where ruc like '%$searchTerm%' or razon_social like '%$searchTerm%' ";
        $resultados = $this->consulta->exeSQL($sql);
        /*   echo 'asdasd';
            die(); */
        $array_resultado = array();
        foreach ($resultados as $value) {
            $fila = array();
            $fila['value'] = $value['ruc'] . ' | ' . $value['razon_social'];
            $fila['ruc'] = $value['ruc'];
            $fila['razon'] = $value['razon_social'];

            array_push($array_resultado, $fila);
        }

        return json_encode($array_resultado);
    }

    function buscarProducto($almacen)
    {
        $searchTerm = filter_input(INPUT_GET, 'term');

        $resultados = $this->consulta->buscarProducto($_SESSION['id_empresa'], $searchTerm, $almacen);
        /*   echo 'asdasd';
            die(); */
        $array_resultado = array();
        foreach ($resultados as $value) {
            $serie = onlyTrim($value['serie_producto']) != '' ? ' | Serie # : ' . $value['serie_producto'] . ' ' : '';
            $fila = array();
            $fila['value'] = $value['codigo'] . $serie . ' | ' . $value['descripcion'] . " | P.Venta $ : " . $value['precio_unidad'] . " | Stock: " . $value['cantidad'];
            $fila['codigo'] = $value['id_producto'];
            $fila['codigo_pp'] = $value['codigo'];
            $fila['descripcion'] = $value['descripcion'];
            $fila['precio'] = $value['precio_unidad'];
            $fila['cnt'] = $value['cantidad'];
            $fila['costo'] = $value['costo'];
            $fila['precio2'] = $value['precio2'];
            $fila['precio3'] = $value['precio3'];
            $fila['precio4'] = $value['precio4'];
            $fila['precio_unidad'] = floatval($value['precio_unidad']);
            $fila['serie_producto'] = $value['serie_producto'];
	    
            array_push($array_resultado, $fila);
	    
        }

        return json_encode($array_resultado);
    }

    function buscarProducto2($almacen)
    {
        $searchTerm = filter_input(INPUT_GET, 'term');

        $resultados = $this->consulta->buscarProducto($_SESSION['id_empresa'], $searchTerm, $almacen);
        /*   echo 'asdasd';
            die(); */
        $array_resultado = array();
        foreach ($resultados as $value) {
            $serie = onlyTrim($value['serie_producto']) != '' ? ' | Serie # : ' . $value['serie_producto'] . ' ' : '';
            $fila = array();
            $fila['value'] = $value['codigo'] . $serie . ' | ' . $value['descripcion'] . " | P.Venta $ : " . $value['precio_unidad'] . " | Stock: " . $value['cantidad'];
            $fila['codigo'] = $value['id_producto'];
            $fila['codigo_pp'] = $value['codigo'];
            $fila['descripcion'] = $value['descripcion'];
            $fila['precio'] = $value['precio_unidad'];
            $fila['cnt'] = $value['cantidad'];
            $fila['costo'] = $value['costo'];
            $fila['precio2'] = $value['precio2'];
            $fila['precio3'] = $value['precio3'];
            $fila['precio4'] = $value['precio4'];
            $fila['precio_unidad'] = floatval($value['precio_unidad']);
            $fila['serie_producto'] = $value['serie_producto'];
	    if ($value['cantidad']>0) {
            array_push($array_resultado, $fila);
	    }    
        }

        return json_encode($array_resultado);
    }


    public function consultaStockAlmacen()
    {
        $almacen = $_POST["almacen"];
        $producto = $_POST["producto"];

        /*  where  */
        $sql = "SELECT * FROM productos WHERE id_producto = $producto AND almacen =$almacen AND id_empresa = '{$_SESSION['id_empresa']}' AND sucursal='{$_SESSION['sucursal']}'";

        $datos = $this->consulta->exeSQL($sql)->fetch_assoc();
        echo json_encode($datos);
    }

    function buscarProductoCoti()
    {
        $searchTerm = filter_input(INPUT_GET, 'term');
        $almacen = filter_input(INPUT_GET, 'almacen');
        $resultados = $this->consulta->buscarProductoCoti($_SESSION['id_empresa'], $searchTerm, $almacen);
        /*   echo 'asdasd';
            die(); */
        $array_resultado = array();
        foreach ($resultados as $value) {
            $serie = onlyTrim($value['serie_producto']) != '' ? ' | Serie # : ' . $value['serie_producto'] . ' ' : '';

            $fila = array();
            $fila['value'] = $value['codigo'] . $serie . ' | ' . $value['descripcion'] . " | P.Venta S/ : " . $value['precio'] . " | Stock: " . $value['cantidad'] . " - Almacen " . $value['almacen'];
            $fila['codigo'] = $value['id_producto'];
            $fila['codigo_pp'] = $value['codigo'];
            $fila['descripcion'] = $value['descripcion'];
            $fila['precio'] = $value['precio'];
            $fila['cnt'] = $value['cantidad'];
            $fila['costo'] = $value['costo'];
            $fila['precio2'] = $value['precio2'];
            $fila['precio3'] = $value['precio3'];
            $fila['almacen'] = $value['almacen'];
            $fila['precio4'] = $value['precio4'];
            $fila['precio_unidad'] = floatval($value['precio_unidad']);
            $fila['serie_producto'] = $value['serie_producto'];
            array_push($array_resultado, $fila);
        }

        return json_encode($array_resultado);
    }

    function cargarPreciosProd()
    {
	 /* 
	(CASE WHEN precio <1 THEN  
			ROUND((precio_unidad-(precio_unidad*(3/100))),0) ELSE 
			ROUND(precio,0) END) AS pvip,
		(CASE WHEN precio4 <1 THEN 
			ROUND((precio_unidad-(precio_unidad*(5/100))),0) ELSE 
			ROUND(precio4,0) END) AS pdistri	 

	
	 */

        $sql = "SELECT *, 
		(CASE WHEN (precio_unidad = precio) THEN  
			ROUND((costo+(costo*(10/100))),0) 
			WHEN (precio_unidad <1)THEN  
			ROUND((costo+(costo*(10/100))),0) 
			ELSE 
			ROUND(precio_unidad,0) END) as punidad,		

		(CASE WHEN precio_unidad = precio THEN  
			ROUND((costo+(costo*(3/100))),0) 
			WHEN (precio <1)THEN  
			ROUND((costo+(costo*(3/100))),0) 
			ELSE 
			ROUND(precio,0) END) AS pvip,
		(CASE WHEN precio4 <1 THEN 
			ROUND((costo+(costo*(5/100))),0) ELSE 
			ROUND(precio4,0) END) AS pdistri	 
               FROM  
		productos where id_empresa = '{$_SESSION['id_empresa']}' and sucursal = '{$_SESSION['sucursal']}' and estado = '1' AND id_producto='{$_POST['cod']}' order by id_producto DESC";

        $result = $this->consulta->exeSQL($sql)->fetch_assoc();
        echo json_encode($result);
    }

    function consultarGuiaXCoti()
    {
        $sql = "SELECT * FROM productos_cotis WHERE id_coti = '{$_POST['cod']}'";
        $lista = [];
        foreach ($this->consulta->exeSQL($sql) as $row) {
            /*  $lista[] = $row; */

            /* $listaTotal[] = ['detalle' => $row2['detalle'], 'salida' => 0, 'entrada' => $row2['entrada'], 'hora' => '-']; */
            $sql = "SELECT * FROM productos WHERE id_producto = '{$row['id_producto']}'";

            foreach ($this->consulta->exeSQL($sql) as $row2) {
                $lista[] = ['cantidad' => $row['cantidad'], 'costo' => $row['costo'], 'id_producto' => $row['id_producto'], 'precio' => $row['precio'], 'descripcion' => $row2['descripcion']];
            }
        }
        echo json_encode($lista);
    }

    function consultarGuiaXCotiCliente()
    {
        $sql = "SELECT c.datos,c.direccion FROM cotizaciones co JOIN clientes c ON co.id_cliente=c.id_cliente WHERE co.cotizacion_id ='{$_POST['cod']}'";
        $result = $this->consulta->exeSQL($sql)->fetch_assoc();
        return json_encode($result);
    }
}
