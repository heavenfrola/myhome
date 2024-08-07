<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Acecounter
	' +----------------------------------------------------------------------------------------------+*/

	if($_SESSION['browser_type'] == 'mobile') {
		if(!$cfg['ace_counter_gcode_m'] && $cfg['ace_counter_gcode']) {
			$cfg['ace_counter_gcode_m'] = $cfg['ace_counter_gcode'];
		}
		if(!$cfg['ace_counter_gcode_m']) return;
		include 'acecounter_mobile.inc.php';
	} else {
		if(!$cfg['ace_counter_gcode']) return;
		include 'acecounter_pc.inc.php';
	}

?>