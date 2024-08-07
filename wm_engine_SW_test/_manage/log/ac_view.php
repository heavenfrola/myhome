<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  고급접속통계 - 에이스카운터
	' +----------------------------------------------------------------------------------------------+*/

	function enc($str,$mode,$seed,$seed_point){
		for($i=0;$i<strlen($seed);$i++){
			$new_seed.=chr(ord(substr($seed,$i,1))+$seed_point+$i);
		}

		if($mode=="e"){
			$str=base64_encode($str);
			$str=substr($str,0,$seed_point).$new_seed.substr($str,$seed_point);
			$str=urlencode($str);
		}

		if($mode=="d"){
			$str=urldecode($str);
			$read_seed=substr($str,$seed_point,strlen($new_seed));

			if($read_seed==$new_seed){
				$str=substr($str,0,$seed_point).substr($str,$seed_point+strlen($new_seed));
			}
			$str=base64_decode($str);
		}

		return $str;
	}

	$login_info=enc("id=".$cfg[ace_counter_id]."&pw=".$cfg[ace_counter_pwd], "e", "makeshop", 4);

	$acSrc="http://wisa.acecounter.com/login.php3?".$login_info;
	$acSrc="http://wisa.acecounter.com/login.php3?id=".$cfg[ace_counter_id]."&pw=".$cfg[ace_counter_pwd];

?>
<div class="box_full">
	<iframe src="<?=$acSrc?>" name="acView" width="990px" height="1500px" id="acView" frameborder="0"></iframe>
</div>