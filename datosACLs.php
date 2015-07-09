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
		
		//Interfaz VLAN
		$interfazVlan = $API->comm('/interface/vlan/print');	
		
		//Firewall rules
		$firewall = $API->comm("/ip/firewall/filter/print");
		$numFirewall=count($firewall);

		$API->write("/interface/ethernet/monitor",false);
		$API->write("=numbers=".$valores,false);  
		$API->write("=once=",true);
		$READ = $API->read(false);
		$statusPorts = $API->parse_response($READ);
		$API->disconnect();}	
				echo "
				<h3>ACLs</h3>
				<table class='ACLRules'>
				<tr>
					
					<th>Src. VLAN</th>
					<th>Action</th>
					<th>Dst. VLAN</th>
				</tr>";
				
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
				
				echo "</table>";
		
?>
