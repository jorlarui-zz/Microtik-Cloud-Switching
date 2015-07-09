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
				for ($cont = 0; $cont < $numPorts; $cont++){
				
				if($statusPorts[$cont]['status']=='link-ok' && $Ports[$cont]['master-port']!='none'){
					echo "<svg version='1.1' id='etherGreen$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
	 				width='5.2%' height='5.2%' viewBox='0 0 15 11' style='enable-background:new 0 0 15 11;' xml:space='preserve'>
					<polygon class='st0' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
					</svg>";
					}
				else if($statusPorts[$cont]['status']=='link-ok' && $Ports[$cont]['master-port']=='none'){	
					echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='enable-background:new 0 0 15 11;' xml:space='preserve'>
<style type='text/css'>
<![CDATA[
	.st0{font-size:9px;}
	.st2{font-family:'Open Sans';}
	.st3{fill:#127018;}
]]>
</style>

	<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
<text transform='matrix(1.0151 0 0 1 3.375 10.2891)' class='st3 st2 st0'>m</text>
</svg>";
							
					}
				
				}
				echo "<img src='images/$modelo.png'>";			
?>
