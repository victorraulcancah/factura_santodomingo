<?php

    require_once 'utils/lib/vendor/autoload.php';
    require_once 'utils/lib/mpdf/vendor/autoload.php';
    require_once 'utils/lib/exel/vendor/autoload.php';
    require_once "app/models/Producto.php";
    require_once "app/models/Venta.php";
    require_once "app/models/Varios.php";

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Reader\Html;

    class GenerarReporte extends Controller
    {
        private $conexion;

        /*  private $mpdf; */

        public function __construct() {
            /*  $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']); */
            $this->conexion = (new Conexion())->getConexion();
        }

        public function kardex_balance(Request $request) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            $dias_semana = array(
                "Monday" => "lunes",
                "Tuesday" => "martes",
                "Wednesday" => "miércoles",
                "Thursday" => "jueves",
                "Friday" => "viernes",
                "Saturday" => "sábado",
                "Sunday" => "domingo"
            );

            $desde = htmlspecialchars($request->desde);
            $hasta = htmlspecialchars($request->hasta);

            $c_producto = new Producto();
            $c_producto->setIdEmpresa($_SESSION['id_empresa']);
            $result = $c_producto->ver_kardex_productos($request->almacenId, $request->desde, $request->hasta);

            $empresa = $this->conexion->query("SELECT * FROM empresas WHERE id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();

            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'L']);

            $mpdf->WriteHTML("
        table, th, #informacion td {
            border: 1px solid #1f1f1f;
            color: #1f1f1f;
            border-collapse: collapse;
            font-size: 12px;
            padding: 3px 5px;
        }
        #cuerpo th, #cuerpo_info th {
            background: yellow;
        }
        #cuerpo td {
            border: 1px solid #1f1f1f;
            border-collapse: collapse;
            font-size: 11px;
            padding: 1px 3px;
        }
        #cuerpo .text-right, #cuerpo_info .text-right {
            text-align: right;
            padding-right: 8px;
        }
        #cuerpo .footer-td {
            background: yellow;
            font-size: 15px !important;
            font-weight: 500 !important;
            padding: 4px 3px;
            text-align: right;
        }
        #cuerpo .no-footer-td {
            border: 0px;
        }
        ", \Mpdf\HTMLParserMode::HEADER_CSS);

            $rowHtml = '';

            $data = new stdClass();
            while ($row = $result->fetch_assoc()) {
                $data->capital = $data->capital ?? 0;
                $data->venta = $data->venta ?? 0;

                $data->entradas_cantidad = $data->entradas_cantidad ?? 0;
                $data->entradas_costo_unitario = $data->entradas_costo_unitario ?? 0;
                $data->entradas_costo_total = $data->entradas_costo_total ?? 0;

                $data->salidas_cantidad = $data->salidas_cantidad ?? 0;
                $data->salidas_costo_unitario = $data->salidas_costo_unitario ?? 0;
                $data->salidas_costo_total = $data->salidas_costo_total ?? 0;

                $data->total_productos = $data->total_productos ?? 0;
                $data->total_productos++;
		 

                $row['entradas_cantidad'] = number_format(round($row['entradas_cantidad'], 2), 2, '.', '');
                $row['entradas_costo_unitario'] = number_format(round($row['entradas_costo_unitario'], 2), 2, '.', '');
                $row['entradas_costo_total'] = number_format(round($row['entradas_costo_total'], 2), 2, '.', '');

                $row['salidas_cantidad'] = number_format(round($row['salidas_cantidad'], 2), 2, '.', '');
                $row['salidas_costo_unitario'] = number_format(round($row['salidas_costo_unitario'], 2), 2, '.', '');
                $row['salidas_costo_total'] = number_format(round($row['salidas_costo_total'], 2), 2, '.', '');

                $data->capital += $row['entradas_costo_total'];
                $data->venta += $row['salidas_costo_total'];

                $data->entradas_cantidad += $row['entradas_cantidad'];
                $data->entradas_costo_unitario += $row['entradas_costo_unitario'];
                $data->entradas_costo_total += $row['entradas_costo_total'];

                $data->salidas_cantidad += $row['salidas_cantidad'];
                $data->salidas_costo_unitario += $row['salidas_costo_unitario'];
                $data->salidas_costo_total += $row['salidas_costo_total'];

                $data->ganancias = $data->ganancias ?? 0;
                $data->acum_total = $data->acum_total ?? 0;
                $data->acum_entrada = $data->acum_entrada ?? 0;
                $ganancias = $row['salidas_costo_total'] - ($row['salidas_cantidad'] * $row['entradas_costo_unitario']);
                $ganancias = number_format(round($ganancias, 2), 2, '.', '');
		 
		 if ($row['salidas_cantidad'] > $row['entradas_cantidad'] ){

	                $cant_total = $row['salidas_cantidad'];
	        } else {
		        $cant_total = $row['entradas_cantidad'] - $row['salidas_cantidad'];

		}
	        $can_ent = $row['entradas_cantidad'] + $row['salidas_cantidad'];

                $data->ganancias += $ganancias;
		 $data->acum_total +=$row['entradas_cantidad'];
                $data->acum_entrada += $can_ent;


                $rowHtml .= "<tr>
                <td style='border: 2px dashed black;'>" . htmlspecialchars($row['producto']) . "</td>
                <td style='border: 2px dashed black;'>" . htmlspecialchars($row['kardex_empresa_proveedor']) . "</td>
                <td style='border: 2px dashed black;'>" . htmlspecialchars($row['kardex_operacion']) . "</td>
                
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($can_ent) . "</td>
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($row['entradas_costo_unitario']) . "</td>
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($row['entradas_costo_total']) . "</td>
                
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($row['salidas_cantidad']) . "</td>
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($row['salidas_costo_unitario']) . "</td>
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($row['salidas_costo_total']) . "</td>
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($row['entradas_cantidad']) . "</td>
                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($row['salidas_costo_unitario']) . "</td>

                <td class='text-right' style='border: 2px dashed black;'>" . htmlspecialchars($ganancias) . "</td>
            </tr>";
            }

            $data->capital = number_format(round($data->capital ?? 0, 2), 2, '.', '');
            $data->venta = number_format(round($data->venta ?? 0, 2), 2, '.', '');
            $data->total = $data->venta - $data->capital;

            $html = "
        <div style='width: 100%; '>
            <div style='width: 100%; text-align: center;'>
                <h3 style=''>REPORTE DE CONTROL INTERNO DE PRODUCTOS (Kardex)</h3>
            </div>
            <div style='width: 100%;'>
                <table style='width: 100%;' id='informacion'>
                    <tr>
                        <td><strong>PERIODO:</strong></td>
                        <td><strong> " . htmlspecialchars($request->desde) . " | " . htmlspecialchars($request->hasta) . " </strong></td>
                    </tr>
                    <tr>
                        <td><strong>RAZON SOCIAL:</strong></td>
                        <td style='text-align: left;'><strong>" . htmlspecialchars($empresa["ruc"]) . " | " . htmlspecialchars($empresa['razon_social']) . "</strong></td>
                    </tr>


                    <tr>
                        <td><strong>CANTIDAD DE PRODUCTOS:</strong></td>
                        <td style='text-align: left;'><strong>" . htmlspecialchars($data->total_productos ?? 0) . "</strong></td>
                    </tr>


                </table>
            </div>
            
            <div style='width: 100%; margin-top: 40px;'>
                <table style='width: 100%; text-align: center;' id='cuerpo'>
                    <tbody>
                    <tr>
                        <th rowspan='2' style='border: 2px solid black; text-align: center;'><strong>PRODUCTO</strong></th>
                        <th rowspan='2' style='border: 2px solid black; text-align: center;'><strong>PROVEEDOR</strong></th>
                        <th rowspan='2' style='border: 2px solid black; text-align: center;'><strong>TIPO DE OPERACION</strong></th>
                        
                        <th colspan='3' style='border: 2px solid black; text-align: center;'><strong>ENTRADAS</strong></th>
                        <th colspan='3' style='border: 2px solid black; text-align: center;'><strong>SALIDAS</strong></th>
                        <th colspan='3' style='border: 2px solid black; text-align: center;'><strong>SALDO FINAL</strong></th>
                    </tr>
                    <tr>
                        <th style='border: 2px solid black;'>Cantidad</th>
                        <th style='border: 2px solid black;'>Costo Unitario</th>
                        <th style='border: 2px solid black;'>Costo Total</th>
                        <th style='border: 2px solid black;'>Cantidad</th>
                        <th style='border: 2px solid black;'>Costo Unitario</th>
                        <th style='border: 2px solid black;'>Costo Total</th>
                        <th style='border: 2px solid black;'>Cantidad</th>
                        <th style='border: 2px solid black;'>Costo Unitario</th>
                        <th style='border: 2px solid black;'>Costo Total</th>

                    </tr> 
                   $rowHtml
                   <tr>
                        <td colspan='3' class='no-footer-td'></td>
                        <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->acum_entrada ?? 0) . "</td>
                        <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->entradas_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->entradas_costo_total ?? 0) . "</td>
                        <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->salidas_cantidad ?? 0) . "</td>
                        <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->salidas_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->salidas_costo_total ?? 0) . "</td>
	  		 <td class='footer-td'  style='border: 2px solid dotted;'>" . htmlspecialchars($data->acum_total ?? 0) . "</td>
                       <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->salidas_costo_unitario ?? 0) . "</td>
                       <td class='footer-td' style='border: 2px solid dotted;'>" . htmlspecialchars($data->ganancias ?? 0) . "</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        ";

            if ($request->tipo == 'pdf') {
                $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
                $mpdf->Output();
            } else {
                $spreadsheet = new Spreadsheet();
                $reader = new Html();
                $spreadsheet = $reader->loadFromString($html);


                $sheet = $spreadsheet->getActiveSheet();
                foreach (range('B', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getColumnDimension('A')->setWidth(60);
                $writer = new Xlsx($spreadsheet);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="kardex.xlsx"');
                $writer->save('php://output');
            }
        }

        public function compras_balance(Request $request) {
            $dias_semana = array(
                "Monday" => "lunes",
                "Tuesday" => "martes",
                "Wednesday" => "miércoles",
                "Thursday" => "jueves",
                "Friday" => "viernes",
                "Saturday" => "sábado",
                "Sunday" => "domingo"
            );

            $desde = $request->desde;
            $hasta = $request->hasta;

            $c_producto = new Producto();
            $c_producto->setIdEmpresa($_SESSION['id_empresa']);
            $result = $c_producto->ver_compras_balance($request->almacenId, $request->desde, $request->hasta);
            #echo "<pre>";
            #var_dump($result);
            #echo "</pre>";

            $empresa = $this->conexion->query("select * from empresas
        where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();


            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'L']);

            $mpdf->WriteHTML("
        table, th, #informacion td {
          border: 1px solid #1f1f1f;
          color : #1f1f1f;
          border-collapse: collapse;
          font-size:12px;
          padding: 3px 5px;
        }
         #cuerpo th ,#cuerpo_info th {
            background: yellow
         }
         #cuerpo td {
          border: 1px solid #1f1f1f;
          border-collapse: collapse;
          font-size:11px;
          padding: 1px 3px;
        }
        #cuerpo .text-right,#cuerpo_info .text-right{
            text-align: right;
            padding-right: 8px;
        }
        #cuerpo .footer-td{
            background: yellow;
            font-size: 15px !important;
            font-weight: 500 !important;
             padding: 4px 3px;
              text-align: right;
        }
         #cuerpo .no-footer-td{
            border: 0px;
        }
        " .
                "
        ", \Mpdf\HTMLParserMode::HEADER_CSS);

            $rowHmtl = '';
            #$sql = "SELECT ingreso_egreso.*,productos.descripcion,IF(ingreso_egreso.tipo = 'e', 'Egreso', 'Ingreso') AS tipoIntercambio FROM ingreso_egreso JOIN productos ON ingreso_egreso.id_producto=productos.id_producto WHERE intercambio_id = '$id'";
            #$result = $this->conexion->query($sql)->fetch_assoc();

            /*$sql = "SELECT * FROM usuarios WHERE usuario_id = {$result['id_usuario']}";
            $resul2 = $this->conexion->query($sql)->fetch_assoc();*/
            /*  $sql */

            $data = new stdClass();
            while ($row = $result->fetch_assoc()) {
                $data->capital = $data->capital ?? 0;
                $data->venta = $data->venta ?? 0;
                $data->entradas_cantidad = $data->entradas_cantidad ?? 0;
                $data->entradas_costo_unitario = $data->entradas_costo_unitario ?? 0;
                $data->entradas_costo_total = $data->entradas_costo_total ?? 0;


                $data->final_cantidad = $data->final_cantidad ?? 0;
                $data->final_costo_unitario = $data->final_costo_unitario ?? 0;
                $data->final_costo_total = $data->final_costo_total ?? 0;


                $data->total_productos = $data->total_productos ?? 0;
                $data->total_productos++;

                #echo "<pre>";
                #var_dump($row);
                #echo "</pre>";
                #<td style='width:auto'>{$row['intercambio_id']}</td>

                #die();
                $row['comprobante_dia'] = $dias_semana[$row['comprobante_dia']] ?? $row['comprobante_dia'];

                $row['entradas_cantidad'] = number_format(round($row['entradas_cantidad'], 2), 2, '.', '');
                $row['entradas_costo_unitario'] = number_format(round($row['entradas_costo_unitario'], 2), 2, '.', '');
                $row['entradas_costo_total'] = number_format(round($row['entradas_costo_total'], 2), 2, '.', '');


                $row['final_cantidad'] = number_format(round($row['final_cantidad'], 2), 2, '.', '');
                $row['final_costo_unitario'] = number_format(round($row['final_costo_unitario'], 2), 2, '.', '');
                $row['final_costo_total'] = number_format(round($row['final_costo_total'], 2), 2, '.', '');

                $data->capital += $row['entradas_costo_total'];
                $data->venta += $row['final_costo_total'];

                $data->entradas_cantidad += $row['entradas_cantidad'];
                $data->entradas_costo_unitario += $row['entradas_costo_unitario'];
                $data->entradas_costo_total += $row['entradas_costo_total'];

                $data->final_cantidad += $row['final_cantidad'];
                $data->final_costo_unitario += $row['entradas_costo_unitario'];
                $data->final_costo_total += $row['final_costo_total'];

                $rowHmtl .= "<tr>
                <td>" . htmlspecialchars($row['producto']) . "</td>
                <td>" . htmlspecialchars($row['comprobante_dia']) . "</td>
                <td>" . htmlspecialchars($row['comprobante_fecha']) . "</td>
                <td>" . htmlspecialchars($row['comprobante_tipo']) . "</td>
                <td>" . htmlspecialchars($row['comprobante_serie']) . "</td>
                <td>" . htmlspecialchars($row['comprobante_numero']) . "</td>
                <td>" . htmlspecialchars($row['kardex_operacion']) . "</td>
                <td>" . htmlspecialchars($row['kardex_destino']) . "</td>
                <td>" . htmlspecialchars($row['kardex_empresa']) . "</td>
                
                <td class='text-right'>" . htmlspecialchars($row['entradas_cantidad']) . "</td>
                <td class='text-right'>" . htmlspecialchars($row['entradas_costo_unitario']) . "</td>
                <td class='text-right'>" . htmlspecialchars($row['entradas_costo_total']) . "</td>
                
                <td class='text-right'>" . htmlspecialchars($row['salidas_cantidad']) . "</td>
                <td class='text-right'>" . htmlspecialchars($row['salidas_costo_unitario']) . "</td>
                <td class='text-right'>" . htmlspecialchars($row['salidas_costo_total']) . "</td>
                
                <td class='text-right'>" . htmlspecialchars($row['final_cantidad']) . "</td>
                <td class='text-right'>" . htmlspecialchars($row['final_costo_unitario']) . "</td>
                <td class='text-right'>" . htmlspecialchars($row['final_costo_total']) . "</td>
            </tr>";

            }

            $data->capital = number_format(round($data->capital ?? 0, 2), 2, '.', '');
            $data->venta = number_format(round($data->venta ?? 0, 2), 2, '.', '');
            $data->total = $data->venta - $data->capital ?? 0;

            $html = "
         
        <div style='width: 100%; '>
            <div style='width: 100%; text-align: center;'>
                    <h3 style=''>REPORTE DE COMPRAS DE PRODUCTOS (Compras)</h3>
            </div>
            <div style='width: 100%;'>
                <table style='width: 100%;' id='informacion'>
                    <tr>
                        <td>PERIODO:</td>
                        <td>" . htmlspecialchars($request->desde) . " | " . htmlspecialchars($request->hasta) . "</td>
                    </tr>
                    <tr>
                        <td>CANTIDAD DE PRODUCTOS:</td>
                        <td>" . htmlspecialchars($data->total_productos ?? 0) . "</td>
                    </tr>
                </table>
            </div>
            

            <div style='width: 100%; margin-top:20px;'>
                <table style='width: 100%; text-align: center;' id='cuerpo'>
                    <tbody>
                    <tr>
                        <th rowspan='2'>Producto</th>
                        <th rowspan='2'>Dia</th>
                        <th colspan='4'>Comprobante de pago</th>
                        <th rowspan='2'>Operacion</th>
                        <th rowspan='2'>Destino</th>
                        <th rowspan='2'>Empresa</th>
                        
                        <!--<th rowspan='2'>Operacion</th>
                        <th rowspan='2'>Destino</th>
                        <th rowspan='2'>Empresa</th>-->
                        <th colspan='3'>ENTRADAS</th>
                        <th colspan='3'>SALIDAS</th>
                        <th colspan='3'>SALDO FINAL</th>
                    </tr>
                    <tr>
                        <!--<th>Dia</th>-->
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Serie</th>
                        <th>Numero</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                    </tr>
                    <!--</tbody>-->
                   <!--<tbody>-->
                   $rowHmtl
                   <tr>
                        <td colspan='9' class='no-footer-td' ></td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->entradas_cantidad ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->entradas_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->entradas_costo_total ?? 0) . "</td>
                        <td colspan='3' class='no-footer-td'></td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_cantidad ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_costo_total ?? 0) . "</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
        ";
            #die();
            if ($request->tipo == 'pdf') {
                $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
                $mpdf->Output();
            } else {
                $spreadsheet = new Spreadsheet();
                $reader = new Html();
                $spreadsheet = $reader->loadFromString($html);


                $sheet = $spreadsheet->getActiveSheet();
                foreach (range('B', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getColumnDimension('A')->setWidth(60);
                $writer = new Xlsx($spreadsheet);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="Compras.xlsx"');
                $writer->save('php://output');
            }
        }

        public function ventas_balance(Request $request) {
            $dias_semana = array(
                "Monday" => "lunes",
                "Tuesday" => "martes",
                "Wednesday" => "miércoles",
                "Thursday" => "jueves",
                "Friday" => "viernes",
                "Saturday" => "sábado",
                "Sunday" => "domingo"
            );

            $desde = $request->desde;
            $hasta = $request->hasta;

            $c_producto = new Producto();
            $c_producto->setIdEmpresa($_SESSION['id_empresa']);
            $result = $c_producto->ver_ventas_balance($request->almacenId, $request->desde, $request->hasta);
            #echo "<pre>";
            #var_dump($result);
            #echo "</pre>";

            $empresa = $this->conexion->query("select * from empresas
        where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();


            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'L']);

            $mpdf->WriteHTML("
        table, th, #informacion td {
          border: 1px solid #1f1f1f;
          color : #1f1f1f;
          border-collapse: collapse;
          font-size:12px;
          padding: 3px 5px;
        }
         #cuerpo th ,#cuerpo_info th {
            background: yellow
         }
         #cuerpo td {
          border: 1px solid #1f1f1f;
          border-collapse: collapse;
          font-size:11px;
          padding: 1px 3px;
        }
        #cuerpo .text-right,#cuerpo_info .text-right{
            text-align: right;
            padding-right: 8px;
        }
         #cuerpo .footer-td{
            background: yellow;
            font-size: 15px !important;
            font-weight: 500 !important;
             padding: 4px 3px;
              text-align: right;
        }
         #cuerpo .no-footer-td{
            border: 0px;
        }
        " .
                "
        ", \Mpdf\HTMLParserMode::HEADER_CSS);

            $rowHmtl = '';
            #$sql = "SELECT ingreso_egreso.*,productos.descripcion,IF(ingreso_egreso.tipo = 'e', 'Egreso', 'Ingreso') AS tipoIntercambio FROM ingreso_egreso JOIN productos ON ingreso_egreso.id_producto=productos.id_producto WHERE intercambio_id = '$id'";
            #$result = $this->conexion->query($sql)->fetch_assoc();

            /*$sql = "SELECT * FROM usuarios WHERE usuario_id = {$result['id_usuario']}";
            $resul2 = $this->conexion->query($sql)->fetch_assoc();*/
            /*  $sql */

            $data = new stdClass();

            #var_dump($result);
            #die();

            while ($row = $result->fetch_assoc()) {
                $data->capital = $data->capital ?? 0;
                $data->venta = $data->venta ?? 0;
                $data->salidas_cantidad = $data->salidas_cantidad ?? 0;
                $data->salidas_costo_unitario = $data->salidas_costo_unitario ?? 0;
                $data->salidas_costo_total = $data->salidas_costo_total ?? 0;


                $data->final_cantidad = $data->final_cantidad ?? 0;
                $data->final_costo_unitario = $data->final_costo_unitario ?? 0;
                $data->final_costo_total = $data->final_costo_total ?? 0;


                $data->total_productos = $data->total_productos ?? 0;
                $data->total_productos++;

                #echo "<pre>";
                #var_dump($row);
                #echo "</pre>";
                #<td style='width:auto'>{$row['intercambio_id']}</td>

                #die();
                $row['comprobante_dia'] = $dias_semana[$row['comprobante_dia']] ?? $row['comprobante_dia'];

                $row['salidas_cantidad'] = number_format(round($row['salidas_cantidad'], 2), 2, '.', '');
                $row['salidas_costo_unitario'] = number_format(round($row['salidas_costo_unitario'], 2), 2, '.', '');
                $row['salidas_costo_total'] = number_format(round($row['salidas_costo_total'], 2), 2, '.', '');


                $row['final_cantidad'] = number_format(round($row['final_cantidad'], 2), 2, '.', '');
                $row['final_costo_unitario'] = number_format(round($row['final_costo_unitario'], 2), 2, '.', '');
                $row['final_costo_total'] = number_format(round($row['final_costo_total'], 2), 2, '.', '');

                $data->capital += $row['salidas_costo_total'];
                $data->venta += $row['final_costo_total'];


                $data->salidas_cantidad += $row['salidas_cantidad'];
                $data->salidas_costo_unitario += $row['salidas_costo_unitario'];
                $data->salidas_costo_total += $row['salidas_costo_total'];

                $data->final_cantidad += $row['final_cantidad'];
                $data->final_costo_unitario += $row['final_costo_unitario'];
                $data->final_costo_total += $row['final_costo_total'];

                $rowHmtl .= "<tr>
                                <td>" . htmlspecialchars($row['producto']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_dia']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_fecha']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_tipo']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_serie']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_numero']) . "</td>
                                <td>" . htmlspecialchars($row['venta_operacion']) . "</td>
                                <td>" . htmlspecialchars($row['venta_destino']) . "</td>
                                <td>" . htmlspecialchars($row['venta_empresa']) . "</td>
                                
                                <td class='text-right'>" . htmlspecialchars($row['entradas_cantidad']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['entradas_costo_unitario']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['entradas_costo_total']) . "</td>
                                
                                <td class='text-right'>" . htmlspecialchars($row['salidas_cantidad']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['salidas_costo_unitario']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['salidas_costo_total']) . "</td>
                                
                                <td class='text-right'>" . htmlspecialchars($row['final_cantidad']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['final_costo_unitario']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['final_costo_total']) . "</td>
                            </tr>";

            }

            $data->capital = number_format(round($data->capital ?? 0, 2), 2, '.', '');
            $data->venta = number_format(round($data->venta ?? 0, 2), 2, '.', '');
            $data->total = $data->venta - $data->capital;

            $html = "
         
        <div style='width: 100%; '>
            <div style='width: 100%; text-align: center;'>
                    <h3 style=''>REPORTE DE VENTAS DE PRODUCTOS (Ventas)</h3>
            </div>
            <div style='width: 100%;'>
                <table style='width: 100%;' id='informacion'>
                    <tr>
                        <td>PERIODO:</td>
                        <td>" . htmlspecialchars($request->desde) . " | " . htmlspecialchars($request->hasta) . "</td>
                    </tr>
                    <tr>
                        <td>CANTIDAD DE PRODUCTOS:</td>
                        <td>" . htmlspecialchars($data->total_productos ?? 0) . "</td>
                    </tr>
                </table>
            </div>
              
            <div style='width: 100%; margin-top:20px;'>
                <table style='width: 100%; text-align: center;' id='cuerpo'> 
                 <tbody>
                    <tr>
                        <th rowspan='2'>Producto</th>
                        <th rowspan='2'>Dia</th>
                        <th colspan='4'>Comprobante de pago</th>
                        <th rowspan='2'>Operacion</th>
                        <th rowspan='2'>Destino</th>
                        <th rowspan='2'>Empresa</th>
                        
                        <!--<th rowspan='2'>Operacion</th>
                        <th rowspan='2'>Destino</th>
                        <th rowspan='2'>Empresa</th>-->
                        <th colspan='3'>ENTRADAS</th>
                        <th colspan='3'>SALIDAS</th>
                        <th colspan='3'>SALDO FINAL</th>
                    </tr>
                    <tr>
                        <!--<th>Dia</th>-->
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Serie</th>
                        <th>Numero</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                    </tr> 
                 
                   $rowHmtl
                    <tr>
                        <td colspan='9' class='no-footer-td' ></td>
                         <td colspan='3' class='no-footer-td'></td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->salidas_cantidad ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->salidas_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->salidas_costo_total ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_cantidad ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_costo_total ?? 0) . "</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
        ";
            #die();
            if ($request->tipo == 'pdf') {
                $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
                $mpdf->Output();
            } else {
                $spreadsheet = new Spreadsheet();
                $reader = new Html();
                $spreadsheet = $reader->loadFromString($html);


                $sheet = $spreadsheet->getActiveSheet();
                foreach (range('B', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getColumnDimension('A')->setWidth(60);
                $writer = new Xlsx($spreadsheet);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="ventas.xlsx"');
                $writer->save('php://output');
            }
        }

        public function balance(Request $request) {
            $dias_semana = array(
                "Monday" => "lunes",
                "Tuesday" => "martes",
                "Wednesday" => "miércoles",
                "Thursday" => "jueves",
                "Friday" => "viernes",
                "Saturday" => "sábado",
                "Sunday" => "domingo"
            );

            $desde = $request->desde;
            $hasta = $request->hasta;

            $c_producto = new Producto();
            $c_producto->setIdEmpresa($_SESSION['id_empresa']);
            $result = $c_producto->ver_balance($request->almacenId, $request->desde, $request->hasta);
            #echo "<pre>";
            #var_dump($result);
            #echo "</pre>";

            $empresa = $this->conexion->query("select * from empresas
        where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();


            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'L']);

            $mpdf->WriteHTML("
        table, th, #informacion td {
          border: 1px solid #1f1f1f;
          color : #1f1f1f;
          border-collapse: collapse;
          font-size:12px;
          padding: 3px 5px;
        }
         #cuerpo th ,#cuerpo_info th {
            background: yellow
         }
         #cuerpo td {
          border: 1px solid #1f1f1f;
          border-collapse: collapse;
          font-size:11px;
          padding: 1px 3px;
        }
        #cuerpo .text-right,#cuerpo_info .text-right{
            text-align: right;
            padding-right: 8px;
        }
        #cuerpo .footer-td{
            background: yellow;
            font-size: 15px !important;
            font-weight: 500 !important;
             padding: 4px 3px;
              text-align: right;
        }
         #cuerpo .no-footer-td{
            border: 0px;
        }
        " .
                "
        ", \Mpdf\HTMLParserMode::HEADER_CSS);

            $rowHmtl = '';
            #$sql = "SELECT ingreso_egreso.*,productos.descripcion,IF(ingreso_egreso.tipo = 'e', 'Egreso', 'Ingreso') AS tipoIntercambio FROM ingreso_egreso JOIN productos ON ingreso_egreso.id_producto=productos.id_producto WHERE intercambio_id = '$id'";
            #$result = $this->conexion->query($sql)->fetch_assoc();

            /*$sql = "SELECT * FROM usuarios WHERE usuario_id = {$result['id_usuario']}";
            $resul2 = $this->conexion->query($sql)->fetch_assoc();*/
            /*  $sql */

            $data = new stdClass();

            #var_dump($result);
            #die();

            while ($row = $result->fetch_assoc()) {
                $data->capital = $data->capital ?? 0;
                $data->salidas = $data->salidas ?? 0;
                $data->venta = $data->venta ?? 0;

                $data->entradas_cantidad = $data->entradas_cantidad ?? 0;
                $data->entradas_costo_unitario = $data->entradas_costo_unitario ?? 0;
                $data->entradas_costo_total = $data->entradas_costo_total ?? 0;

                $data->salidas_cantidad = $data->salidas_cantidad ?? 0;
                $data->salidas_costo_unitario = $data->salidas_costo_unitario ?? 0;
                $data->salidas_costo_total = $data->salidas_costo_total ?? 0;

                $data->final_cantidad = $data->final_cantidad ?? 0;
                $data->final_costo_unitario = $data->final_costo_unitario ?? 0;
                $data->final_costo_total = $data->final_costo_total ?? 0;


                $data->total_productos = $data->total_productos ?? 0;
                $data->total_productos++;

                #echo "<pre>";
                #var_dump($row);
                #echo "</pre>";
                #<td style='width:auto'>{$row['intercambio_id']}</td>

                #die();
                #echo "<pre>";
                #var_dump($row);
                #echo "</pre>";
                #die();
                $cantidad = 0;

                $row['comprobante_dia'] = $dias_semana[$row['comprobante_dia']] ?? $row['comprobante_dia'];


                $row['entradas_cantidad'] = number_format(round($row['entradas_cantidad'], 2), 2, '.', '');
                $row['entradas_costo_unitario'] = number_format(round($row['entradas_costo_unitario'], 2), 2, '.', '');
                $row['entradas_costo_total'] = number_format(round($row['entradas_costo_total'], 2), 2, '.', '');


                $row['salidas_cantidad'] = number_format(round($row['salidas_cantidad'], 2), 2, '.', '');
                $row['salidas_costo_unitario'] = number_format(round($row['salidas_costo_unitario'], 2), 2, '.', '');
                $row['salidas_costo_total'] = number_format(round($row['salidas_costo_total'], 2), 2, '.', '');


                $row['final_cantidad'] = number_format(round($row['final_cantidad'], 2), 2, '.', '');
                $row['final_costo_unitario'] = number_format(round($row['final_costo_unitario'], 2), 2, '.', '');
                $row['final_costo_total'] = number_format(round($row['final_costo_total'], 2), 2, '.', '');

                $data->capital += floatval($row['entradas_costo_total']);
                $data->venta += floatval($row['final_costo_total']);

                $data->entradas_cantidad += intval($row['entradas_cantidad']);
                $data->entradas_costo_unitario += floatval($row['entradas_costo_unitario']);
                $data->entradas_costo_total += floatval($row['entradas_costo_total']);

                $data->salidas_cantidad += intval($row['salidas_cantidad']);
                $data->salidas_costo_unitario += floatval($row['salidas_costo_unitario']);
                $data->salidas_costo_total += floatval($row['salidas_costo_total']);

                $data->final_cantidad += intval($row['final_cantidad']);
                $data->final_costo_unitario += floatval($row['final_costo_unitario']);
                $data->final_costo_total += floatval($row['final_costo_total']);


                $rowHmtl .= "<tr>
                                <td>" . htmlspecialchars($row['producto']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_dia']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_fecha']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_tipo']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_serie']) . "</td>
                                <td>" . htmlspecialchars($row['comprobante_numero']) . "</td>
                                <td>" . htmlspecialchars($row['venta_operacion']) . "</td>
                                <td>" . htmlspecialchars($row['venta_destino']) . "</td>
                                <td>" . htmlspecialchars($row['venta_empresa']) . "</td>
                                
                                <td class='text-right'>" . (intval($row['entradas_cantidad']) == 0 ? '' : htmlspecialchars($row['entradas_cantidad'])) . "</td>
                                <td class='text-right'>" . (intval($row['entradas_costo_unitario']) == 0 ? '' : htmlspecialchars($row['entradas_costo_unitario'])) . "</td>
                                <td class='text-right'>" . (intval($row['entradas_costo_total']) == 0 ? '' : htmlspecialchars($row['entradas_costo_total'])) . "</td>
                                
                                <td class='text-right'>" . (intval($row['salidas_cantidad']) == 0 ? '' : htmlspecialchars($row['salidas_cantidad'])) . "</td>
                                <td class='text-right'>" . (intval($row['salidas_costo_unitario']) == 0 ? '' : htmlspecialchars($row['salidas_costo_unitario'])) . "</td>
                                <td class='text-right'>" . (intval($row['salidas_costo_total']) == 0 ? '' : htmlspecialchars($row['salidas_costo_total'])) . "</td>
                                
                                <td class='text-right'>" . htmlspecialchars($row['final_cantidad']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['final_costo_unitario']) . "</td>
                                <td class='text-right'>" . htmlspecialchars($row['final_costo_total']) . "</td>
                            </tr>";

            }


            $html = "
         
        <div style='width: 100%; '>
            <div style='width: 100%; text-align: center;'>
                    <h3 style=''>REPORTE DE COMPRAS Y VENTAS</h3>
            </div>
            <div style='width: 100%;'>
                <table style='width: 100%;' id='informacion'>
                    <tr>
                        <td>PERIODO:</td>
                        <td>" . htmlspecialchars($request->desde) . " | " . htmlspecialchars($request->hasta) . "</td>
                    </tr>
                    <tr>
                        <td>CANTIDAD DE PRODUCTOS:</td>
                        <td>" . htmlspecialchars($data->total_productos ?? 0) . "</td>
                    </tr> 
                </table>
            </div>            

            <div style='width: 100%; margin-top:20px;'>
                <table style='width: 100%; text-align: center;' id='cuerpo'>
                   <tbody>
                    <tr>
                        <th rowspan='2'>Producto</th>
                        <th rowspan='2'>Dia</th>
                        <th colspan='4'>Comprobante de pago</th>
                        <th rowspan='2'>Operacion</th>
                        <th rowspan='2'>Destino</th>
                        <th rowspan='2'>Empresa</th> 
                        <th colspan='3'>ENTRADAS</th>
                        <th colspan='3'>SALIDAS</th>
                        <th colspan='3'>SALDO FINAL</th>
                    </tr>
                    <tr> 
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Serie</th>
                        <th>Numero</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                        <th>Cantidad</th>
                        <th>Costo Unitario</th>
                        <th>Costo Total</th>
                    </tr> 
                   
                   $rowHmtl
                    <tr>
                        <td colspan='9' class='no-footer-td' ></td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->entradas_cantidad ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->entradas_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->entradas_costo_total ?? 0) . "</td>
                        
                         <td class='footer-td' style=''>" . htmlspecialchars($data->salidas_cantidad ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->salidas_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->salidas_costo_total ?? 0) . "</td>
                        
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_cantidad ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_costo_unitario ?? 0) . "</td>
                        <td class='footer-td' style=''>" . htmlspecialchars($data->final_costo_total ?? 0) . "</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
        ";
            if ($request->tipo == 'pdf') {
                $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
                $mpdf->Output();
            } else {
                $spreadsheet = new Spreadsheet();
                $reader = new Html();
                $spreadsheet = $reader->loadFromString($html);


                $sheet = $spreadsheet->getActiveSheet();
                foreach (range('B', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getColumnDimension('A')->setWidth(60);
                $writer = new Xlsx($spreadsheet);

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="balance.xlsx"');
                $writer->save('php://output');
            }
        }

        public function ingresosEgresos($id) {
            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
            $empresa = $this->conexion->query("select * from empresas
        where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();

            $mpdf->WriteHTML("
        table, th, td {
          border: 1px solid black;
          border-collapse: collapse;
        }
        ", \Mpdf\HTMLParserMode::HEADER_CSS);

            $rowHmtl = '';
            $sql = "SELECT ingreso_egreso.*,productos.descripcion,IF(ingreso_egreso.tipo = 'e', 'Egreso', 'Ingreso') AS tipoIntercambio FROM ingreso_egreso JOIN productos ON ingreso_egreso.id_producto=productos.id_producto WHERE intercambio_id = '$id'";
            $result = $this->conexion->query($sql)->fetch_assoc();

            $sql = "SELECT * FROM usuarios WHERE usuario_id = {$result['id_usuario']}";
            $resul2 = $this->conexion->query($sql)->fetch_assoc();
            /*  $sql */
            $rowHmtl .= "<tr>
        <td style='width:auto'>{$result['intercambio_id']}</td>
        <td style='width:auto'>{$result['descripcion']}</td>
        <td style='width:auto'>{$result['tipoIntercambio']}</td>
        <td style='width:auto'>{$result['cantidad']}</td>
        <td style='width:auto'>Almacen {$result['almacen_ingreso']}</td>
        <td style='width:auto'>Almacen {$result['almacen_egreso']}</td>
        <td style='width:auto'>{$resul2['nombres']}</td>
    </tr>";

            $html = "
         
        <div style='width: 100%; '>
            <div style='width: 100%; text-align: center;'>
                    <h3 style=''>REPORTE DEL INTERCAMBIO DE PRODUCTOS N# $id</h3>
            </div>
            <div style='width: 100%;'>
                <table style='width: 100%;'>
                    <tr>
                        <td>EMPRESA:</td>
                        <td>{$empresa["ruc"]} | {$empresa['razon_social']}</td>
                    </tr>
                </table>
            </div>
            
            <div style='width: 100%; margin-top:40px;'>
                <table style='width: 100%; text-align: center;' >
                    <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Ingreso</th>
                        <th>Egreso</th>
                        <th>Hecho por</th>
                    </tr>
                    </thead>
                   <tbody>
                   $rowHmtl
                    </tbody>
                </table>
            </div>
            
        </div>
        ";
            /*  $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            $mpdf->Output(); */
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            $mpdf->Output();
        }

        public function generarExcelProducto() {

            $texto = $_GET['texto'];
            $sql = "select descripcion,MIN(codigo) AS codigo,
 MIN(costo) as costo,
       SUM(CASE WHEN almacen = 1 THEN cantidad ELSE 0 END) AS cantidad1, SUM(CASE WHEN almacen = 2 THEN cantidad ELSE 0 END) AS cantidad2 
        from productos where descripcion like '%$texto%' or codigo like '%$texto%' GROUP BY descripcion;";

            $result = $this->conexion->query($sql);

            foreach ($result as $fila) {

                $tbody .= '
            <tr>
                <td>' . $fila['codigo'] . '</td>            
                <td>' . $fila['descripcion'] . '</td>            
                <td>' . $fila['costo'] . '</td>            
                <td>' . $fila['cantidad1'] . '</td>            
                <td>' . $fila['cantidad2'] . '</td>         
                         
            </tr>';
            }

            $tabla = "
        <table>
            <tr>
                    <th style='background-color: #90BFEB;width:10px'>Codigo</th>
                    <th style='background-color: #90BFEB;width:85px'>Descripcion</th>
                    <th style='background-color: #90BFEB;width:7px'>Costo</th>
                    <th style='background-color: #90BFEB;width:7px'>CNT A1</th>
                    <th style='background-color: #90BFEB;width:8px'>CNT A2</th> 
            </tr>
            <tbody>
                " . $tbody . "
            </tbody>
        </table>";


            /*   return ($arrayRes);  */
            $nombre_exel = "reporteproductosstock.xlsx";
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($tabla);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");


            $writer->save($nombre_exel);
            header('Location: ' . URL::to($nombre_exel));
        }

        public function generarExcel($id) {

            $c_venta = new Venta();
            $c_varios = new Varios();
            $c_venta->setIdEmpresa($_SESSION['id_empresa']);
            ob_start();
            $a_ventas = $c_venta->verFilasPeriodo($id);
            ob_end_clean();

            $explodeFecha = explode('-', $id);
            $anio = $explodeFecha[0];
            $mes = $explodeFecha[1];
            $periodo = $anio . $mes;

            $empresa = $this->conexion->query("select * from empresas
                where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle("Reporte Ventas");

            $styleHeader = [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $styleBorder = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ];
            $styleTh = [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ];

            $row = 1;
            $sheet->mergeCells("A$row:I$row");
            $sheet->setCellValue("A$row", "REPORTE DE VENTAS DEL PERIODO: $periodo");
            $sheet->getStyle("A$row")->applyFromArray($styleHeader);

            $row = 2;
            $sheet->mergeCells("A$row:I$row");
            $sheet->setCellValue("A$row", "EMPRESA: {$empresa['ruc']} | {$empresa['razon_social']}");
            $sheet->getStyle("A$row")->getFont()->setBold(true);

            $row = 4;
            $headers = ['Codigo', 'Fecha', 'Documento', 'Cliente', 'Metodo 1', 'Metodo 2', 'SubTotal', 'IGV', 'Total'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($styleTh);
                $col++;
            }

            $item = 1;
            foreach ($a_ventas as $fila) {
                if ($fila['id_tido'] == 1) continue;
                $row++;
                $total = 0;
                $cliente = "**** DOCUMENTO ANULADO ****";
                if ($fila['estado'] == 1) {
                    $total = $fila['total'];
                    $cliente = $fila['documento'] . " | " . $fila['datos'];
                }
                $codigo = $periodo . $c_varios->zerofill($item, 3);
                $documento_venta = $fila['abreviatura'] . " | " . $fila['serie'] . " - " . $c_varios->zerofill($fila['numero'], 3);
                $metodo2 = '';
                if (!is_null($fila['pagado2']) && strlen($fila['pagado2']) > 0) {
                    $metodo = $fila['metodo'] . ": S/ " . ($fila['pagado']);
                    $metodo2 = $fila['metodo2'] . ': S/' . $fila['pagado2'];
                } else {
                    $metodo = $fila['metodo'] . ": S/ " . ($fila['pagado'] ? $fila['pagado'] : $fila['total']);
                }

                $subtotal = number_format($total / 1.18, 2);
                $igv = number_format($total / 1.18 * 0.18, 2);
                $total_f = number_format($total, 2);
                $item++;

                $data = [$codigo, $fila['fecha_emision'], $documento_venta, $cliente, $metodo, $metodo2, $subtotal, $igv, $total_f];
                $col = 'A';
                foreach ($data as $val) {
                    $sheet->setCellValue($col . $row, $val);
                    $sheet->getStyle($col . $row)->applyFromArray($styleBorder);
                    $col++;
                }
            }

            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(22);
            $sheet->getColumnDimension('D')->setWidth(45);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(18);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->getColumnDimension('I')->setWidth(12);

            $nombre_exel = "Venta de " . $anio . "-" . $mes . ".xlsx";
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer->save($nombre_exel);
            header('Location: ' . URL::to($nombre_exel));
        }

        public function generarExcelRVTA($fecha) {


            $explodeFecha = explode('-', $fecha);
            $anio = $explodeFecha[0];
            $mes = $explodeFecha[1];
            $sql = 'SELECT v.id_tido,v.id_venta,v.fecha_emision,v.fecha_vencimiento,ds.nombre AS tipoDocPago,v.serie,v.numero AS numeroVenta,v.enviado_sunat,v.igv,
       IF(v.enviado_sunat = 0,"No enviado","Enviado") AS enviadoSunat,
        (CASE 
        WHEN LENGTH(c.documento) = 8 THEN "DNI" 
         WHEN LENGTH(c.documento) = 11 THEN "RUC"
        END ) AS tipoDocumento,
        c.documento,c.datos AS cliente,v.total
         FROM ventas v 
        LEFT JOIN documentos_sunat ds ON v.id_tido=ds.id_tido
        LEFT JOIN clientes c ON c.id_cliente=v.id_cliente 
        WHERE  YEAR(v.fecha_emision) =' . $anio . ' AND MONTH(v.fecha_emision) = ' . $mes . ' AND v.id_empresa=' . $_SESSION['id_empresa'];

            /*   var_dump($sql);
            die();
     */
            $result = $this->conexion->query($sql);
            $tabla = '';
            $tbody = '';
            $totalOpgravado = 0;
            /*   $total = 0; */
            foreach ($result as $fila) {
                if ($fila['id_tido'] != '1' && $fila['id_tido'] != '2') {
                    continue;
                }
                $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
                $igv = $fila['total'] / ($fila['igv'] + 1) * $fila['igv'];
                $totalOpgravado = $fila['total'] - $igv;
                $total = number_format((float)$fila['total'], 2, '.', '');
                $igv = number_format($igv, 2, '.', ',');
                $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
                $style = '';
                if ($fila['enviado_sunat'] == '0') {
                    $style = "red";
                } else {
                    $style = "green";
                }
                $tbody .= '
               <tr>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['id_venta'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['fecha_emision'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['fecha_vencimiento'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['tipoDocPago'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['serie'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['numeroVenta'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['tipoDocumento'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['documento'] . '</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">' . $fila['cliente'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">0</td>
               <td style="font-size: 10px;border:1px solid black;">' . $totalOpgravado . '</td>
               <td style="font-size: 10px;border:1px solid black;">0</td>
               <td style="font-size: 10px;border:1px solid black;">0</td>
               <td style="font-size: 10px;border:1px solid black;"colspan="2"></td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">' . ($fila['igv'] * 100) . ' %</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">0</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">' . $total . '</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;width:10px;background-color:' . $style . '">' . $fila['enviadoSunat'] . '</td>
               </tr>
                ';
            }
            $tabla = '  <table style="width:100%">
        <tr>
        </tr>
        <tr>
            <th rowspan="3" colspan="1" style="font-weight:bold;border:1px solid black;text-align: center;font-size: 10px;width:20px;word-wrap: break-word">NUMERO DEL REGISTRO O CODIGO UNICO DE OPERACIÓN</th>
            <th rowspan="2" colspan="5" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">COMPROBANTE DE PAGO O DOCUMENTO</th>
            <th colspan="4" rowspan="1" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">INFORMACION DEL CLIENTE</th>
            <th rowspan="3" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">VALOR FACTURADO DE LA EXPORTACION</th>
            <th rowspan="3" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">BASE IMPONIBLE DE LA OPERACIÓN GRAVADA</th>
            <th rowspan="2" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">IMPORTE DE LA OPERACIÓN EXONERADA O INAFECTA</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">ISC</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">IGV Y/O IPM</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">OTROS TRIBUTOS Y CARGOS QUE NO FORMAN PARTE DE LA BASE IMPONIBLE</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">IMPORTE TOTAL DEL COMPROBANTE DE PAGO</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">TIPO DE CAMBIO</th>
            <th rowspan="2" colspan="4" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">REF. COMPROBANTE DE PAGO QUE SE MODIFICA</th>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 10px;font-weight:bold;border:1px solid black;width:20px;text-align: center;word-wrap: break-word">DOC. IDENTIDAD</td>
            <td rowspan="2" colspan="2" style="font-size: 10px;font-weight:bold;border:1px solid black;width:30px;text-align: center;word-wrap: break-word">APELLIDOS Y NOMBRES O RAZON SOCIAL</td>
          
        </tr>
        <tr>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">FECHA DE EMISION</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:13px;text-align: center;word-wrap: break-word">FECHA DE VCTO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">TIPO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">SERIE</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">NUMERO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;width:8px;">TIPO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;width:13px;">NUMERO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;">EXONERADA</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;">INAFECTA</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:15px;text-align: center;">FECHA EMISION</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">TIPO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">SERIE</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">NUMERO</td>
        </tr>
       ' . $tbody . '
    </table>';

            $nombre_exel = "RVTA " . $anio . "-" . $mes . ".xlsx";
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($tabla);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
            $writer->save($nombre_exel);
            header('Location: ' . URL::to($nombre_exel));
        }
    }
