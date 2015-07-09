<?php ob_start(); session_start(); require('routeros_api.class.php'); ?>
<?php error_reporting (E_ALL ^ E_NOTICE); ?>

<script src="jquery/jquery.min.js"></script>


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


		$API->disconnect();
		}
		else {
			header( 'Location:index.php?notLogin=true' );}

?>

<html>
<head>
	<title>Mikrotik Web Controller</title>
	<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" href="bootstrap/css/bootstrap.css"/>
	<link rel="stylesheet" href="css/style.css"/>
	<?php
		echo "<link rel='stylesheet' href='css/style$modelo.css'/>";
	?>
		





</head>
<body>


	
    <nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
            <p class="navbar-text"><img src="images/logolittle.png"></p>
        </div>
       
        <div id="navbar" class="collapse navbar-collapse">

	    <ul class="nav navbar-nav navbar-right">
                <li><a id="logOut" href="Status.php?logOut=yes">Log Out&nbsp&nbsp&nbsp</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-center">
                <li><a href="Status.php">Status</a></li>
              <li><a href="Switch.php">Switch</a></li>
                <li class="active"><a href="Vlans.php">VLANs</a></li>
		<li><a href="Routing.php">Routing</a></li>
		<li><a href="ACLs.php">ACLs</a></li>
            </ul>
        </div> 
    	</div>
    </nav>



<div class="container" style="margin-top:50px;">

      	<div class="row">
		<div class="col-lg-12 switch-box">
			<div class="col-lg-2"></div>
			<div class="col-lg-6">
				 <div id="refreshImage">
    
				<?php

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
 				 </div> 
				</div>
			<div class="col-lg-3" id="info">
			<?php
			echo "<div id='model'>
				<p class='infoBold'>Model: </p>";
				echo "<p>".$modelo."</p>
			</div></br>";
				echo "<div id='cpu'>
					<div id='cpu2'><p class='infoBold'>CPU: </p></div>
					<div class='progress' style='margin-bottom: 0px;'>
  					  <div class='progress-bar' role='progressbar' aria-valuenow='$cpuInfo[0]['cpu-load']' aria-valuemin='0' aria-valuemax='100' style='min-width: 2em; width:".$cpuInfo[0]['cpu-load']."%'>".
    						$cpuInfo[0]['cpu-load']."%
 					 </div>
				</div>
				</div>";

				echo "<div id='uptime'><p class='infoBold'>Uptime: </p><p>".$cpuInfo[0]['uptime']."</p></div>";
			?>
			</div>
			<div class="col-lg-1"></div>		
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12 info-box">
			<div class="col-lg-2"></div>
			<div class="col-lg-4">
			<div id="refreshVlans">
<!--TABLA RB-->

<?php
				
				if(strcmp($identidadRS,"RB") == 0 ){
					echo "<h3>Access</h3>
						<table class='tablePortsCR'>
					";
					echo '<tr><th>Ports</th>';
					echo '<th>VLAN</td></th>';
					for ($cont = 0; $cont < count($portsSwitch); $cont++){
						if($portsSwitch[$cont]['vlan-mode']!='disabled' and $portsSwitch[$cont]['vlan-header']=='always-strip'){
						echo '<tr><td>'.$portsSwitch[$cont]['name'].'</td>';
						echo '<td>'.$portsSwitch[$cont]['default-vlan-id'].'</td>';
						echo "<td><form name='button$cont' method='post'>
							<input type='submit' name='disableAccessRB$cont' value='X' class='buttonDisable'/>
							</form></td>";
			

						echo '</tr>';}
					}
				
					echo "</table>";
							
					echo "<h3>Trunk</h3>
						<table class='tablePortsCR'>
					";
					echo '<tr><th>Ports</th>';
					echo '<th>Allowed VLANs</th></tr>';
					for ($cont = 0; $cont < count($portsSwitch); $cont++){
						if($portsSwitch[$cont]['vlan-mode']!='disabled' and $portsSwitch[$cont]['vlan-header']=='add-if-missing'){
						echo '<tr><td>'.$portsSwitch[$cont]['name'].'</td>';
						echo '<td>';
							for($cont2 = 0; $cont2 < count($vlans); $cont2++){
							$pos = strpos($vlans[$cont2]['ports'], $portsSwitch[$cont]['name']);
							
							if($pos !== false){
								if($cont2 < count($vlans) -1){
									echo $vlans[$cont2]['vlan-id'].",";
								}
								else{
									echo $vlans[$cont2]['vlan-id'];
								}
							}
						}	

						echo '</td>';
						echo "<td><form name='button$cont' method='post'>
							<input type='submit' name='disableTrunkRB$cont' value='X' class='buttonDisable'/>
							</form></td>";
			

						echo '</tr>';}
					}
					echo "</table>";



				}
			?>
