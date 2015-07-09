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



		$API->write("/interface/ethernet/monitor",false);
		$API->write("=numbers=".$valores,false);  
		$API->write("=once=",true);
		$READ = $API->read(false);
		$statusPorts = $API->parse_response($READ);
		$API->disconnect();}	
				?>
<?php
				if(strcmp($identidadRS,"RB") == 0 ){
					echo "<h3>Access</h3>
						<table class='tablePortsCR'>
					";
					echo '<tr><th>Ports</th>';
					echo '<th>VLAN</th></tr>';
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
