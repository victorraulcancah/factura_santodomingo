<?php
function AgregarProductos($pronmbre, $promarca,$proprecio,$prostock,$procosto) {

$url = 'https://cgs-computer.pe/public_html/RestProducto.php';
$curl = curl_init();
$fields = array(
    'pronmbre' =>  $pronmbre,
    'promarca' =>  $promarca,
    'proprecio' => $proprecio,
    'prostock' =>  $prostock,
    'procosto' =>  $procosto
);
$fields_string = http_build_query($fields);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
    $data = curl_exec($curl);
    curl_close($curl);
$data1 = json_decode($data, true);

//print_r ($fields);

}
?>