<!--TABLA CR-->
			<?php
				
				if(strcmp($identidadRS,"CR") == 0 ){
					echo "<h3>Access</h3>
						<table class='tablePortsCR'>
					";
					echo '<tr><th>Ports</th>';
					echo '<th>VLAN</th></tr>';
					for ($cont = 0; $cont < count($estadoAccessCR); $cont++){
						echo '<tr><td>'.$estadoAccessCR[$cont]['ports'].'</td>';
						echo '<td>'.$estadoAccessCR[$cont]['new-customer-vid'].'</td>';
						echo "<td><form name='button$cont' method='post'>
							<input type='submit' name='disableAccessCR$cont' value='X' class='buttonDisable'/>
							</form></td>";
			

						echo '</tr>';
					}
				
					echo "</table>";
							
					echo "<h3>Trunk</h3>
						<table class='tablePortsCR'>
					";
					echo '<tr><th>Ports</th>';
					echo '<th>Allowed VLANs</th></tr>';
			
					$allPorts= "";
					for ($cont = 0; $cont < count($estadoTrunkCR); $cont++){
						$allPorts = $estadoTrunkCR[$cont]['tagged-ports'].",".$allPorts;
					}
					
					//DELETE LAST COMMA
					if ($allPorts[strlen($allPorts)-1] == ","){
						$allPorts = rtrim($allPorts,',');
						}
					//LEAVE UNIQUE VALUES WITHOUT REPEATING
					$allPorts = implode(',',array_unique(explode(',', $allPorts)));
					//DO ARRAY WITH EACH VALUE
					$arrayPorts = explode(',', $allPorts);


					for ($cont = 0; $cont < count($arrayPorts); $cont++){
						for($cont2=count($estadoTrunkCR); $cont2 >= 0 ; $cont2--){
							
								//Check if PORT exist
								$pos= strpos($estadoTrunkCR[$cont2]['tagged-ports'], $arrayPorts[$cont]);
								if($pos !== false){
									$portsTable =$estadoTrunkCR[$cont2]['vlan-id'].",".$portsTable;
								}
							
						}
							if ($portsTable[strlen($portsTable)-1] == ","){
								$portsTable= rtrim($portsTable,',');
							}
						echo '<tr><td>'.$arrayPorts[$cont].'</td>';
						echo '<td>'.$portsTable.'</td>';
						$portsTable="";

						echo "<td><form name='button$cont' method='post'>
							<input type='submit' name='disableTrunkCR$cont' value='X' class='buttonDisable'/>
							</form></td>";
			

						echo '</tr>';

					}


					echo "</table>";


				}
			?>
			</div>
			</div>
			<div class="col-lg-4  portsBox">
				<form method='post' action='#' name='formPorts'>
				Select interfaces:
				<div class='styled-select'>
				<select name='interfaces'>
				<option value>Interfaz</option>
			<?php



			
				
			if(strcmp($identidadRS,"RB") == 0 ){
				for ($cont = 0; $cont < count($Ports); $cont++){
			
					$interfazSel = $Ports[$cont]['name'];
					echo "<option value=$interfazSel>$interfazSel</option>";
			
				}		
			}
			else if(strcmp($identidadRS,"CR") == 0 ){
				for ($cont = 0; $cont < $numPortsSwitch; $cont++){
			
					$interfazSel = $portsSwitch[$cont]['name'];
					echo "<option value=$interfazSel>$interfazSel</option>";
			
				}		
			}
	
			

		
			
			echo "</select></div>";
			?>

			</br>
			Access <input type='radio' name='form' value='Access'/>
			Trunk <input type='radio' name='form' value='Trunk'/>
			No Switchport<input type='radio' name='form' value='NoSwitchport'/>

			<div style='display: none' id='areaAccess'>
					Access Vlan: <input name='accessVlanID' type='number' min='0' max='4095' placeholder='100'/></br>
					
				
					</br>
				
			</div>	

			<div style='display: none' id='areaTrunk'>
					<?php
						echo "Allowed VLANS: <input name='allowedVlans' type='text' placeholder='80, 90, 100'/></br>";
				
						?>
					</br>
					
				
			</div>	
			<div style='display: none' id='areaNoSwitchport'>
					Ip Address: <input name='noSwitchportIP' type='text' placeholder='192.168.100.10/24'/>
					</br>
					
			</div>
			<input type='submit' name='submitButton' value='Submit' style='display: none'/>
			</form>
			
			<div class="col-lg-2"></div>
			
	</div>

<?php
$interfaz = $_POST['interfaces'];
$modoPuerto = $_POST['form'];
$accessVlanID = $_POST['accessVlanID'];
$noSwitchportIP = $_POST['noSwitchportIP'];
//Separar allowedVlans en un array
$allowedVlans = $_POST['allowedVlans'];
$allowedVlans = explode(',', $allowedVlans);


$cpuFallback = $_POST['cpuFallback'];
$contadorVlans = 0;
$contadorAccess = 0;

/////////// CREATION ///////////

