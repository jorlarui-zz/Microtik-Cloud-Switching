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
		$ARRAY = $API->comm("/interface/print");
		$interfaces = count($ARRAY);
		$Ports = $API->comm("/interface/ethernet/print");
		$numPorts = count($Ports);

		//Modelo
		$modeloCom = $API->comm("/system/routerboard/print");
		$modelo=$modeloCom[0]['model'];

		//Firewall rules
		$firewall = $API->comm("/ip/firewall/filter/print");
		$numFirewall=count($firewall);
		
		//Estado Link
		$valoresPar= json_encode(range(0, $numPorts-1));
		$valores = substr($valoresPar, 1, -1);

		//Interfaz VLAN
		$interfazVlan = $API->comm('/interface/vlan/print');	

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
			header( 'Location:index.php?notLogin=true' );

		}

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
		<li><a href="Vlans.php">Vlans</a></li>
		<li><a href="Routing.php">Routing</a></li>
		<li class="active"><a href="ACLs.php">ACLs</a></li>
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
		<div class="col-lg-12 info-box ACLBox">
				<table class='ACLTable'>
				<tr>
				<form method='post' action='#' name='formACL'>
				<td>	
				Source VLAN: 	
				<div class='styled-selectACL'>
				<select name='sourceVlan'>
				<option value=''>Select</option>
				<?php
					for($cont=0;$cont < count($interfazVlan); $cont++){
						$interfazSel = $interfazVlan[$cont]['name'];
						$comment = $interfazVlan[$cont]['comment'];
						echo "<option value=$interfazSel>$interfazSel"." ($comment)</option>";
					}

				?>
					
					
				</select>
				</div>
				</td>
				<td>	
				Permit/Deny: 	
				<div class='styled-selectACL'>
				<select name='permitDeny'>
					<option value='accept'>Permit</option>
					<option value='drop'>Deny</option>
				</select>
				</div>
				</td>
				<td>
				Destination Vlan
				<div class='styled-selectACL'>
				<select name='destinationVlan'>
				<option value=''>Select</option>
				<?php
					for($cont=0;$cont < count($interfazVlan); $cont++){
						$interfazSel = $interfazVlan[$cont]['name'];
						$comment = $interfazVlan[$cont]['comment'];
						echo "<option value=$interfazSel>$interfazSel"." ($comment)</option>";
					}

				?>
					
					
				</select>
				</div>

				</td>
				
				</table>

				<div class='buttonACL'>
					<input type='submit' name='submitButton' value='Submit'/>
				     </div>
				</form>

			
		
	
		<div class="col-lg-12 firewallRules">
			<div class="col-lg-2"></div>
			<div class="col-lg-8">	
				<div id="refreshACLs">
				<h3>ACLs</h3>
				<table class="ACLRules">
				
				<tr>
					
					<th>Src. VLAN</th>
					<th>Action</th>
					<th>Dst. VLAN</th>
				</tr>
				<?php
				
				for ($cont = 0; $cont < $numFirewall; $cont++){
					for($cont2=0;$cont2 < count($interfazVlan); $cont2++){
						if($firewall[$cont]['in-interface'] === $interfazVlan[$cont2]['name']){
							echo "<tr>";
							echo "<td>".$firewall[$cont]['in-interface']."</td>";
							if($firewall[$cont]['action']==='accept'){
								echo "<td id='permit'>Permit</td>";	
							}	
							else if($firewall[$cont]['action']==='drop'){
								echo "<td id='deny'>Deny</td>";	
							}
							else{
								echo "<td>".$firewall[$cont]['action']."</td>";	
							}
							echo "<td>".$firewall[$cont]['out-interface']."</td>";		
							
							echo "<td><form name='button$cont' method='post'>
							<input type='submit' name='disableRule$cont' value='X' class='button'/>
							</form></td>";		
							echo "</tr>";	
						}

					}
					
					
				}
						
				?>
				</table>
				</div>
			</div>
		<div class="col-lg-2"></div>
		</div>
	</div>

<?php

$permitDeny = $_POST['permitDeny'];
$sourceVlan= $_POST['sourceVlan'];
$destinationVlan= $_POST['destinationVlan'];

	if(isset($_POST['submitButton'])){
		$API = new routeros_api();
			if ($API->connect($IP, $user, $password)) {
				$API->comm("/ip/firewall/filter/add", array(
				"chain" => "forward",
         			"action"     => $permitDeny,
          			"in-interface" => $sourceVlan,
				"out-interface" => $destinationVlan,
			));

			
			$API->disconnect();
			}
		
	}

for ($cont = 0; $cont < $numFirewall; $cont++){
		if(isset($_POST['disableRule'.$cont])){
			$API = new routeros_api();
			$IP = $_SESSION[ 'ip' ];
			$user = $_SESSION[ 'user' ];
			$password = $_SESSION[ 'password' ];
			if ($API->connect($IP, $user, $password)) {
				$API->write("/ip/firewall/filter/remove",false);
				$API->write("=.id=".$cont);
				$Ports = $API->read();
				$API->disconnect();
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
			$('#refreshImage').load('datosStatusImage.php');
			$('#refreshACLs').load('datosACLs.php');
			}, 3000);

			var auto_refresh = setInterval(function (){
			$('#info').load('datosCPU.php');
			}, 3000);
		});		
			

 
	
  </script>
		

</body>
</html>
