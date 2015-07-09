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

		//Todas Interfaces
		$Interfaces= $API->comm("/interface/print");
		$numInterfaces = count($Interfaces);

		//Modelo
		$modeloCom = $API->comm("/system/routerboard/print");
		$modelo=$modeloCom[0]['model'];
		//Estado Link

		$valoresPar= json_encode(range(0, $numPorts-1));
		$valores = substr($valoresPar, 1, -1);

		//Switch
		$switches = $API->comm("/interface/ethernet/switch/print");
		$numSwitches = count($switches);

		//Interfaz VLAN
		$interfazVlan = $API->comm('/interface/vlan/print');	

		//Interfaz bridge
		$interfazBridge = $API->comm('/interface/bridge/print');	

		//routes
		$routes = $API->comm('/ip/route/print');	

		//IP addresses
		$ipAddress = $API->comm('/ip/address/print');		
		//CPU
		$cpuInfo = $API->comm("/system/resource/print");
		//RB o CS
		$routeroSwitch = $cpuInfo[0]['board-name'];
		//Saber iniciales Router o Switch
		$identidadRS = substr($routeroSwitch,0,2);

		$API->write("/interface/ethernet/monitor",false);
		$API->write("=numbers=".$valores,false);  
		$API->write("=once=",true);
		$READ = $API->read(false);
		$statusPorts = $API->parse_response($READ);
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
                <li><a href="Vlans.php">VLANs</a></li>
		<li class="active"><a href="Routing.php">Routing</a></li>
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
			<div class="col-lg-1"></div>
			<div class="col-lg-5">
				<div id="refreshPorts">
				<?php
					echo "<h3>VLAN Interface</h3>";
					echo "<table class='tableRouting'>
						<tr><th>Name</th>
							<th>VLAN ID</th>
							<th>Interface</th>
							<th>IP Address</th></tr>";
					for ($cont = 0; $cont < count($interfazVlan); $cont++){
						echo '<tr>
							<td>'.$interfazVlan[$cont]['comment'].'</td>';
						echo '<td>'.$interfazVlan[$cont]['vlan-id'].'</td>';
						echo '<td>'.$interfazVlan[$cont]['interface'].'</td>';

						for($cont1 = 0; $cont1 < count($ipAddress); $cont1++){
							
							if($interfazVlan[$cont]['name'] == $ipAddress[$cont1]['interface']){
								echo '<td>'.$ipAddress[$cont1]['address'].'</td>';
						}}
						echo "<td><form name='button$cont' method='post'>
							<input type='submit' name='disableInterface$cont' value='X' class='buttonDisable'/>
							</form></td>";
			

						echo '</tr>';
					}
				
					echo "</table>";

	//TABLE ROUTES//
					echo "<h3>Routes</h3>";
					echo "<table class='tableRouting'>
						<tr><th>Destination Address</th>
							<th>Gateway</th>
							<th>Dynamic/Static</th></tr>";
					for ($cont = 0; $cont < count($routes); $cont++){
						echo '<tr>
							<td>'.$routes[$cont]['dst-address'].'</td>';
						echo '<td>'.$routes[$cont]['gateway'].'</td>';
						if($routes[$cont]['dynamic']=='true'){
							echo '<td>D</td>';
						}
						else{
							echo '<td>S</td>';
						}
						echo "<td><form name='button$cont' method='post'>
							<input type='submit' name='disableInterfaceRouting$cont' value='X' class='buttonDisable'/>
							</form></td>";
			

						echo '</tr>';
					}
				
					echo "</table>";
							
							

				?>
				</div>

			</div>
			<div class="col-lg-3 routingBox">
		
		<!-- FORM INTERFACES VLAN-->		
			<h3>VLAN Interface</h3>
			<div>
				<form method='post' action='#' name='formRouting'>
				Master interface:
				<div class='styled-select'>
				<select name='interfaces'>
				<option value>Master interface</option>
				<?php
				
				for ($cont = 0; $cont < $numPorts; $cont++){
					if($Ports[$cont]['master-port'] === 'none'){
					$interfazSel = $Ports[$cont]['name'];
					echo "<option value=$interfazSel>$interfazSel"." (Master Port)</option>";}
			
				}
				for ($cont = 0; $cont < count($interfazBridge); $cont++){
					$interfazSel = $interfazBridge[$cont]['name'];
					echo "<option value=$interfazSel>$interfazSel"." (Bridge) </option>";
				}
		
				echo "</select></div>";
				?>
				</br> VLAN ID:</br> <input name='VlanID' type='number' min='0' max='4095' placeholder='100'/></br>
				</br> VLAN Name:</br> <input name='VlanName' type='text' placeholder='Management VLAN'/></br>
				</br> IP Address:</br> <input name='VlanAddress' type='text' placeholder='192.168.100.1/24'/></br>

				</br><input type='submit' name='submitButton' value='Submit'/>
				</form>


			</div>	
			</div>

	<!-- FORM ROUTES -->
				<div class="col-lg-3 routingBox">

				<form method='post' action='#' name='formRouting'>

				<h3>Routes</h3>

				Destination Address:</br> <input name='dstAddress' type='text' placeholder='0.0.0.0/0'/></br>

				</br> Gateway: </br>
				<input name='gateway' type='text' placeholder='192.168.80.1'/></br>
			
				</br><input type='submit' name='submitButton2' value='Submit'/>
				</form>


			</div>	
			</div>
			
	</div>