//////// NO SWITCHPORT ////////
///// RB /////

	if(isset($_POST['submitButton'])){
	
		if($modoPuerto == "NoSwitchport"){
			if(strcmp($identidadRS,"RB") == 0 ){
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {

				for($cont = count($vlans) -1 ;$cont >= 0; $cont--){
					//Check if PORT exist in another VLAN, if exist DELETE PORT and create in NEW
					$pos= strpos($vlans[$cont]['ports'], $interfaz);
					
					
					if($pos !== false){
					
						//Get Actual Port to delete
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $vlans[$cont]['ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
						$API->comm("/interface/ethernet/switch/vlan/set", array(
		 					".id"     => $cont,
							"ports" => $finalPort,
							));
						}
				
				
						$switchPerPort;
						for($cont3=0; $cont3<$numPortsSwitch; $cont3++){
							if($portsSwitch[$cont3]['name'] == $actualPort) {
								$switchPerPort = $portsSwitch[$cont3]['switch'];
							}
						}

						//DELETE VLAN if last PORT
						if(($previousPort == $switchPerPort."-cpu,".$actualPort) or $previousPort == $actualPort.",".$switchPerPort."-cpu" or $previousPort == $actualPort){
							

							$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 					".id"     => $cont
							));


							$API->comm("/interface/ethernet/switch/port/set", array(
         				 		".id"     => $switchPerPort,
          						"vlan-mode" => "disabled",
								));
					
							
						}
						
							
					}

					

					//Check if VLAN exist, if exist $contadorVlans is increased
					if($vlans[$cont]['vlan-id'] == $accessVlanID){
						$contadorVlans++;
					}

				}
				
				//If VLAN dont exist, a new VLAN is created
					//GET Switch per Port to create VLAN
					$switchPerPort;
					for($cont2=0; $cont2<$numPortsSwitch; $cont2++){
						if($portsSwitch[$cont2]['name'] == $interfaz) {
							$switchPerPort = $portsSwitch[$cont2]['switch'];
							
						}
					}
					if($contadorVlans==0){
						$API->comm("/interface/ethernet/switch/vlan/add", array(
         				 	"ports"     => $interfaz,
          					"switch" => $switchPerPort,
						"vlan-id" => $accessVlanID
					));
					}
				
				//If VLAN exist, edit VLAN and add ports
					if($contadorVlans!=0){
						for($cont = 0; $cont < count($vlans); $cont++){
							if($vlans[$cont]['vlan-id'] == $accessVlanID){
								$previousPort = $vlans[$cont]['ports'];
								$actualPort = $previousPort.",".$interfaz;
								$API->comm("/interface/ethernet/switch/vlan/set", array(
         				 				".id"     => $cont,
          								"ports" => $actualPort,
									));
							}

						}
										
					
						
					}

				//Set PORT mode NO SWITCHPORT
				

					//IF NO IP REMOVE IP ADDRESS				
					if($noSwitchportIP == null){
						for($cont = 0; $cont < count($ipAddress); $cont++){
							if($ipAddress[$cont]['interface'] == $interfaz){
								$API->comm("/ip/address/remove", array(
					 			".id"     => $cont,
								));
							}
						
							
						}
					}
					else{
							$API->comm("/ip/address/add", array(
							"address"=> $noSwitchportIP, 
							"interface"=> $interfaz,
							));
							}
					
					
					$API->comm("/interface/ethernet/switch/port/set", array(
         				".id"     => $interfaz,
          				"vlan-mode" => "disabled",
          				"vlan-header" => "leave-as-is",
					"default-vlan-id" => "0",
					));
				}$API->disconnect();
				
			
		}
//////// NO SWITCHPORT ////////		
///// CR /////
		
			else if(strcmp($identidadRS,"CR") == 0 ){

				for($cont = count($vlans) -1 ;$cont >= 0; $cont--){
					//Check if PORT exist in another VLAN, if exist DELETE PORT and create in NEW
					$pos= strpos($vlans[$cont]['ports'], $interfaz);
					
					
					if($pos !== false){
					
						//Get Actual Port to delete
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $vlans[$cont]['ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT and set SwitchPort Disabled
						if($previousPort == $actualPort.",switch1-cpu"){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						else {
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/set", array(
		 					".id"     => $cont,
							"ports" => $finalPort,
							));
							$API->disconnect();
							}						
						}
					}

					

					//Check if VLAN exist, if exist $contadorVlans is increased
					if($vlans[$cont]['vlan-id'] == $accessVlanID){
						$contadorVlans++;
					}

				}
				
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				//If VLAN dont exist, a new VLAN is created
					//GET Switch per Port to create VLAN
					
					if($contadorVlans==0){
						$API->comm("/interface/ethernet/switch/vlan/add", array(
         				 	"ports"     => $interfaz,
						"vlan-id" => $accessVlanID
					));
					}
				
				//If VLAN exist, edit VLAN and add ports
					if($contadorVlans!=0){
						for($cont = 0; $cont < count($vlans); $cont++){
							if($vlans[$cont]['vlan-id'] == $accessVlanID){
								$previousPort = $vlans[$cont]['ports'];
								$actualPort = $previousPort.",".$interfaz;
								$API->comm("/interface/ethernet/switch/vlan/set", array(
         				 				".id"     => $cont,
          								"ports" => $actualPort,
									));
							}

						}
										
					
						
					}

				//IF PORT WAS ACCESS, DELETE PORTS FROM INGRESS
			
				for($cont = count($estadoAccessCR) -1; $cont >= 0; $cont--){
					if($estadoAccessCR[$cont]['ports'] === $interfaz){
								$API->comm("/interface/ethernet/switch/ingress-vlan-translation/remove", array(
         				 				".id"     => $cont
									));
								
					}
				}

				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$estadoTrunkCR = $API->comm("/interface/ethernet/switch/egress-vlan-tag/print");
				$API->disconnect();
				}

				//IF PORT WAS TRUNK, DELETE PORTS FROM EGRESS
				for($cont = count($estadoTrunkCR) -1; $cont >= 0; $cont--){
					
					$posTrunk= strpos($estadoTrunkCR[$cont]['tagged-ports'], $interfaz);
					
					if($posTrunk !== false){
					
						//Get Actual Port to delelte
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $estadoTrunkCR[$cont]['tagged-ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT
						if($previousPort == $actualPort){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/egress-vlan-tag/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/egress-vlan-tag/set", array(
		 					".id"     => $cont,
							"tagged-ports" => $finalPort,
							));
							$API->disconnect();
						
						}
					}

				}



				//Set PORT mode NO SWITCHPORT
				

					//IF NO IP REMOVE IP ADDRESS				
					if($noSwitchportIP == null){
						for($cont = 0; $cont < count($ipAddress); $cont++){
							if($ipAddress[$cont]['interface'] == $interfaz)
								$API->comm("/ip/address/remove", array(
					 			".id"     => $cont,
								));
							}
						}
							else{

							$API->comm("/ip/address/add", array(
							"address"=> $noSwitchportIP, 
							"interface"=> $interfaz,
							));
							}
				$API->disconnect();
				
			}
		}
			
		}
			

