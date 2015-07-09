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
		$API->disconnect();}	
				?>
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
