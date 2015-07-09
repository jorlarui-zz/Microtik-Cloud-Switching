<?php ob_start(); session_start(); require('routeros_api.class.php'); ?>


<?php
	//Conectamos al CAPsMAN
	$IP = $_SESSION[ 'ip' ];
	$user = $_SESSION[ 'user' ];
	$password = $_SESSION[ 'password' ];
	$interfaz = $_SESSION ['interfaz'];

	$API = new routeros_api();
	if ($API->connect($IP , $user , $password)) {

		//Creamos dos array para almacenar los datos de trafico
		$rows = array(); $rows2 = array();	

		//Enviamos el comando de monitor para capturar el tráfico una vez
		 $API->write("/interface/monitor-traffic",false);
		 $API->write("=interface=".$interfaz,false);  
		 $API->write("=once=",true);
		 $READ = $API->read(false);
		 $ARRAY = $API->parse_response($READ);

			//Si hay datos
			if(count($ARRAY)>0){  
				//En rx almacenamos el trafico leido y en tx el de transmision
				$rx = number_format($ARRAY[0]["rx-bits-per-second"]/1024,1);
				$tx = number_format($ARRAY[0]["tx-bits-per-second"]/1024,1);

				//En el array establecemos un nombre y unos datos
				$rows['name'] = 'Tx';
				$rows['data'][] = $tx;
				$rows2['name'] = 'Rx';
				$rows2['data'][] = $rx;
			}else{  
				echo $ARRAY['!trap'][0]['message'];	 
			} 
	}
	$API->disconnect();

	$result = array();
	array_push($result,$rows);
	array_push($result,$rows2);
	//Pasamos por JSON los array
	print json_encode($result, JSON_NUMERIC_CHECK);
	
?>