//////// ACCESS ////////
///// RB /////
		if($modoPuerto == "Access"){
			if(strcmp($identidadRS,"RB") == 0 ){

			
				for($cont = count($vlans)-1 ;$cont >= 0; $cont--){
					//Check if PORT exist in another VLAN, if exist DELETE PORT and create in NEW
					$pos= strpos($vlans[$cont]['ports'], $interfaz);
					
					if($pos !== false){
					
						//Get Actual Port to delete
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $vlans[$cont]['ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT
						if($previousPort == $actualPort){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/set", array(
		 					".id"     => $cont,
							"ports" => $finalPort,
							));
							$API->disconnect();
						
						}
						}

					

					//Check if VLAN exist, if exist $contadorVlans is increased
					if($vlans[$cont]['vlan-id'] == $accessVlanID){
						$contadorVlans++;
					}

				}
				
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				//If VLAN dont exist, a new VLAN is created
					//GET Switch per Port to create VLAN
					$switchPerPort;
					for($cont2=0; $cont2<$numPortsSwitch; $cont2++){
						if($portsSwitch[$cont2]['name'] == $interfaz) {
							$switchPerPort = $portsSwitch[$cont2]['switch'];
							
						}
					}
					if($contadorVlans==0){
						$API->comm("/interface/ethernet/switch/vlan/add", array(
         				 	"ports"     => $interfaz,
          					"switch" => $switchPerPort,
						"vlan-id" => $accessVlanID
					));

						$API->comm("/interface/ethernet/switch/port/set", array(
         				 	".id"     => $switchPerPort,
          					"vlan-mode" => "fallback",
					));
					}
				
				//If VLAN exist, edit VLAN and add ports
					if($contadorVlans!=0){
						for($cont = 0; $cont < count($vlans); $cont++){
							if($vlans[$cont]['vlan-id'] == $accessVlanID){
								$previousPort = $vlans[$cont]['ports'];
								$actualPort = $previousPort.",".$interfaz;
								$API->comm("/interface/ethernet/switch/vlan/set", array(
         				 				".id"     => $cont,
          								"ports" => $actualPort,
									));
							}

						}
										
					
						
					}
				
				
				//DONT ALLOW TO SET PORT IN MODE ACCESS IF VLAN ISNT IN THE SAME SWITCH
				$vlans = $API->comm("/interface/ethernet/switch/vlan/print");
				for($cont2 = 0; $cont2 < count($portsSwitch); $cont2 ++){
					if($portsSwitch[$cont2]['name'] === $interfaz){
						for($cont3 = 0; $cont3 < count($vlans); $cont3 ++){
						if($accessVlanID === $vlans[$cont3]['vlan-id'] and $portsSwitch[$cont2]['switch'] === $vlans[$cont3]['switch']){
							
							//Set PORT mode ACCESS
							$API->comm("/interface/ethernet/switch/port/set", array(
         						 ".id"     => $interfaz,
          						"vlan-mode" => "secure",
          						"vlan-header" => "always-strip",
							"default-vlan-id" => $accessVlanID
							));
							$API->disconnect();}
						}
						}
					}
				
				
				}
			}
		

//////// ACCESS ////////
///// CR /////		
			else if(strcmp($identidadRS,"CR") == 0 ){

				for($cont = count($vlans) -1 ;$cont >= 0; $cont--){
					//Check if PORT exist in another VLAN, if exist DELETE PORT and create in NEW
					
					$pos= strpos($vlans[$cont]['ports'], $interfaz);
					
					if($pos !== false and $vlans[$cont]['vlan-id'] !== $accessVlanID){
					
						//Get Actual Port to delelte
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $vlans[$cont]['ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT
						if($previousPort == $actualPort.",switch1-cpu"){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/set", array(
		 					".id"     => $cont,
							"ports" => $finalPort,
							));
							$API->disconnect();
						
						}
					}

					

					//Check if VLAN exist, if exist $contadorVlans is increased
					if($vlans[$cont]['vlan-id'] == $accessVlanID){
						$contadorVlans++;
					}

				}
				
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				//If VLAN dont exist, a new VLAN is created
					
					if($contadorVlans==0){
						$API->comm("/interface/ethernet/switch/vlan/add", array(
         				 	"ports"     => $interfaz.", switch1-cpu",
						"vlan-id" => $accessVlanID
					));
					}
				
				//If VLAN exist, edit VLAN and add ports
					if($contadorVlans!=0){
						for($cont = 0; $cont < count($vlans); $cont++){
							if($vlans[$cont]['vlan-id'] == $accessVlanID){
								$previousPort = $vlans[$cont]['ports'];
								$actualPort = $previousPort.",".$interfaz;
								$API->comm("/interface/ethernet/switch/vlan/set", array(
         				 				".id"     => $cont,
          								"ports" => $actualPort,
									));
							}

						}
										
					}
						
				//IF INTERFACE IS ALREADY AN ACCESS PORT, DONT CREATE ANOTHER NEW	
				for($cont = 0; $cont < count($estadoAccessCR); $cont++){
					if($estadoAccessCR[$cont]['ports'] === $interfaz){
						$contadorAccess++;
					}

				}

				//If ACCESS dont exist, a new ACCESS is created
				if ($contadorAccess === 0 ){
				
					if ($API->connect($IP, $user, $password)) {
					$API->comm("/interface/ethernet/switch/ingress-vlan-translation/add", array(
         					 "ports"     => $interfaz,
          					"sa-learning" => "yes",
          					"customer-vid" => "0",
						"new-customer-vid" => $accessVlanID
					));
					}
				}
				
				//If ACCESS exist, edit ACCESS and edit ports
				if($contadorAccess!==0){
						for($cont2 = 0; $cont2 < count($estadoAccessCR); $cont2++){
							if($estadoAccessCR[$cont2]['ports'] === $interfaz){
								$API->comm("/interface/ethernet/switch/ingress-vlan-translation/set", array(
         				 				".id"     => $cont2,
          								"new-customer-vid" => $accessVlanID,
									));
							}
						}					
				}



				//IF PORT WAS TRUNK, DELETE PORTS FROM EGRESS
				for($cont = count($estadoTrunkCR) -1; $cont >= 0; $cont--){
					
					$posTrunk= strpos($estadoTrunkCR[$cont]['tagged-ports'], $interfaz);
					if($posTrunk !== false){
						//Get Actual Port to delelte
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $estadoTrunkCR[$cont]['tagged-ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT
					
						if($previousPort == $actualPort){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/egress-vlan-tag/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/egress-vlan-tag/set", array(
		 					".id"     => $cont,
							"tagged-ports" => $finalPort,
							));
							$API->disconnect();
						
						}
					}


				}
				$API->disconnect();
			}
		}
	}

