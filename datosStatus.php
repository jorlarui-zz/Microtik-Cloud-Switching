<?php ob_start(); session_start(); require('routeros_api.class.php'); ?>

<?php
		$API = new routeros_api();
		$IP = $_SESSION[ 'ip' ];
		$user = $_SESSION[ 'user' ];
		$password = $_SESSION[ 'password' ];
		//Comprobamos conexion API
		if ($API->connect($IP, $user, $password)) {

		//Comprobamos interfaces
		
		$Ports = $API->comm("/interface/ethernet/print");
		$numPorts = count($Ports);

		//Modelo
		$modeloCom = $API->comm("/system/routerboard/print");
		$modelo=$modeloCom[0]['model'];
		//Estado Link

		$valoresPar= json_encode(range(0, $numPorts-1));
		$valores = substr($valoresPar, 1, -1);


		$API->write("/interface/ethernet/monitor",false);
		$API->write("=numbers=".$valores,false);  
		$API->write("=once=",true);
		$READ = $API->read(false);
		$statusPorts = $API->parse_response($READ);
		$API->disconnect();}	
				echo "<table>";
					for ($cont = 0; $cont < $numPorts; $cont++){
						echo "<tr>";
						
						echo "<td>".$Ports[$cont]['name']."</td>";
						if($statusPorts[$cont]['status']=='link-ok'){
							echo "<td class='link-ok'>";			
						}
						else if($statusPorts[$cont]['status']=='no-link'){
							echo "<td class='no-link'>";				
						}
				
				
						echo $statusPorts[$cont]['status']."</td>";
					}			
				echo "</table>";
		
?>
