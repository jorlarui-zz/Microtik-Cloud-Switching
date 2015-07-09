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

		//Switch
		$switches = $API->comm("/interface/ethernet/switch/print");
		$numSwitches = count($switches);

		//Ports Switch
		$portsSwitch = $API->comm("/interface/ethernet/switch/port/print");
		$numPortsSwitch = count($portsSwitch);

		//puerto Trunk CR
		$estadoTrunkCR = $API->comm("/interface/ethernet/switch/egress-vlan-tag/print");

		//puerto Acceso CR
		$estadoAccessCR = $API->comm("/interface/ethernet/switch/ingress-vlan-translation/print");

		//VLANS
		$vlans = $API->comm("/interface/ethernet/switch/vlan/print");

		//IP ADDRESS
		$ipAddress = $API->comm("/ip/address/print");

		//CPU
		$cpuInfo = $API->comm("/system/resource/print");
		//RB o CR
		$routeroSwitch = $cpuInfo[0]['board-name'];
		//Saber iniciales Router o Switch
		$identidadRS = substr($routeroSwitch,0,2);
	
		//GET NUMBER OF ACCESS PORTS RB
			$portsAccessRB= "";
			for ($cont2 = 0; $cont2 < count($portsSwitch); $cont2++){
			$allPortsAccessRB = $portsSwitch[$cont2]['default-vlan-id'].",".$allPortsAccessRB;
			}
						
			//DELETE LAST COMMA
			if ($allPortsAccessRB[strlen($allPorts)-1] == ","){
			$allPortsAccessRB = rtrim($allPorts,',');
			}
			//LEAVE UNIQUE VALUES WITHOUT REPEATING
			$allPortsAccessRB = implode(',',array_unique(explode(',', $allPortsAccessRB)));
			//DO ARRAY WITH EACH VALUE
			$allPortsAccessRB = explode(',', $allPortsAccessRB);

		
		//GET NUMBER OF ACCESS PORTS CR
			$portsAccessCR= "";
			for ($cont3 = 0; $cont3 < count($estadoAccessCR); $cont3++){
			$allPortsAccessCR = $estadoAccessCR[$cont3]['new-customer-vid'].",".$allPortsAccessCR;
			}
						
			//DELETE LAST COMMA
			if ($allPortsAccessCR[strlen($allPorts)-1] == ","){
			$allPortsAccessCR = rtrim($allPorts,',');
			}
			//LEAVE UNIQUE VALUES WITHOUT REPEATING
			$allPortsAccessCR= implode(',',array_unique(explode(',', $allPortsAccessCR)));
			//DO ARRAY WITH EACH VALUE
			$allPortsAccessCR = explode(',', $allPortsAccessCR);
		
		


		$API->write("/interface/ethernet/monitor",false);
		$API->write("=numbers=".$valores,false);  
		$API->write("=once=",true);
		$READ = $API->read(false);
		$statusPorts = $API->parse_response($READ);

		//Array Colores,
		$colores=["#00fff9","#ff00e7","#a3ff00","#ffdc0b","#ff4400","#7c00ff","#3377ff","#ff7468","#20e523","#fcc512"
			 ,"#9f0d0d","#bc9ad4","#79addd","#e7d1e5","#7bcf5a","#cc8324","#b80f12","#0da9b0","#eea7b9","#1e7352"
			,"#eee117","#b80000","#00137a","#AA0078","#3333FF","#99FF00","#FFCC00","#CC0000","#587498","#E86850"
			,"#FFD800","##00FF00","#FF0000","#0000FF","#FF6600","#A16B23","#C9341C","#ECC5A8","#A3CBF1","#79BFA1"
			,"#FB7374","#FF9900","#4FD5D6","#D6E3B5","#FFD197","#FFFF66","#FFC3CE","#21B6A8","#CDFFFF",""];


		$API->disconnect();}	
				
			
				//Sacar valores String TRUNK CR
			for ($cont = 0; $cont < count($estadoTrunkCR); $cont++){
			//Concatenamos todos los puertos de trunk
				$puertosTrunk = ($estadoTrunkCR[$cont]['tagged-ports'].(",").$puertosTrunk);}
			//Eliminamos la ultima coma
				$rest = substr($puertosTrunk, 0, -1);
			//Separamos cada puerto en una string delimitando la coma
				$tags = explode(',', $rest);
			//Elegimos el primer puerto y eliminamos a los repetidos
				$resultTrunk = array_unique($tags);


				for ($cont = 0; $cont < $numPorts; $cont++){
				
				if($statusPorts[$cont]['status']=='link-ok'){
					if(strcmp($identidadRS,"RB") == 0 ){
					//COLOR ACCESS IMAGE
						if($portsSwitch[$cont+1]['vlan-mode']!='disabled' and $portsSwitch[$cont+1]['vlan-header']=='always-strip'){
								for($cont2 = 0; $cont2 < count($allPortsAccessRB); $cont2 ++){
									if($portsSwitch[$cont+1]['default-vlan-id'] === $allPortsAccessRB[$cont2]){
									
								echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='fill:$colores[$cont2]; enable-background:new 0 0 15 11;' xml:space='preserve'>
								<style type='text/css'>
								<![CDATA[
								.st0{font-size:9px;}
								.st2{font-family:'Open Sans';}
								.st3{fill:#000;}
								]]>
								</style>

								<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
								<text transform='matrix(1.0151 0 0 1 4.375 10.2891)' class='st3 st2 st0'>A</text>
								</svg>";
									}
								}
							}
				//COLOR TRUNK IMAGE
							else if($portsSwitch[$cont+1]['vlan-mode']!='disabled' and $portsSwitch[$cont+1]['vlan-header']=='add-if-missing'){
								echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='fill:#00fff9; enable-background:new 0 0 15 11;' xml:space='preserve'>
								<style type='text/css'>
								<![CDATA[
								.st0{font-size:9px;}
								.st2{font-family:'Open Sans';}
								.st3{fill:#000;}
								]]>
								</style>

								<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
								<text transform='matrix(1.0151 0 0 1 3.375 10.2891)' class='st3 st2 st0'>T</text>
								</svg>";
							}
				//COLOR NO SWITCHPORT
							else{
								echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='fill:#e7d1e5; enable-background:new 0 0 15 11;' xml:space='preserve'>
								<style type='text/css'>
								<![CDATA[
								.st0{font-size:9px;}
								.st2{font-family:'Open Sans';}
								.st3{fill:#000;}
								]]>
								</style>

								<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
								<text transform='matrix(1.0151 0 0 1 2.375 10.2891)' class='st3 st2 st0'>NS</text>
								</svg>";
							}
					
					}


					else if(strcmp($identidadRS,"CR") == 0 ){



//DIBUJAMOS NS EN TODOS LOS SWITCHPORTS						
						
								echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='fill:#e7d1e5; enable-background:new 0 0 15 11;' xml:space='preserve'>
								<style type='text/css'>
								<![CDATA[
								.st0{font-size:9px;}
								.st2{font-family:'Open Sans';}
								.st3{fill:#000;}
								]]>
								</style>

								<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
								<text transform='matrix(1.0151 0 0 1 2.375 10.2891)' class='st3 st2 st0'>NS</text>
								</svg>";
							

//DIBUJAR TRUNK CR		
						for($cont1 = 0; $cont1 < count($resultTrunk); $cont1++){
							if($resultTrunk[$cont1]==$Ports[$cont]['name']){
								
								echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='fill:#00fff9; enable-background:new 0 0 15 11;' xml:space='preserve'>
								<style type='text/css'>
								<![CDATA[
								.st0{font-size:9px;}
								.st2{font-family:'Open Sans';}
								.st3{fill:#000;}
								]]>
								</style>

								<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
								<text transform='matrix(1.0151 0 0 1 3.375 10.2891)' class='st3 st2 st0'>T</text>
								</svg>";
							}
						}

//DIBUJAR ACCESS CR
						for($cont2 = 0; $cont2 < count($estadoAccessCR); $cont2++){
							if($estadoAccessCR[$cont2]['ports']==$Ports[$cont]['name']){
								for($cont3 = 0; $cont3 < count($allPortsAccessCR); $cont3 ++){
									if($estadoAccessCR[$cont2]['new-customer-vid'] === $allPortsAccessCR[$cont3]){
								
								echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='fill:$colores[$cont3]; enable-background:new 0 0 15 11;' xml:space='preserve'>
								<style type='text/css'>
								<![CDATA[
								.st0{font-size:9px;}
								.st2{font-family:'Open Sans';}
								.st3{fill:#000;}
								]]>
								</style>

								<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
								<text transform='matrix(1.0151 0 0 1 4.375 10.2891)' class='st3 st2 st0'>A</text>
								</svg>";
									}
								}
							}
						}
						
					}
				}
			}
				echo "<img src='images/$modelo.png'>";			
				?>