//////// TRUNK ////////
///// RB /////

		if($modoPuerto == "Trunk"){
			if(strcmp($identidadRS,"RB") == 0 ){

			for($cont = count($vlans)-1 ;$cont >= 0; $cont--){
					//Check if PORT exist in another VLAN, if exist DELETE PORT and create in NEW
					$pos= strpos($vlans[$cont]['ports'], $interfaz);
					
					if($pos !== false){
					
						//Get Actual Port to delete
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $vlans[$cont]['ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT
						if($previousPort == $actualPort){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/set", array(
		 					".id"     => $cont,
							"ports" => $finalPort,
							));
							$API->disconnect();
						
						}
					}
				}


		//Create Vlan per Allowed Vlan in Trunk
			for($contAllowed = 0; $contAllowed < count($allowedVlans);$contAllowed++){
				
				for($cont = 0;$cont < count($vlans); $cont++){

			//Check if VLAN exist, if exist $contadorVlans is increased
			
					if($vlans[$cont]['vlan-id'] == $allowedVlans[$contAllowed]){
						$contadorVlans++;
					}
					


				}
				
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				//If VLAN doesnt exist, a new VLAN is created
					//GET Switch per Port to create VLAN
					$switchPerPort;
					for($cont2=0; $cont2<$numPortsSwitch; $cont2++){
						if($portsSwitch[$cont2]['name'] == $interfaz) {
							$switchPerPort = $portsSwitch[$cont2]['switch'];
							
						}
					}

					if($contadorVlans==0){
						$API->comm("/interface/ethernet/switch/vlan/add", array(
         				 	"ports"     => $interfaz.",".$switchPerPort."-cpu",
          					"switch" => $switchPerPort,
						"vlan-id" => $allowedVlans[$contAllowed]
					));
					
						

						$API->comm("/interface/ethernet/switch/port/set", array(
         				 	".id"     => $switchPerPort,
          					"vlan-mode" => "fallback",
					));
					}
					
				//If VLAN exist, edit VLAN and add ports
					else if($contadorVlans!=0){
						for($cont = 0; $cont < count($vlans); $cont++){
							if($vlans[$cont]['vlan-id'] == $allowedVlans[$contAllowed]){
								$previousPort = $vlans[$cont]['ports'];
								$actualPort = $previousPort.",".$interfaz;

								$pos= strpos($actualPort, $switchPerPort."-cpu");
					
								if($pos !== false){
								$API->comm("/interface/ethernet/switch/vlan/set", array(
         				 				".id"     => $cont,
          								"ports" => $actualPort,
									));
								}
								else{
								$API->comm("/interface/ethernet/switch/vlan/set", array(
         				 				".id"     => $cont,
          								"ports" => $actualPort.",".$switchPerPort."-cpu",
									));
								}
							}

						}
										
					
						
					}
				//Reseteamos contadorVlans para todas las allowed Vlans
					$contadorVlans = 0;

				}

				//Set PORT mode TRUNK
				$API->comm("/interface/ethernet/switch/port/set", array(
         			 ".id"     => $interfaz,
          			"vlan-mode" => "secure",
          			"vlan-header" => "add-if-missing",
				));
				$API->disconnect();
				}
			}

		
