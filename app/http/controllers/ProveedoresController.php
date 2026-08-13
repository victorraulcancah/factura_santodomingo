<?php

    use Mpdf\Utils\Arrays;

    require_once "app/models/Proveedores.php";
    require_once "utils/lib/exel/vendor/autoload.php";


    class ProveedoresController extends Controller
    {

        private $cliente;
        private $models;

        public function __construct() {
            $this->modelo = new models();
        }

        public function render() {
            $result = $this->modelo->setTable('proveedores')
                ->setWhere(['id_empresa' => $_SESSION['id_empresa']])
                ->setOrderBy(['fecha_create'])
                ->desc()
                ->selectAll();
            #console($result);
            #$getAll = $this->cliente->getAllData();
            echo json_encode($result);
        }

        public function delete(Request $request) {
            $result = $this->modelo->setTable('proveedores')
                ->delete(['proveedor_id' => $request->id]);
        }

        public function get(Request $request) {
            $result = $this->modelo->setTable('proveedores')
                ->select(['proveedor_id' => $request->id]);
            echo json_encode($result);
        }

        public function update(Request $request) {
            $requestData = getVarsPost($_POST);
            $result = $this->modelo->setTable('proveedores')
                ->update(['proveedor_id' => $request->proveedor_id], $requestData);
            if ($result->estatus):
                echo json_encode([$result]);
            else:
                echo json_encode('Error al editar');
            endif;
        }

        public function set(Request $request) {
            $requestData = getVarsPost($_POST);
            $requestData->id_empresa = $_SESSION['id_empresa'];
            $result = $this->modelo->setTable('proveedores')
                ->insert($requestData, true);
            #console($result);
            if ($result->estatus):
                echo json_encode([$result]);
            else:
                echo json_encode('Error al editar');
            endif;
        }

        public function search(Request $request) {
            $requestData = getVarsPost($_GET);

            $result = $this->modelo->setTable('proveedores')
                ->setColums(["
                    proveedor_id AS id,
                    ruc AS value,
                     CONCAT(ruc,' | ',razon_social) AS label
                "])
                ->setWhere([['ruc' => "%$requestData->term%", 'razon_social' => "%$requestData->term%"]])
                ->setLimit(10)
                ->setOrderBy(['razon_social'])->asc()
                ->selectAll();


            echo json_encode($result);
            #$requestData->id_empresa = $_SESSION['id_empresa'];
            #$result = $this->modelo->setTable('proveedores')
            #    ->insert($requestData, true);
            ##console($result);
            #if ($result->estatus):
            #    echo json_encode([$result]);
            #else:
            #    echo json_encode('Error al editar');
            #endif;
        }

        public function importarExcel() {
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
                /*   $last = $this->cliente->idLast();
                $arr = array($respuesta, $last); */
            }

            return json_encode($respuesta);
        }

        public function insertarXLista() {
            $lista = json_decode($_POST['lista'], true);

            $respuesta = ["res" => false];
            foreach ($lista as $item) {
                $item->id_empresa = $_SESSION['id_empresa'];
                $result = $this->modelo->setTable('proveedores')
                    ->insert($item);
                #console($result);
                if ($result->estatus):
                    $respuesta["res"] = true;
                endif;
            }
            return json_encode($respuesta);
        }

    }