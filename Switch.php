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
		
		//Estado Link
		$valoresPar= json_encode(range(0, $numPorts-1));
		$valores = substr($valoresPar, 1, -1);

		//CPU
		$cpuInfo = $API->comm("/system/resource/print");

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


<!-- Barra de Menu-->
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
               <li  class="active"><a href="Switch.php">Switch</a></li>
                <li><a href="Vlans.php">VLANs</a></li>
		<li><a href="Routing.php">Routing</a></li>
		<li><a href="ACLs.php">ACLs</a></li>
            </ul>
        </div> 
    	</div>
    </nav>


<!-- Imagen Equipo con color de Puertos segun Switch -->
<div class="container" style="margin-top:50px;">

      	<div class="row">
		<div class="col-lg-12 switch-box">
			<div class="col-lg-2"></div>
			<div class="col-lg-6">
				<div>
				 <div id="refreshImage">
				
				<?php
				$contSwitchImg=0;
				for ($cont = 0; $cont < $numPorts; $cont++){
					if($Ports[$cont]['master-port']=='none'){

					$contSwitchImg=$contSwitchImg+1;}

					for($cont2 = 0; $cont2 < $numPorts; $cont2++){
								if($Ports[$cont]['name']==$Ports[$cont2]['master-port']){
									if($statusPorts[$cont2]['status']=='link-ok'){
						echo "<svg version='1.1' id='etherGreen$cont2' style='fill:".$colores[$contSwitchImg-1]."' xmlns='http://www.w3.org/2000/svg'
						xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
						width='5.2%' height='5.2%' viewBox='0 0 15 11' style='enable-background:new 0 0 15 11;' xml:space='preserve''>
						<polygon class='st0' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
						</svg>";
					}
									
								}

							}

					if($statusPorts[$cont]['status']=='link-ok' and $Ports[$cont]['master-port']=='none'){	
							echo "<svg version='1.1' id='etherMaster$cont' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='5.2%' height='5.2%' viewBox='0 0 15 11' style='fill:".$colores[$contSwitchImg-1]."; enable-background:new 0 0 15 11;' xml:space='preserve'>
								<style type='text/css'>
								<![CDATA[
								.st0{font-size:9px;}
								.st2{font-family:'Open Sans';}
								.st3{fill:#000;}
								]]>
								</style>

								<polygon class='st1' points='10.7,2.7 10.7,0.5 4.5,0.5 4.5,2.7 0.3,2.7 0.3,11 15,11 15,2.7 '/>
								<text transform='matrix(1.0151 0 0 1 3.375 10.2891)' class='st3 st2 st0'>m</text>
								</svg>";
							
						}
				
					
						
				}

				echo "<img src='images/$modelo.png'></div>"; 			
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
			<div id="refreshPorts">
				<table class="SwitchTable">
				<?php
				$contSwitch=0;
				for ($cont = 0; $cont < $numPorts; $cont++){
						
						if($Ports[$cont]['master-port']=='none'){
							$contSwitch=$contSwitch+1;
							echo "<tr>";
							echo "<th style='border-bottom: 3px solid ".$colores[$contSwitch-1].";'>Switch ".$contSwitch."</th></tr>";
							echo "<tr>";
							echo "<td id='master-port'>".$Ports[$cont]['name']." <b>(Master-port)</b></td>";
							for($cont2 = 0; $cont2 < $numPorts; $cont2++){
								if($Ports[$cont]['name']==$Ports[$cont2]['master-port']){
									echo "<tr><td>".$Ports[$cont2]['name']."</td></tr>";
								}

							}
						

						}
					
				}
						
				?>
				</table>
			</div>
			</div>
			<div class="col-lg-6">
				<table>
				<td>
				<table class="SwitchTable">
				<?php

					
					for ($cont = 0; $cont < $numPorts; $cont++){
					
						echo "<tr>
						<td>".$Ports[$cont]['name']."</td>
						<td>
						<form action=Switch.php method=post>";
						echo "<div class='styled-selectSwitch'><select name='formMaster$cont' onchange='this.form.submit()'>
  							<option value=''>".$Ports[$cont]['master-port']."</option>";
							
							for ($cont2 = 0; $cont2 < $numPorts; $cont2++){
								if ($Ports[$cont]['master-port'] != $Ports[$cont2]['name']){
								
								
								echo "<option value='".$Ports[$cont2]['name']."'>".$Ports[$cont2]['name']."</option>"; 
								} 								
								
							}
							if ($Ports[$cont]['master-port'] != 'none'){
							echo "<option value='none'>".none."</option>";}

						echo "</select</div>></form>";	
						echo "</td>";
									
						echo "</tr>";
					}
				?>
					</table>
				</td>
				<td>
				
			
			</div>		
		</div>
	</div>

</div>

<?php
		for ($cont = 0; $cont < $numPorts; $cont++){
			if(isset($_POST['formMaster'.$cont])){
			$seleccion= $_POST['formMaster'.$cont];

			$API = new routeros_api();
			$IP = $_SESSION[ 'ip' ];
			$user = $_SESSION[ 'user' ];
			$password = $_SESSION[ 'password' ];
			if ($API->connect($IP, $user, $password)) {
				$API->write("/interface/ethernet/set",false);
				$API->write("=master-port=".$seleccion,false);
				$API->write("=.id=".$Ports[$cont]['name']);
				$ARRAY = $API->read();
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
			$('#refreshImage').load('datosSwitchImage.php');
			$('#refreshPorts').load('datosSwitch.php');
			}, 3000);

			var auto_refresh = setInterval(function (){
			$('#info').load('datosCPU.php');
			}, 3000);
		});		
			

 
	
  </script>
	

</body>

</html>