//////// TRUNK ////////
///// CR /////
			

		else if(strcmp($identidadRS,"CR") == 0 ){

		//DELETE PORTS IF WE CHANGE FROM ACCESS TO TRUNK


				//Create Vlan per Allowed Vlan in Trunk
			for($contAllowed = 0; $contAllowed < count($allowedVlans);$contAllowed++){
				
				
				for($cont = count($vlans) -1 ;$cont >= 0; $cont--){
					//Check if PORT exist in another VLAN, if exist DELETE PORT and create in NEW
					
					$pos= strpos($vlans[$cont]['ports'], $interfaz);
					
					if($pos !== false and $vlans[$cont]['vlan-id'] !== $allowedVlans[$contAllowed]){
					
						//Get Actual Port to delelte
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $vlans[$cont]['ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT
						if($previousPort == $actualPort.",switch1-cpu"){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/vlan/set", array(
		 					".id"     => $cont,
							"ports" => $finalPort,
							));
							$API->disconnect();
						
						}
					}
			}
		}

		//IF PORT WAS TRUNK, DELETE PORTS FROM EGRESS
				for($cont = count($estadoTrunkCR) -1; $cont >= 0; $cont--){
					
					$posTrunk= strpos($estadoTrunkCR[$cont]['tagged-ports'], $interfaz);
					if($posTrunk !== false){
						//Get Actual Port to delelte
						$actualPort = $interfaz;
						//Get all ports of VLAN
						$previousPort = $estadoTrunkCR[$cont]['tagged-ports'];
						//Delete port from previousPort
						$finalPort = str_replace($actualPort,"",$previousPort);
						//Replace ,, to , if the port is deleted in the middle of string
						$finalPort = str_replace(',,',",",$finalPort);
						//Delete the last comma
						if ($finalPort[strlen($finalPort)-1] == ","){
					
							$finalPort = rtrim($finalPort,',');
						}

						//DELETE VLAN if last PORT
					
						if($previousPort == $actualPort){
							$API = new routeros_api();
							if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/egress-vlan-tag/remove", array(
		 					".id"     => $cont
							));
							$API->disconnect();
							}
						}
						//Delete Port from VLAN
						$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
							$API->comm("/interface/ethernet/switch/egress-vlan-tag/set", array(
		 					".id"     => $cont,
							"tagged-ports" => $finalPort,
							));
							$API->disconnect();
						
						}
					}


				}
			$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$vlans = $API->comm("/interface/ethernet/switch/vlan/print");
				$API->disconnect();
				}
		//CREATE VLANS AND SET PORTS MODE TRUNK

		for($contAllowed = 0; $contAllowed < count($allowedVlans);$contAllowed++){
				
				for($cont = count($vlans) -1 ;$cont >= 0; $cont--){
			//Check if VLAN exist, if exist $contadorVlans is increased
				
					if($vlans[$cont]['vlan-id'] == $allowedVlans[$contAllowed]){
						$contadorVlans++;
						
					}
					
				}


				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$estadoTrunkCR = $API->comm("/interface/ethernet/switch/egress-vlan-tag/print");
				$API->disconnect();
				}
			//Check if another Port is Trunking the VLAN ID
				for($cont = 0;$cont < count($estadoTrunkCR); $cont++){
					if($estadoTrunkCR[$cont]['vlan-id'] == $allowedVlans[$contAllowed]){
						$previousPorts = $estadoTrunkCR[$cont]['tagged-ports'];
						$actualPort = $previousPorts.",".$interfaz;
							if ($API->connect($IP, $user, $password)) {
								$API->comm("/interface/ethernet/switch/egress-vlan-tag/set", array(
         				 				".id"     => $cont,
          								"tagged-ports" => $actualPort,
									));
								$API->disconnect();

							}

					}

				}
				
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				//If VLAN dont exist, a new VLAN is created
					
					if($contadorVlans==0){
						$API->comm("/interface/ethernet/switch/vlan/add", array(
         				 	"ports"     => $interfaz.", switch1-cpu",
						"vlan-id" => $allowedVlans[$contAllowed]
					));
					
					}
					
				//If VLAN exist, edit VLAN and add ports
					else if($contadorVlans!=0){
						
						for($cont = 0; $cont < count($vlans); $cont++){
							if($vlans[$cont]['vlan-id'] == $allowedVlans[$contAllowed]){
								$previousPort = $vlans[$cont]['ports'];
								$actualPort = $previousPort.",".$interfaz;
								$API->comm("/interface/ethernet/switch/vlan/set", array(
         				 				".id"     => $cont,
          								"ports" => $actualPort,
									));
							}

						}
										
					
						
					}
				//Reseteamos contadorVlans para todas las allowed Vlans
					$contadorVlans = 0;

				}

				//IF PORT WAS ACCESS, DELETE PORTS FROM INGRESS
			
				for($cont = count($estadoAccessCR) -1; $cont >= 0; $cont--){
					if($estadoAccessCR[$cont]['ports'] === $interfaz){
								$API->comm("/interface/ethernet/switch/ingress-vlan-translation/remove", array(
         				 				".id"     => $cont
									));
								
					}
				}

				//Set PORT mode TRUNK
				$API->comm("/interface/ethernet/switch/egress-vlan-tag/add", array(
         			 "tagged-ports"     => $interfaz,
          			"vlan-id" => $allowedVlans[$contAllowed]
				));
				$API->disconnect();
				}
			}
		}
	}
		

	


?>


<!--------------------------- Eliminar Access o Trunk----------------------------->
<?php

//////// DELETE ACCESS ////////
///// RB /////


