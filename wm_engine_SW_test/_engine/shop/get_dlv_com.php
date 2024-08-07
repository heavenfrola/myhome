<?PHP

	## 해당국가에 맞는 배송업체와 주(state)를 구해옴
	include "../_config/set.php";
	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_config/set.country.php";

	$nations = addslashes(trim($_REQUEST['nations']));
	$addressee_addr1 = addslashes(trim($_REQUEST['addressee_addr1']));

	$_tmp = "";
	if($nations){
		$res = $pdo->iterator("select delivery_com from $tbl[os_delivery_country] where country_code='".$nations."'");
		$_tmp = "<option value=\"\">:: ".__lang_order_info_delivery_com__." ::</option>\n";

        foreach ($res as $data) {
			$cdata = $pdo->assoc("select no,name from $tbl[delivery_url] where no='".$data['delivery_com']."'");
			$_tmp .= "<option value='${cdata['no']}'>${cdata['name']}</option>\n";
		}
	}

	if(!$_tmp){
		$_tmp_arr = getOverseaDeliveryComList();
		$_tmp = "<option value=\"\">:: ".__lang_order_info_delivery_com__." ::</option>\n";
		if(count($_tmp_arr['list']) > 0){
			foreach($_tmp_arr['list'] as $k=>$v){
				$_tmp .= "<option value='${v['no']}'>${v['name']}</option>\n";
			}
		}
	}

	$_tmp2="";
	$_tmp3="";
	if(is_array($_nations_country_state_code[$nations]) && count($_nations_country_state_code[$nations]) > 0){
		$_tmp2 = "<select name=\"addressee_addr1\">";
		$_tmp2 .= "<option value=''>".__lang_order_info_country_state__."</option>";
		foreach($_nations_country_state_code[$nations] as $k=>$v){
			$_tmp2 .= "<option value='".$v."' ".($addressee_addr1==$v?'selected':'').">".$v."</option>";
		}
		$_tmp2 .= "</select>";
		$_tmp3 = "S";
	}else{
		$_tmp2 = "<input type=\"text\" name=\"addressee_addr1\" value=\"".$addressee_addr1."\" class=\"input mid\" maxlength=\"50\" placeholder=\"State/Province\">";
		$_tmp3 = "I";
	}

	echo json_encode(array('nations'=>$_tmp,'state'=>$_tmp2,'change'=>$_tmp3));

?>