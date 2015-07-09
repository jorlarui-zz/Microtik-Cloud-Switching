<?php ob_start(); session_start(); require('routeros_api.class.php'); ?>
<?php error_reporting (E_ALL ^ E_NOTICE); ?>

<script src="jquery/jquery.min.js"></script>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>



<!--Script para dibujar las graficas-->
<script> 
	var chart;
	function requestDatta(interface) {
		$.ajax({
			url: 'datosGraficas.php?interface='+interface,
			datatype: "json",
			success: function(data) {
				var midata = JSON.parse(data);
				if( midata.length > 0 ) {
					var TX=parseInt(midata[0].data);
					var RX=parseInt(midata[1].data);
					var x = (new Date()).getTime(); 
					shift=chart.series[0].data.length > 8;
					chart.series[0].addPoint([x, TX], true, shift);
					chart.series[1].addPoint([x, RX], true, shift);
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				console.error("Status: " + textStatus + " request: " + XMLHttpRequest); console.error("Error: " + errorThrown); 
			}       
		});
	}	

	$(document).ready(function() {
			Highcharts.setOptions({
				global: {
					useUTC: false
				}
			});
	

           chart = new Highcharts.Chart({
			   chart: {
				renderTo: 'container',
				animation: Highcharts.svg,
				type: 'spline',
				events: {
					load: function () {
						setInterval(function () {
							requestDatta(document.getElementById("interface").value);
						}, 1000);
					}				
			}
		 },
		 title: {
			text: ''
		 },
		 xAxis: {
			type: 'datetime',
				tickPixelInterval: 200,
				maxZoom: 20 * 400
		 },
		 yAxis: {
			minPadding: 0.2,
				maxPadding: 0.2,
				title: {
					text: 'Trafico Kbps',
					margin: 10
				}
		 },
            series: [{
                name: 'TX',
                data: []
            }, {
                name: 'RX',
                data: []
            }]
	  });
  });
</script>



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

		//CPU
		$cpuInfo = $API->comm("/system/resource/print");

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
		

	
<!--Script para dibujar las graficas-->



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
                <li class="active"><a href="Status.php">Status</a></li>
		<li><a href="Switch.php">Switch</a></li>
                <li><a href="Vlans.php">VLANs</a></li>
		<li><a href="Routing.php">Routing</a></li>
		<li><a href="ACLs.php">ACLs</a></li>
            </ul>
        </div> 
    	</div>
    </nav>


<!-- Imagen Equipo con color de Puertos segun el estado -->
<div class="container" style="margin-top:50px;">

      	<div class="row">
		<div class="col-lg-12 switch-box hideBox">
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

<!-- Informacion del equipo (Modelo, CPU, Tiempo de vida)-->

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
		<div class="col-lg-12 info-box graphicBox">
			<div class="col-lg-2">	</div>
			<div class="col-lg-4">
					<?php
//Creamos un formulario que actualiza la gráfica en función de la interfaz seleccionada
			echo "<form method=post>
				<b>Select interface:</b>  ".$_POST['interfaces']."
				<div class='styled-select'>
					<select name='interfaces' size='1' onchange='this.form.submit()'>
					<option value>Interface</option>";
			
			for ($cont = 0; $cont < $numPorts; $cont++){
			
				$interfazSel = $Ports[$cont]['name'];
				echo "<option value=$interfazSel>$interfazSel</option>";
			
			}		
			echo "</select></div></form>";
			
			
			$interfaz =  $_POST['interfaces'];
			$_SESSION[ 'interfaz' ]=$interfaz;
	
			
			?>
				<div class='graphics'>
				<div id="container"></div>
				<input name="interface" id="interface" type="text" value="rb_inalambricos" />
				</div>
				
				
				<button class="buttonIn">Zoom in</button>
				<button class="buttonOut" style="display:  none;">Zoom out</button>
			</div>
				
				

			<div class="col-lg-4 hideBox">
				<table>
				<td>

<!-- Información del puerto (link ok, no link)-->

					 <table id="refreshPorts">
          
           

				<?php
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
						echo "</tr>";
				?>
					</table>
				</td>
				<td>
				<table id="PortsButtons">
				 
<!-- Formulario para activar, desactivar puertos -->

				<?php
					for ($cont = 0; $cont < $numPorts; $cont++){
						echo "<tr>";
						echo "<form name='button$cont' method='post'>
							<td><input type='submit' name='enablePort$cont' value='&#10004' class='buttonGreen'/></td>
							<td><input type='submit' name='disablePort$cont' value='X' class='buttonRed'/></td>
							</form>";
						echo "</tr>";
					}	
				?>
				</table>
				</td>
			</table>
			</div>
			</div>
			<div class="col-lg-2"></div>
	</div>

<?php
		for ($cont = 0; $cont < $numPorts; $cont++){
		if(isset($_POST['enablePort'.$cont])){
			$API = new routeros_api();
			$IP = $_SESSION[ 'ip' ];
			$user = $_SESSION[ 'user' ];
			$password = $_SESSION[ 'password' ];
			if ($API->connect($IP, $user, $password)) {
				$API->write("/interface/ethernet/set",false);
				$API->write("=disabled=no",false);
				$API->write("=.id=".$Ports[$cont]['name']);
				$Ports = $API->read();
				$API->disconnect();
		}}
		}

		for ($cont = 0; $cont < $numPorts; $cont++){
		if(isset($_POST['disablePort'.$cont])){
			$API = new routeros_api();
			$IP = $_SESSION[ 'ip' ];
			$user = $_SESSION[ 'user' ];
			$password = $_SESSION[ 'password' ];
			if ($API->connect($IP, $user, $password)) {
				$API->write("/interface/ethernet/set",false);
				$API->write("=disabled=yes",false);
				$API->write("=.id=".$Ports[$cont]['name']);
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
<script src="jquery/jquery-ui.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>	


<!--Script para actualizar imagenes y contenido automaticamente-->
<script type="text/javascript">
  $(document).ready(function(){
			var auto_refresh = setInterval(function (){
			$('#refreshImage').load('datosStatusImage.php');
			$('#refreshPorts').load('datosStatus.php');
			}, 3000);

			var auto_refresh = setInterval(function (){
			$('#info').load('datosCPU.php');
			}, 3000);
		});		
			

 
	
  </script>


<!-- Script Zoom Grafica -->

 <script>
$(document).ready(function(){

	    $(".buttonIn").click(function(){		
	   	$(this).hide();
      		$(".hideBox").addClass("hideElement",400, "swing");
		$(".graphics").addClass("maxBox",400, "swing");
		$("#highcharts-0").addClass("hideElement",400, "swing");
	//	$("img").addClass("imgBig");
		
		$(".col-lg-4").addClass("colMax");
		$(".buttonOut").show();

		Highcharts.setOptions({
				global: {
					useUTC: false
				}

			});
	

           chart = new Highcharts.Chart({
			   chart: {
				renderTo: 'container',
				animation: Highcharts.svg,
				type: 'spline',
				
		 },
		 title: {
			text: ''
		 },
		 xAxis: {
			type: 'datetime',
				tickPixelInterval: 200,
				maxZoom: 20 * 400
		 },
		 yAxis: {
			minPadding: 0.2,
				maxPadding: 0.2,
				title: {
					text: 'Trafico Kbps',
					margin: 10
				}
		 },
            series: [{
                name: 'TX',
                data: []
            }, {
                name: 'RX',
                data: []
            }]
	  });
	});
	
	$(".buttonOut").click(function(){		
		$(this).hide();
		$(".hideBox").removeClass("hideElement");   
		$(".graphics").removeClass("maxBox");
		$("#highcharts-0").removeClass("hideElement");
		$("#highcharts-0").addClass("hideElement");
	//	$("img").addClass("imgBig");
		$(".colMax").removeClass("colMax");
		$(".buttonIn").show();

		Highcharts.setOptions({
				global: {
					useUTC: false
				}
			});
	

           chart = new Highcharts.Chart({
			   chart: {
				renderTo: 'container',
				animation: Highcharts.svg,
				type: 'spline',
				
		 },
		 title: {
			text: ''
		 },
		 xAxis: {
			type: 'datetime',
				tickPixelInterval: 200,
				maxZoom: 20 * 400
		 },
		 yAxis: {
			minPadding: 0.2,
				maxPadding: 0.2,
				title: {
					text: 'Trafico Kbps',
					margin: 10
				}
		 },
            series: [{
                name: 'TX',
                data: []
            }, {
                name: 'RX',
                data: []
            }]
	  });
	});
	
	
});
</script>



<!-- Dibujamos las graficas-->


<script type="text/javascript" src="highchart/js/highcharts.js"></script>
<script type="text/javascript" src="highchart/js/themes/gray2.js"></script>

</body>
</html>