for ($cont = 0; $cont < count($portsSwitch); $cont++){
		if(isset($_POST['disableAccessRB'.$cont])){
		
			//Delete determinated port
			for($cont2 = 0; $cont2 < count($vlans); $cont2++){
			if($portsSwitch[$cont]['default-vlan-id'] == $vlans[$cont2]['vlan-id']){
				//Get Actual Port to delelte
				$actualPort = $portsSwitch[$cont]['name'];
				//Get all ports of VLAN
				$previousPort = $vlans[$cont2]['ports'];
				//Delete port from previousPort
				$finalPort = str_replace($actualPort,"",$previousPort);
				//Replace ,, to , if the port is deleted in the middle of string
				$finalPort = str_replace(',,',",",$finalPort);
				//Delete the last comma
				if ($finalPort[strlen($finalPort)-1] == ","){
					
					$finalPort = rtrim($finalPort,',');
				}

				$switchPerPort;
					for($cont3=0; $cont3<$numPortsSwitch; $cont3++){
						if($portsSwitch[$cont3]['name'] == $actualPort) {
							$switchPerPort = $portsSwitch[$cont3]['switch'];
						}
					}
			//DELETE VLAN if last PORT and set SwitchPort Disabled
				if(($previousPort == $switchPerPort."-cpu,".$actualPort) or $previousPort == $actualPort.",".$switchPerPort."-cpu" or $previousPort == $actualPort){
					
					$API = new routeros_api();

					if ($API->connect($IP, $user, $password)) {
					$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 				".id"     => $cont2
						));
					
					$API->comm("/interface/ethernet/switch/port/set", array(
         				 	".id"     => $switchPerPort,
          					"vlan-mode" => "disabled",
					));
					
					

					$API->disconnect();
					}


				}
			//Delete Port from VLAN
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$API->comm("/interface/ethernet/switch/vlan/set", array(
		 				".id"     => $cont2,
						"ports" => $finalPort,
						));
				$API->disconnect();
				}
			}
			}


				//Set port to No Switchport
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$API->comm("/interface/ethernet/switch/port/set", array(

         			 ".id"     => $cont,
          			"vlan-mode" => "disabled",
          			"vlan-header" => "leave-as-is",
				"default-vlan-id" => "0",
				));
				$API->disconnect();
				}	
		}
}




//////// DELETE TRUNK////////
///// RB /////



for ($cont = 0; $cont < count($portsSwitch); $cont++){


	if(isset($_POST['disableTrunkRB'.$cont])){
		
			
			//Delete determinated port
			//INVERSE FOR TO MAKE BETTER DELETION
			for($cont2 = count($vlans) - 1; $cont2 >= 0; $cont2--){
				
				//Get Actual Port to delete
				$actualPort = $portsSwitch[$cont]['name'];
				//Get all ports of VLAN
				$previousPort = $vlans[$cont2]['ports'];
				//Delete port from previousPort
				$finalPort = str_replace($actualPort,"",$previousPort);
				//Replace ,, to , if the port is deleted in the middle of string
				$finalPort = str_replace(',,',",",$finalPort);
				
				//Delete the last comma
				if ($finalPort[strlen($finalPort)-1] == ","){
					
					$finalPort = rtrim($finalPort,',');
				}
			
				//Delete Port from VLAN
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$API->comm("/interface/ethernet/switch/vlan/set", array(
		 				".id"     => $cont2,
						"ports" => $finalPort,
						));
				}
				
				
					$switchPerPort;
					for($cont3=0; $cont3<$numPortsSwitch; $cont3++){
						if($portsSwitch[$cont3]['name'] == $actualPort) {
							$switchPerPort = $portsSwitch[$cont3]['switch'];
						}
					}
			//DELETE VLAN if last PORT
				
				
					if(strcmp($previousPort, $actualPort.",".$switchPerPort."-cpu") === 0 or strcmp($previousPort, $switchPerPort."-cpu,".$actualPort) === 0){

				

					$API = new routeros_api();
						if ($API->connect($IP, $user, $password)) {
						$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 					".id"     => $cont2
							));

					
					$API->comm("/interface/ethernet/switch/port/set", array(
         				 	".id"     => $switchPerPort,
          					"vlan-mode" => "disabled",
					));
					
						
					}
				}
			}


				//Set port to No Switchport
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$API->comm("/interface/ethernet/switch/port/set", array(

         			 ".id"     => $cont,
          			"vlan-mode" => "disabled",
          			"vlan-header" => "leave-as-is",
				"default-vlan-id" => "0",
				));
				$API->disconnect();
				}	
		}
	
}

//////// DELETE ACCESS ////////
///// CR /////
for ($cont = 0; $cont < count($estadoAccessCR); $cont++){
		if(isset($_POST['disableAccessCR'.$cont])){

				//Delete determinated port
			for($cont2 = count($vlans); $cont2 >= 0 ; $cont2--){
			if($estadoAccessCR[$cont]['new-customer-vid'] == $vlans[$cont2]['vlan-id']){
				//Get Actual Port to delete
				$actualPort = $estadoAccessCR[$cont]['ports'];
				//Get all ports of VLAN
				$previousPort = $vlans[$cont2]['ports'];
				//Delete port from previousPort
				$finalPort = str_replace($actualPort,"",$previousPort);
				//Replace ,, to , if the port is deleted in the middle of string
				$finalPort = str_replace(',,',",",$finalPort);
				//Delete the last comma
				if ($finalPort[strlen($finalPort)-1] == ","){
					
					$finalPort = rtrim($finalPort,',');
				}

			//DELETE VLAN if last PORT
				if($previousPort == $actualPort.",switch1-cpu"){
					$API = new routeros_api();
					if ($API->connect($IP, $user, $password)) {
					$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 				".id"     => $cont2
						));
					$API->disconnect();
					}
				}
			//Delete Port from VLAN
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$API->comm("/interface/ethernet/switch/vlan/set", array(
		 				".id"     => $cont2,
						"ports" => $finalPort,
						));
				$API->disconnect();
				}
			}
			}

		//REMOVE PORT IN INGRESS VLAN TRANSLATION
			
			$API = new routeros_api();
			$IP = $_SESSION[ 'ip' ];
			$user = $_SESSION[ 'user' ];
			$password = $_SESSION[ 'password' ];
			if ($API->connect($IP, $user, $password)) {
				$API->write("/interface/ethernet/switch/ingress-vlan-translation/remove",false);
				$API->write("=.id=".$cont);
				$Ports = $API->read();
				$API->disconnect();
		}}
		}


