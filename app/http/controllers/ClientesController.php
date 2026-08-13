<?php

use Mpdf\Utils\Arrays;

require_once "app/models/Cliente.php";
require_once "utils/lib/exel/vendor/autoload.php";


class ClientesController extends Controller
{

    private $cliente;

    public function __construct()
    {
        $this->cliente = new Cliente();
        $this->conectar = (new Conexion())->getConexion();
    }



    public function insertarXLista()
    {
      /*   $lista = json_decode($_POST['lista'], true);
        echo json_encode($lista);
        die(); */
        $lista = json_decode($_POST['lista'], true);
        //var_dump($lista);
        $respuesta = ["res" => false];
        foreach ($lista as $item) {
           

            $datos = $item['datos'];
            $direccion = $item['direccion'];
            $direccion2 = $item['direccion2'];
            $departamento = isset($item['departamento']) ? $item['departamento'] : null;
            $provincia = isset($item['provincia']) ? $item['provincia'] : null;
            $distrito = isset($item['distrito']) ? $item['distrito'] : null;
            $fecha_nacimiento = isset($item['fecha_nacimiento']) ? $item['fecha_nacimiento'] : null;
            
            $sql = "INSERT into clientes set datos=?,
  documento='{$item['documento']}',
  direccion=?,
  direccion2=?,
  email='{$item['email']}',
  id_empresa='{$_SESSION['id_empresa']}',
  telefono='{$item['telefono']}',
  telefono2='{$item['telefono2']}',
  departamento=?,
  provincia=?,
  distrito=?,
  fecha_nacimiento=?";

            $stmt = $this->conectar->prepare($sql);
            $stmt->bind_param('sssssss', $datos, $direccion, $direccion2, $departamento, $provincia, $distrito, $fecha_nacimiento);
            if ($stmt->execute()) {
                $respuesta["res"] = true;
            }
        }
        return json_encode($respuesta);
    }
    public function insertar()
    {
        if (!empty($_POST)) {
            $doc = trim(filter_var($_POST['documentoAgregar'], FILTER_SANITIZE_NUMBER_INT));
            $datosAgregar = trim(filter_var($_POST['datosAgregar'], FILTER_SANITIZE_STRING));
            $direccionAgregar = trim(filter_var($_POST['direccionAgregar'], FILTER_SANITIZE_STRING));
            $direccionAgregar2 = trim(filter_var($_POST['direccionAgregar2'], FILTER_SANITIZE_STRING));
            $departamentoAgregar = isset($_POST['departamentoAgregar']) ? trim(filter_var($_POST['departamentoAgregar'], FILTER_SANITIZE_STRING)) : null;
            $provinciaAgregar = isset($_POST['provinciaAgregar']) ? trim(filter_var($_POST['provinciaAgregar'], FILTER_SANITIZE_STRING)) : null;
            $distritoAgregar = isset($_POST['distritoAgregar']) ? trim(filter_var($_POST['distritoAgregar'], FILTER_SANITIZE_STRING)) : null;
            $fecha_nacimientoAgregar = isset($_POST['fecha_nacimientoAgregar']) && !empty($_POST['fecha_nacimientoAgregar']) ? trim(filter_var($_POST['fecha_nacimientoAgregar'], FILTER_SANITIZE_STRING)) : null;
            $telefonoAgregar = trim(filter_var($_POST['telefonoAgregar'], FILTER_SANITIZE_NUMBER_INT));
            $telefonoAgregar2 = trim(filter_var($_POST['telefonoAgregar2'], FILTER_SANITIZE_NUMBER_INT));
            $direccion = trim(filter_var($_POST['direccion'], FILTER_VALIDATE_EMAIL, FILTER_SANITIZE_EMAIL));
            $telefonoIntVal = intval($telefonoAgregar);
            $docIntVal = intval($doc);
            if ($doc !== "" && $datosAgregar !== "") {
                $telefonoTrueInt = filter_var($telefonoIntVal, FILTER_VALIDATE_INT);
                $doctTrueInt = filter_var($docIntVal, FILTER_VALIDATE_INT);
                if ($doctTrueInt == true) {
                    $this->cliente->setDocumento($doc);
                    $this->cliente->setDatos($datosAgregar);
                    $this->cliente->setDireccion($direccionAgregar);
                    $this->cliente->setDireccion2($direccionAgregar2);
                    $this->cliente->setDepartamento($departamentoAgregar);
                    $this->cliente->setProvincia($provinciaAgregar);
                    $this->cliente->setDistrito($distritoAgregar);
                    $this->cliente->setFechaNacimiento($fecha_nacimientoAgregar);
                    $this->cliente->setTelefono($telefonoAgregar);
                    $this->cliente->setTelefono2($telefonoAgregar2);
                    $this->cliente->setEmail($direccion);
                    $save = $this->cliente->insertar();
                    if ($save == true) {
                        echo json_encode($this->cliente->idLast());
                    } else {
                        echo json_encode("Ocurrio un Error");
                    }
                } else {
                    echo json_encode('Llene el formulario correctamente 39');
                }
            } else {
                echo json_encode('Llene el formulario correctamente 42');
            }
        } else {
            echo json_encode('Error');
        }
    }
    public function render()
    {
        $getAll = $this->cliente->getAllData();
        echo json_encode($getAll);
    }
    public function getOne()
    {
        /* $presupuesto = new PresupuestosModel(); */
        $data = $_POST;
        $id = $data['id'];
        $getOne = $this->cliente->getOne($id);
        echo json_encode($getOne);
    }
    public function cuentasCobrar()
    {
        /* $presupuesto = new PresupuestosModel(); */

        $getAll = $this->cliente->cuentasCobrar();
        echo json_encode($getAll);
    }
    public function cuentasCobrarEstado()
    {
        $getAll = $this->cliente->cuentasCobrarEstado($_POST['id']);
        echo json_encode($getAll);
    }
    public function editar()
    {
        if (!empty($_POST)) {
            $doc = trim(filter_var($_POST['documentoEditar'], FILTER_SANITIZE_STRING));
            $datosEditar = trim(filter_var($_POST['datosEditar'], FILTER_SANITIZE_STRING));
            $direccionEditar = trim(filter_var($_POST['direccionEditar'], FILTER_SANITIZE_STRING));
            $direccionEditar2 = trim(filter_var($_POST['direccionEditar2'], FILTER_SANITIZE_STRING));
            $departamentoEditar = isset($_POST['departamentoEditar']) ? trim(filter_var($_POST['departamentoEditar'], FILTER_SANITIZE_STRING)) : null;
            $provinciaEditar = isset($_POST['provinciaEditar']) ? trim(filter_var($_POST['provinciaEditar'], FILTER_SANITIZE_STRING)) : null;
            $distritoEditar = isset($_POST['distritoEditar']) ? trim(filter_var($_POST['distritoEditar'], FILTER_SANITIZE_STRING)) : null;
            $fecha_nacimientoEditar = isset($_POST['fecha_nacimientoEditar']) && !empty($_POST['fecha_nacimientoEditar']) ? trim(filter_var($_POST['fecha_nacimientoEditar'], FILTER_SANITIZE_STRING)) : null;
            $telefonoEditar = trim(filter_var($_POST['telefonoEditar'], FILTER_SANITIZE_STRING));
            $telefonoEditar2 = trim(filter_var($_POST['telefonoEditar2'], FILTER_SANITIZE_STRING));
            $emailEditar = trim(filter_var($_POST['emailEditar'], FILTER_SANITIZE_EMAIL));
            $emailValidate = filter_var($emailEditar, FILTER_VALIDATE_EMAIL);
            $telefonoIntVal = intval($telefonoEditar);
            $docIntVal = intval($doc);
            $id = $_POST['idCliente'];
            if ($doc !== "" && $datosEditar !== "") {
                $telefonoTrueInt = filter_var($telefonoIntVal, FILTER_VALIDATE_INT);
                $doctTrueInt = filter_var($docIntVal, FILTER_VALIDATE_INT);

                if ($doctTrueInt == true && strlen($docIntVal) == 8 || strlen($docIntVal) == 11) {
                    $this->cliente->setDocumento($doc);
                    $this->cliente->setDatos($datosEditar);
                    $this->cliente->setDireccion($direccionEditar);
                    $this->cliente->setDireccion2($direccionEditar2);
                    $this->cliente->setDepartamento($departamentoEditar);
                    $this->cliente->setProvincia($provinciaEditar);
                    $this->cliente->setDistrito($distritoEditar);
                    $this->cliente->setFechaNacimiento($fecha_nacimientoEditar);
                    $this->cliente->setTelefono($telefonoEditar);
                    $this->cliente->setTelefono2($telefonoEditar2);
                    $this->cliente->setEmail($emailEditar);
                    $save = $this->cliente->editar($_POST['idCliente']);
                    if ($save == true) {
                        echo json_encode($this->cliente->getOne($id));
                    } else {
                        echo json_encode("Ocurrio un Error");
                    }
                } else {
                    echo json_encode('Llene el formulario correctamente');
                }
            } else {
                echo json_encode('Llene el formulario correctamente');
            }
        } else {
            echo json_encode('Error');
        }
    }
    public function borrar()
    {
        $dataId = $_POST["value"];
        $save = $this->cliente->delete($dataId);
        if ($save) {
            echo json_encode("nice");
        } else {
            echo json_encode("error");
        }
    }

