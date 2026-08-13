<?php

    use Mpdf\Utils\Arrays;

    require_once "app/models/Usuario.php";
    require_once "utils/lib/exel/vendor/autoload.php";


    class UsuariosController extends Controller
    {

        private $cliente;
        private $models;

        public function __construct() {
            $this->modelo = new models();
        }

        public function render() {
            $result = $this->modelo->setTable('usuarios')
                ->setWhere(['id_empresa' => $_SESSION['id_empresa']])
                ->setOrderBy(['fecha_create'])
                ->desc()
                ->selectAll();
            echo json_encode($result);
        }

        public function delete(Request $request) {
            $result = $this->modelo->setTable('usuarios')
                ->delete(['usuario_id' => $request->id]);
        }

        public function get(Request $request) {
            $result = $this->modelo->setTable('usuarios')
                ->select(['usuario_id' => $request->id]);
            echo json_encode($result);
        }

        public function update(Request $request) {
            $requestData = getVarsPost($_POST);
            $requestData->id_empresa = $_SESSION['id_empresa'];
            $result = $this->modelo->setTable('usuarios')
                ->update(['usuario_id' => $request->usuario_id], $requestData);
            if ($result->estatus):
                echo json_encode([$result]);
            else:
                echo json_encode('Error al editar');
            endif;
        }

        public function set(Request $request) {
            $requestData = getVarsPost($_POST);
            $requestData->id_empresa = $_SESSION['id_empresa'];
            $requestData->nombres = onlyReduceToOneSpace($requestData->nombres);
            $requestData->apellidos = onlyReduceToOneSpace($requestData->apellidos);
            $requestData->num_doc = onlyReduceToOneSpace($requestData->num_doc);
            $requestData->email = onlyReduceToOneSpace($requestData->email);
            $requestData->direccion = onlyReduceToOneSpace($requestData->direccion);
            $requestData->direccion = onlyReduceToOneSpace($requestData->direccion);
            $requestData->clave = sha1(onlyReduceToOneSpace($requestData->clave));

            $result = $this->modelo->setTable('usuarios')
                ->insert($requestData, true);
            #console($result);
            if ($result->estatus):
                echo json_encode([$result]);
            else:
                echo json_encode('Error al editar');
            endif;
        }
    }