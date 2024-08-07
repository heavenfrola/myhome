<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문추가항목 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	$exec = $_REQUEST['exec'];
	$name = addslashes(trim($_POST['name']));
	$no = numberOnly($_REQUEST['no']);

	$_infofile=$root_dir."/_config/order.php";

	$_ord_add_info=array();
	if(@file_exists($_infofile)){ // 수정전 파일 백업
		$bak_dir=$dir['upload']."/order_addinfo";
		makeFullDir($bak_dir);
		@copy($_infofile, $root_dir."/".$bak_dir."/order_".date("ymdHis",$now)."_".$admin['no'].".php");
		include_once $_infofile;
	}

	foreach($_ord_add_info as $key=>$val){
		$max=$key+1;
	}

	if(!$no){
		$add_num=(count($_ord_add_info) < 1) ? 0 : $max;
		$no=$add_num+1;
	}

	if($no) $no--;
	$_ord_add_info[$no]['name']="ADD";

	$_file_contents="<?php\n// 파일정보 : ".date("Y-m-d H:i")." - ".$admin['admin_id'];
	foreach($_ord_add_info as $key=>$val){
		if($no == $key){
			if($exec == "delete") continue;

			$_ord_add_info[$key]['name'] = $name;
			$_ord_add_info[$key]['type'] = addslashes($_POST['type']);
			$_ord_add_info[$key]['ncs'] = ($_POST['ncs'] == 'Y') ? 'Y' : 'N';
			$_ord_add_info[$key]['text'] = @explode(",",addslashes($_POST['text']));
			$_ord_add_info[$key]['size'] = numberOnly($_POST['size']);
			$_ord_add_info[$key]['class'] = $_POST['class'];
			$_ord_add_info[$key]['format'] = numberOnly($_POST['format']);
		}
		$_file_contents .= "\n\n\$_ord_add_info[".$key."]['name']=\"".addslashes($_ord_add_info[$key]['name'])."\";";
		$_file_contents .= "\n\$_ord_add_info[".$key."]['type']=\"".$_ord_add_info[$key]['type']."\";";
		$_file_contents .= "\n\$_ord_add_info[".$key."]['ncs']=\"".$_ord_add_info[$key]['ncs']."\";";
		if($_ord_add_info[$key]['type'] == "text"){
			$_file_contents .= "\n\$_ord_add_info[".$key."]['size']=\"".$_ord_add_info[$key]['size']."\";";
			$_file_contents .= "\n\$_ord_add_info[".$key."]['class']=\"".$_ord_add_info[$key]['class']."\";";
		}else if($_ord_add_info[$key]['type'] == "date"){
			$_file_contents .= "\n\$_ord_add_info[".$key."]['format']=\"".$_ord_add_info[$key]['format']."\";";
		}
		else{
			$_file_contents .= "\n\$_ord_add_info[".$key."]['text']=array(\"".@implode('","',$_ord_add_info[$key]['text'])."\");";
		}
	}

	@unlink($_infofile);
	if(!$fp=fopen($_infofile, "w"))	msg("파일을 열지 못하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	fwrite($fp, $_file_contents);
	fclose($fp);
	chmod($_infofile,0777);

	msg("","reload","parent");

?>