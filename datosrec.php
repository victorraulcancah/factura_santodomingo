<?php

 $dato1 = $_POST['field_name_1'];
	if ($dato1 !='') {
    		$dato1= 'hola';
	 }
    

 $dato2 = $_POST['field_name_2'];
	if ($dato2 !='') {
    		$dato2= 'mundo';
	 }


$listar = array("dato1" =>$dato1, "dato2"=>$dato2);
$data[] = $listar;
print json_encode($data);
?>