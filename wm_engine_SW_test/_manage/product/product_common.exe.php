<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품공통정보 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	if($admin['partner_no']>0) {
		$updir=$dir['upload']."/partner_common_".$admin['partner_no'];
	}else {
		$updir=$dir['upload']."/".$dir['prd_common'];
	}

	if($_GET['exec'] == 'delete') {
		$img = str_replace('/', '', $_GET['img']);
		if(!$img) {
			msg('삭제할 파일을 입력하세요.');
		}

		deleteAttachFile($updir, $img);

		$ems="파일을 삭제하였습니다";
	}
	elseif($_POST['exec'] == 'upload') {
		$ea = 0;
		$open_dir = opendir($root_dir.'/'.$updir);
		while($cfile = readdir($open_dir)){
			if(is_file($root_dir.'/'.$updir.'/'.$cfile)) $ea++;
		}
		include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
		wingUploadRule($_FILES, 'prdCommon', $ea);


		if(!$_FILES["upfile"]["name"]) {
			msg("업로드할 파일을 입력하세요");
		}
		$up_filename=md5(time()); // 새파일명
		$up_info=uploadFile($_FILES["upfile"],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
		$ems="파일이 업로드 되었습니다";

	}
	elseif($_POST['exec'] == 'add_prd_img') {
		if($_POST['use_opt_addimg'] == 'Y') {
			addField($tbl['product_image'], 'option_item_no', 'int(10) not null default "0"');
			$pdo->query("alter table $tbl[product_image] add index option_item_no(option_item_no)");
		}
		if($_POST['mng_add_prd_img']){
			$_add_content="";
			for($ii=1; $ii<=$_POST['mng_add_prd_img']; $ii++){
				$_name=str_replace('"', "", $_POST["add_prd_img_name".$ii]);
				$_name=str_replace("'", "", $_name);
				$_name=str_replace("^", "", $_name);
				$_name=str_replace(";", "", $_name);
				$_w=numberOnly($_POST["add_prd_img_w".$ii]);
				$_h=numberOnly($_POST["add_prd_img_h".$ii]);
				checkBlank($_name, "추가 ".$ii." 필드명을 입력해주세요.");
				checkBlank($_w, "추가 ".$ii." 가로 사이즈를 입력해주세요.");
				checkBlank($_h, "추가 ".$ii." 세로 사이즈를 입력해주세요.");
				$_add_content .= $_name."^".$_w."^".$_h.";";
			}
			$_POST['mng_add_prd_info']=$_add_content;
		}else{
			$_POST['mng_add_prd_img']="";
			$_POST['mng_add_prd_info']="";
		}

		$no_reload_config=1;
		include_once $engine_dir."/_manage/config/config.exe.php";
		msg("설정되었습니다","reload","parent");

	}
    else if ($_POST['exec'] == 'no_ep') {
        if ($_POST['compare_explain'] == 'Y') {
            addField($tbl['product'], 'no_ep', 'enum("Y","N") not null default "N" after tax_free');
        }
        $scfg->import(array(
            'compare_explain' => ($_POST['compare_explain'] == 'Y') ? 'Y' : ''
        ));
    }
	else {
		$partner_content = "";
		if($admin['partner_no'] > 0) $partner_content = "_".$admin['partner_no'];
		for($ii=3; $ii<=5; $ii++) {
			$code="content".$ii.$partner_content;
			$value=$_POST["content".$ii.$partner_content];
			updateWMCode($code,$value,$ext="");
		}
		$code="ptn_content_use".$partner_content;
		$value=$_POST["ptn_content_use".$partner_content];
		updateWMCode($code,$value,$ext="");
	}

	msg($ems,"reload","parent");

?>