//////// DELETE TRUNK ////////
///// CR /////

for ($cont = 0; $cont < count($arrayPorts); $cont++){
		
		if(isset($_POST['disableTrunkCR'.$cont])){

			//Delete determinated port
			for($cont2 = count($vlans) - 1; $cont2 >= 0  ; $cont2--){
			//Check if Port selected allows to a VLAN
			$pos = strpos($vlans[$cont2]['ports'],$arrayPorts[$cont]);
			if($pos !== false){
				
				//Get Actual Port to delete
				$actualPort = $arrayPorts[$cont];

				//Get all ports of VLAN
				$previousPort = $vlans[$cont2]['ports'];
				//Delete port from previousPort
				$finalPort = str_replace($actualPort,"",$previousPort);
				//Replace ,, to , if the port is deleted in the middle of string
				$finalPort = str_replace(',,',",",$finalPort);
				//Delete the last comma
				if ($finalPort[strlen($finalPort)-1] == ","){
					
					$finalPort = rtrim($finalPort,',');
				}

			//DELETE VLAN if last PORT
				if($previousPort == $actualPort.",switch1-cpu"){
					$API = new routeros_api();
					if ($API->connect($IP, $user, $password)) {
					$API->comm("/interface/ethernet/switch/vlan/remove", array(
		 				".id"     => $cont2
						));
					$API->disconnect();
					}
				}
			//Delete Port from VLAN
				else {
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$API->comm("/interface/ethernet/switch/vlan/set", array(
		 				".id"     => $cont2,
						"ports" => $finalPort,
						));
				$API->disconnect();
				}}
			}
			
			}

	
		//REMOVE PORT IN EGRESS VLAN TAG
		$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$estadoTrunkCR = $API->comm("/interface/ethernet/switch/egress-vlan-tag/print");
				$API->disconnect();
				}
		for($cont2 = count($estadoTrunkCR) - 1; $cont2 >= 0  ; $cont2--){
	
		

				//Get Actual Port to delete
				$actualPort = $arrayPorts[$cont];
				//Get all ports of VLAN
				$previousPort = $estadoTrunkCR[$cont2]['tagged-ports'];
				//Delete port from previousPort
				$finalPort = str_replace($actualPort,"",$previousPort);
				//Replace ,, to , if the port is deleted in the middle of string
				$finalPort = str_replace(',,',",",$finalPort);
				//Delete the last comma
				if ($finalPort[strlen($finalPort)-1] == ","){
					
					$finalPort = rtrim($finalPort,',');
				}
			

			//DELETE VLAN if last PORT
				if($previousPort === $actualPort){
					$API = new routeros_api();
					if ($API->connect($IP, $user, $password)) {
					$API->write("/interface/ethernet/switch/egress-vlan-tag/remove",false);
						$API->write("=.id=".$cont2);
						$Ports = $API->read();
						$API->disconnect();
					}
				}
			//Delete Port from VLAN
				else
				{
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
				$API->comm("/interface/ethernet/switch/egress-vlan-tag/set", array(
		 				".id"     => $cont2,
						"tagged-ports" => $finalPort,
						));
				$API->disconnect();
				}
				}
			
		}}


}

?>

<!--Boton cerrar sesión-->

<?php
	if($_GET['logOut'] == 'yes'){
		session_destroy();
		header( 'Location:index.php'); 
}

?>
<script src="jquery/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>	


<!--Script para actualizar imagenes y contenido automaticamente-->
<script type="text/javascript">
  $(document).ready(function(){
			var auto_refresh = setInterval(function (){
			$('#refreshImage').load('datosVlansImage.php');
			$('#refreshVlans').load('datosVlans.php');
			}, 3000);

			var auto_refresh = setInterval(function (){
			$('#info').load('datosCPU.php');
			}, 3000);
		});		
			

 
	
 </script>


<!--Script para mostrar Access, Trunk o NS -->
<script type="text/javascript">
    $(document).ready(function(){
        $("input[value=Access]:radio" ).change(function(){
                $('#areaAccess').show("fast");
		$('#areaTrunk').hide("fast");
		$('#areaNoSwitchport').hide("fast");
		$('input[name=submitButton]').show("fast");
            });

	$("input[value=Trunk]:radio" ).change(function(){
                $('#areaTrunk').show("fast");
		$('#areaAccess').hide("fast");
		$('#areaNoSwitchport').hide("fast");
		$('input[name=submitButton]').show("fast");
            });

	$("input[value=NoSwitchport]:radio" ).change(function(){
                $('#areaNoSwitchport').show("fast");
		$('#areaTrunk').hide("fast");
		$('#areaAccess').hide("fast");
		$('input[name=submitButton]').show("fast");
            });
});
</script>


</body>
</html>
