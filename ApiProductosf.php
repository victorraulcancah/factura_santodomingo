<?php
function AgregarProductos($pronmbre, $promarca,$proprecio,$prostock,$procosto,$opc) {

$url = 'https://cgs-computer.pe/public_html/testapi/RestProducto.php';
$curl = curl_init();
$fields = array(
    'pronmbre' =>  $pronmbre,
    'promarca' =>  $promarca,
    'proprecio' => $proprecio,
    'prostock' =>  $prostock,
    'procosto' =>  $procosto,
    'opc' =>  $opc,

);
$fields_string = http_build_query($fields);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
    $data = curl_exec($curl);
    curl_close($curl);

    $decodificado = json_decode($data,true);
    echo $rest = $decodificado[0]['resp'];
   
}
$opc = 'agregar-producto';
$procosto = 13;
$prostock = 50;
$proprecio = 15;
AgregarProductos('filtro3','firestick',$proprecio,$prostock,$procosto,$opc);
?>