<?php

$interfaz = $_POST['interfaces'];
$VlanID = $_POST['VlanID'];
$VlanName = $_POST['VlanName'];
$VlanAddress = $_POST['VlanAddress'];
$dstAddress = $_POST['dstAddress'];
$gateway = $_POST['gateway'];

	if(isset($_POST['submitButton'])){
			$API = new routeros_api();
			if ($API->connect($IP, $user, $password)) {
			$API->comm("/interface/vlan/add", array(
         		 "interface"     => $interfaz,
          		"vlan-id" => $VlanID,
          		"name" => "VLAN-".$VlanID,
			"comment" => $VlanName,
			));

			$API->comm("/ip/address/add", array(
			"address"=> $VlanAddress, 
			"interface"=> "VLAN-".$VlanID,
			));
			$API->disconnect();
			
		}
	}

	if(isset($_POST['submitButton2'])){
			//IF selected gateway was selected and ip gateway too, send ip gateway
			
				$API = new routeros_api();
				if ($API->connect($IP, $user, $password)) {
					$API->comm("/ip/route/add", array(
         				 "dst-address"     => $dstAddress,
          				"gateway" => $gateway,
						));

					$API->disconnect();}
			}
	


?>
<!-- Eliminar Interfaz-->
<?php


for ($cont = 0; $cont < count($interfazVlan); $cont++){
	if(isset($_POST['disableInterface'.$cont])){
			$API = new routeros_api();
			$IP = $_SESSION[ 'ip' ];
			$user = $_SESSION[ 'user' ];
			$password = $_SESSION[ 'password' ];
			if ($API->connect($IP, $user, $password)) {
				$API->write("/interface/vlan/remove",false);
				$API->write("=.id=".$cont);
				$Ports = $API->read();
				$API->disconnect();
				for ($cont1 = 0; $cont1 < count($ipAddress); $cont1++){
				
					if($interfazVlan[$cont]['name'] == $ipAddress[$cont1]['interface']){
						if ($API->connect($IP, $user, $password)) {
							$API->write("/ip/address/remove",false);
							$API->write("=.id=".$cont1);
							$Ports = $API->read();
							$API->disconnect();
						}		
					}
				}			
		}
	}
}

// ELIMINAR RUTA //

for ($cont = 0; $cont < count($routes); $cont++){
	if(isset($_POST['disableInterfaceRouting'.$cont])){
			$API = new routeros_api();
			$IP = $_SESSION[ 'ip' ];
			$user = $_SESSION[ 'user' ];
			$password = $_SESSION[ 'password' ];
			if ($API->connect($IP, $user, $password)) {
				$API->write("/ip/route/remove",false);
				$API->write("=.id=".$cont);
				$Ports = $API->read();
				$API->disconnect();
						
		}
	}
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
			$('#refreshImage').load('datosStatusImage.php');
			$('#refreshPorts').load('datosRouting.php');
			}, 3000);

			var auto_refresh = setInterval(function (){
			$('#info').load('datosCPU.php');
			}, 3000);
		});		
			

 
	
  </script>

</body>
</html>
