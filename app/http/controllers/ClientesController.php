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
  id_usuario='" . (int) ($_SESSION['usuario_fac'] ?? 0) . "',
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
            // Sin validaciones: el documento es opcional y se guarda tal como se escribe.
            // Lo unico que se pide es el nombre / razon social.
            $doc = $this->conectar->real_escape_string(trim($_POST['documentoAgregar'] ?? ''));
            $datosAgregar = trim(filter_var($_POST['datosAgregar'] ?? '', FILTER_SANITIZE_STRING));
            $direccionAgregar = trim(filter_var($_POST['direccionAgregar'] ?? '', FILTER_SANITIZE_STRING));
            $direccionAgregar2 = trim(filter_var($_POST['direccionAgregar2'] ?? '', FILTER_SANITIZE_STRING));
            $departamentoAgregar = isset($_POST['departamentoAgregar']) ? trim(filter_var($_POST['departamentoAgregar'], FILTER_SANITIZE_STRING)) : null;
            $provinciaAgregar = isset($_POST['provinciaAgregar']) ? trim(filter_var($_POST['provinciaAgregar'], FILTER_SANITIZE_STRING)) : null;
            $distritoAgregar = isset($_POST['distritoAgregar']) ? trim(filter_var($_POST['distritoAgregar'], FILTER_SANITIZE_STRING)) : null;
            $fecha_nacimientoAgregar = !empty($_POST['fecha_nacimientoAgregar']) ? trim(filter_var($_POST['fecha_nacimientoAgregar'], FILTER_SANITIZE_STRING)) : null;
            $tipoDocumentoAgregar = isset($_POST['tipoDocumentoAgregar']) ? trim($_POST['tipoDocumentoAgregar']) : '1';
            $telefonoAgregar = trim(filter_var($_POST['telefonoAgregar'] ?? '', FILTER_SANITIZE_STRING));
            $telefonoAgregar2 = trim(filter_var($_POST['telefonoAgregar2'] ?? '', FILTER_SANITIZE_STRING));
            $direccion = trim(filter_var($_POST['direccion'] ?? '', FILTER_SANITIZE_EMAIL));

            if ($datosAgregar === "") {
                echo json_encode('Escriba el nombre o razon social del cliente');
                return;
            }

            $this->cliente->setDocumento($doc);
            $this->cliente->setTipoDocumento($tipoDocumentoAgregar);
            if (isset($_POST['idUsuarioAgregar'])) {
                $this->cliente->setIdUsuarioNuevo($_POST['idUsuarioAgregar']);
            }
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
            // Sin validaciones: el documento es opcional y se guarda tal como se escribe.
            // Lo unico que se pide es el nombre / razon social.
            $doc = $this->conectar->real_escape_string(trim($_POST['documentoEditar'] ?? ''));
            $datosEditar = trim(filter_var($_POST['datosEditar'] ?? '', FILTER_SANITIZE_STRING));
            $direccionEditar = trim(filter_var($_POST['direccionEditar'] ?? '', FILTER_SANITIZE_STRING));
            $direccionEditar2 = trim(filter_var($_POST['direccionEditar2'] ?? '', FILTER_SANITIZE_STRING));
            $departamentoEditar = isset($_POST['departamentoEditar']) ? trim(filter_var($_POST['departamentoEditar'], FILTER_SANITIZE_STRING)) : null;
            $provinciaEditar = isset($_POST['provinciaEditar']) ? trim(filter_var($_POST['provinciaEditar'], FILTER_SANITIZE_STRING)) : null;
            $distritoEditar = isset($_POST['distritoEditar']) ? trim(filter_var($_POST['distritoEditar'], FILTER_SANITIZE_STRING)) : null;
            $fecha_nacimientoEditar = !empty($_POST['fecha_nacimientoEditar']) ? trim(filter_var($_POST['fecha_nacimientoEditar'], FILTER_SANITIZE_STRING)) : null;
            $tipoDocumentoEditar = isset($_POST['tipoDocumentoEditar']) ? trim($_POST['tipoDocumentoEditar']) : '1';
            $telefonoEditar = trim(filter_var($_POST['telefonoEditar'] ?? '', FILTER_SANITIZE_STRING));
            $telefonoEditar2 = trim(filter_var($_POST['telefonoEditar2'] ?? '', FILTER_SANITIZE_STRING));
            $emailEditar = trim(filter_var($_POST['emailEditar'] ?? '', FILTER_SANITIZE_EMAIL));
            $id = $_POST['idCliente'] ?? '';

            if ($datosEditar === "") {
                echo json_encode('Escriba el nombre o razon social del cliente');
                return;
            }
            if ($id === "") {
                echo json_encode('No se identifico el cliente a editar');
                return;
            }

            $this->cliente->setDocumento($doc);
            $this->cliente->setTipoDocumento($tipoDocumentoEditar);
            if (isset($_POST['idUsuarioEditar'])) {
                $this->cliente->setIdUsuarioNuevo($_POST['idUsuarioEditar']);
            }
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
            $save = $this->cliente->editar($id);
            if ($save == true) {
                echo json_encode($this->cliente->getOne($id));
            } else {
                echo json_encode("Ocurrio un Error");
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

    /**
     * Arma el Excel de clientes con las mismas columnas que la tabla en pantalla
     * (mas Direccion 2 y Telefono 2, que tambien estan en la BD).
     * Se separa de exportarExcel() para poder probarlo sin enviar cabeceras HTTP.
     */
    public function armarExcelClientes()
    {
        $clientes = $this->cliente->getAllData();
        $clientes = is_array($clientes) ? $clientes : [];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clientes');

        // [titulo, ancho, alineacion]
        $columnas = [
            ['Item', 7, 'center'],
            ['Tipo Doc.', 14, 'center'],
            ['Documento', 16, 'left'],
            ['Nombre/Razon Social', 42, 'left'],
            ['Vendedor', 26, 'left'],
            ['Direccion', 40, 'left'],
            ['Direccion 2', 30, 'left'],
            ['Telefono', 14, 'left'],
            ['Telefono 2', 14, 'left'],
            ['Email', 30, 'left'],
            ['Departamento', 16, 'left'],
            ['Provincia', 16, 'left'],
            ['Distrito', 18, 'left'],
            ['F. Nacimiento', 14, 'center'],
            ['S/ Venta', 14, 'right'],
            ['Ultima Venta', 14, 'center'],
        ];
        $letras = range('A', 'Z');
        $ultimaCol = $letras[count($columnas) - 1];
        $colVenta = 'O'; // S/ Venta
        $colTotalLabel = 'N';

        // ---- Titulo ----
        $sheet->mergeCells("A1:{$ultimaCol}1");
        $sheet->setCellValue('A1', 'LISTA DE CLIENTES');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('0866C6');
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->mergeCells("A2:{$ultimaCol}2");
        $sheet->setCellValue('A2', trim(($_SESSION['nombre_empresa'] ?? '') . '   |   Exportado: ' . date('d/m/Y H:i') . '   |   Total clientes: ' . count($clientes), ' |'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('666666');

        // ---- Cabecera ----
        $filaCab = 4;
        foreach ($columnas as $i => $col) {
            $sheet->setCellValue($letras[$i] . $filaCab, $col[0]);
            $sheet->getColumnDimension($letras[$i])->setWidth($col[1]);
        }
        $rangoCab = "A{$filaCab}:{$ultimaCol}{$filaCab}";
        $sheet->getStyle($rangoCab)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0866C6']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => ['allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '0B4F8F'],
            ]],
        ]);
        $sheet->getRowDimension($filaCab)->setRowHeight(22);

        // ---- Datos ----
        $fmtFecha = function ($f) {
            return ($f && $f !== '0000-00-00') ? date('d/m/Y', strtotime($f)) : '';
        };
        $fila = $filaCab + 1;
        $item = 1;
        foreach ($clientes as $c) {
            $valores = [
                $item++,
                $c['tipo_documento_desc'] ?? '',
                $c['documento'] ?? '',
                $c['datos'] ?? '',
                $c['vendedor'] ?? '',
                $c['direccion'] ?? '',
                $c['direccion2'] ?? '',
                $c['telefono'] ?? '',
                $c['telefono2'] ?? '',
                $c['email'] ?? '',
                $c['departamento'] ?? '',
                $c['provincia'] ?? '',
                $c['distrito'] ?? '',
                $fmtFecha($c['fecha_nacimiento'] ?? ''),
                (float) ($c['total_venta'] ?? 0),
                $fmtFecha($c['ultima_venta'] ?? ''),
            ];
            foreach ($valores as $i => $v) {
                $celda = $letras[$i] . $fila;
                if ($i === 0 || $letras[$i] === $colVenta) {
                    $sheet->setCellValue($celda, $v);
                } else {
                    // Texto explicito: conserva ceros a la izquierda en documentos y telefonos
                    $sheet->setCellValueExplicit($celda, trim((string) $v), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
            if ($fila % 2 === 0) {
                $sheet->getStyle("A{$fila}:{$ultimaCol}{$fila}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F3F7FC');
            }
            $fila++;
        }
        $ultimaFila = $fila - 1;

        if ($ultimaFila > $filaCab) {
            $primeraFila = $filaCab + 1;
            $rangoDatos = "A{$primeraFila}:{$ultimaCol}{$ultimaFila}";
            $sheet->getStyle($rangoDatos)->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');
            $sheet->getStyle($rangoDatos)->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            foreach ($columnas as $i => $col) {
                $sheet->getStyle("{$letras[$i]}{$primeraFila}:{$letras[$i]}{$ultimaFila}")
                    ->getAlignment()->setHorizontal($col[2]);
            }
            $sheet->getStyle("{$colVenta}{$primeraFila}:{$colVenta}{$ultimaFila}")
                ->getNumberFormat()->setFormatCode('#,##0.00');

            // ---- Total ----
            $filaTot = $ultimaFila + 1;
            $sheet->setCellValue("{$colTotalLabel}{$filaTot}", 'TOTAL');
            $sheet->setCellValue("{$colVenta}{$filaTot}", "=SUM({$colVenta}{$primeraFila}:{$colVenta}{$ultimaFila})");
            $sheet->getStyle("{$colTotalLabel}{$filaTot}:{$colVenta}{$filaTot}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]],
            ]);
            $sheet->getStyle("{$colTotalLabel}{$filaTot}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("{$colVenta}{$filaTot}")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // ---- Filtro, cabecera fija e impresion ----
        $sheet->setAutoFilter($rangoCab);
        $sheet->freezePane('A' . ($filaCab + 1));
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)->setFitToHeight(0);

        return $spreadsheet;
    }

    public function exportarExcel()
    {
        $spreadsheet = $this->armarExcelClientes();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="clientes_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