    public function importarExcel()
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

            $spreadsheet = $reader->load("files/temp/" . $nombre_logo);

            $worksheet = $spreadsheet->getActiveSheet();
            $data = [];
            foreach ($worksheet->getRowIterator() as $row) {
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getFormattedValue();
                }
                $data[] = $rowData;
            }
            $respuesta["data"] = $data;

            unlink($location);
            //return $schdeules;
            /*   $last = $this->cliente->idLast();
            $arr = array($respuesta, $last); */
        }

        return json_encode($respuesta);
    }
    /*   public function importAdd(){
        echo json_encode($_POST);
    } */

    public function exportarExcel()
    {
        $getAll = $this->cliente->getAllData();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Cabeceras
        $sheet->setCellValue('A1', 'Documento');
        $sheet->setCellValue('B1', 'Nombres/Razon Social');
        $sheet->setCellValue('C1', 'Direccion');
        $sheet->setCellValue('D1', 'Direccion Llegada');
        $sheet->setCellValue('E1', 'Telefono 1');
        $sheet->setCellValue('F1', 'Telefono 2');
        $sheet->setCellValue('G1', 'Email');
        $sheet->setCellValue('H1', 'Departamento');
        $sheet->setCellValue('I1', 'Provincia');
        $sheet->setCellValue('J1', 'Distrito');
        $sheet->setCellValue('K1', 'Fecha Nacimiento');

        // Datos
        $fila = 2;
        foreach ($getAll as $c) {
            $cFull = current($this->cliente->getOne($c['id_cliente'])) ?: [];
            if(empty($cFull)) continue;
            
            $sheet->setCellValue('A' . $fila, $cFull['documento'] ?? '');
            $sheet->setCellValue('B' . $fila, $cFull['datos'] ?? '');
            $sheet->setCellValue('C' . $fila, $cFull['direccion'] ?? '');
            $sheet->setCellValue('D' . $fila, $cFull['direccion2'] ?? '');
            $sheet->setCellValue('E' . $fila, $cFull['telefono'] ?? '');
            $sheet->setCellValue('F' . $fila, $cFull['telefono2'] ?? '');
            $sheet->setCellValue('G' . $fila, $cFull['email'] ?? '');
            $sheet->setCellValue('H' . $fila, $cFull['departamento'] ?? '');
            $sheet->setCellValue('I' . $fila, $cFull['provincia'] ?? '');
            $sheet->setCellValue('J' . $fila, $cFull['distrito'] ?? '');
            $sheet->setCellValue('K' . $fila, $cFull['fecha_nacimiento'] ?? '');
            $fila++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="clientes_export.xlsx"');
        $writer->save('php://output');
        exit;
    }
}
