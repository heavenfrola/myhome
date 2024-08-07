<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사용자 코드 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	include_once $engine_dir."/_engine/include/img_ftp.lib.php";
	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();
	include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];
	$exec = $_POST['exec'];
    $type = $_POST['type'];
	$code_type = $_POST['code_type'];
	$page_type = $_POST['page_type'];
	$ctype = $_POST['ctype'];
	$orderby = $_POST['orderby'];
	$max_category = numberOnly($_POST['max_category']);
	$min_category = (int) $_POST['min_category'];
	$cate_type = $_POST['cate_type'];
	$auto_scroll = $_POST['auto_scroll'];
	$use_cate_info = ($_POST['use_cate_info'] == 'Y') ? 'Y' : 'N';
	$use_cate_info2 = ($_POST['use_cate_info2'] == 'Y') ? 'Y' : 'N';
	$use_cate_info2_child = ($_POST['use_cate_info2_child'] == 'Y') ? 'Y' : 'N';
	$child_cate_chk = ($_POST['child_cate_chk'] == 'Y') ? 'Y' : 'N';
    $code_comment = trim($_POST['code_comment']);

	checkBlank($_POST['code_type'], "코드 유형을 선택해주세요.");
	checkBlank($_POST['page_type'], "출력 페이지 설정을 선택해주세요.");

    if ($code_type == 'is' && empty($code_comment) == true) {
        msg('코드 설명을 입력해주세요.');
    }

	if(file_exists($root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'])) include_once $root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'];

	if($_POST['user_code']){ // 수정
		$_code_num=$_POST['user_code'];
		unset($_user_code[$_code_num]);
	}else{ // 신규
		$_code_num=$_POST['new_code'];
	}

	$_code_name=userCodeName($_code_num);
	$_code_hangul_name=userCodeName($_code_num, 1);
	$file_src=$root_dir."/_skin/".$_skin_name."/MODULE/".$_code_name.".".$_skin_ext['m'];

	if($exec == "delete"){
		// 백업 파일 삭제
		$_module_name=$_code_name.".".$_skin_ext['m'];
		$_bak_full_dir=$root_dir."/".$dir['upload']."/skin_".$_skin_name."_bak/".$_module_name;
		@unlink($_bak_full_dir);
		@unlink($_bak_full_dir."_tmp");
		// 모듈 파일 삭제
		ftpDeleteFile($root_dir."/_skin/".$_skin_name."/MODULE/", $_module_name);
		// 배너 파일 삭제
		$_banner_src=$root_dir."/_skin/".$_skin_name."/img/banner";
		$_del_ext=array("gif", "jpg", "jpeg", "bmp", "png");
		for($ii=0; $ii<$_user_img_number; $ii++){
			$_banner_name="ucode_".$user_code."_".$ii;
			$_banner_name2="ucode_".$user_code."_".$ii."r";
			foreach($_del_ext as $dk=>$dv){
				if(@is_file($_banner_src."/".$_banner_name.".".$dv)) ftpDeleteFile($_banner_src, $_banner_name.".".$dv);
				if(@is_file($_banner_src."/".$_banner_name2.".".$dv)) ftpDeleteFile($_banner_src, $_banner_name2.".".$dv);
			}
		}
	}elseif($exec == "uimage_delete"){
		$_file=$_POST['ori_img'][$_POST['img_num']];
		$_file2=$_POST['ori_img'][$_POST['img_num']+100];
		$exec="delete";
		$no_reload=1;
		$_POST['folder_dir']=str_replace(basename($_file), "", $_file);
		if(is_file($_file)){
			include $engine_dir."/_manage/design/common_img.exe.php";
		}
		if(is_file($_file2)){
			$_POST['img_num'] += 100;
			include $engine_dir."/_manage/design/common_img.exe.php";
		}
		msg("", "reload", "parent");
	}else{
		$_POST['code_comment']=str_replace("\"", "", $_POST['code_comment']);
		$_POST['code_comment']=str_replace("\'", "", $_POST['code_comment']);
		$_POST['code_comment']=strip_tags($_POST['code_comment']);
		$_user_code[$_code_num]['code_comment']=trim($_POST['code_comment']);
		$_user_code[$_code_num]['code_type']=$_POST['code_type'];
		$_user_code[$_code_num]['page_type']=$_POST['page_type'];
		$_user_code[$_code_num]['pr_ebig']=preg_replace('/[^0-9,]/', '', $_POST['pr_ebig']);
		if($_user_code[$_code_num]['pr_ebig']) {
			$_user_code[$_code_num]['pr_ck_depth'] = ($_POST['pr_ck_depth'] == 'Y') ? 'Y' : 'N';
		}

		if($page_type == "p"){
			$page_list=implode("@", $_POST['page_list']);
		}

		if($code_type == "p" || $code_type == "c" || $code_type == 'd'){
			if($ctype == "2"){
				if($code_type == "p" || $code_type == 'd') $_cate=implode(",", $_POST['ebig']);
			}else if($ctype == "6"){
				if($code_type == "p" || $code_type == 'd') $_cate=implode(",", $_POST['mbig']);
			}elseif($ctype == "1"){
				$_cate=numberOnly($_POST['big']);
				$_cate .= $_POST['mid'] ? ",".numberOnly($_POST['mid']) : "";
				$_cate .= $_POST['small'] ? ",".numberOnly($_POST['small']) : '';
				if($max_category > 3 || $code_type == "p") {
					$_cate .= $_POST['depth4'] ? ','.numberOnly($_POST['depth4']) : '';
				}
			}elseif($ctype == "4"){
				$_cate=numberOnly($_POST['xbig']);
				$_cate .= $_POST['xmid'] ? ",".numberOnly($_POST['xmid']) : "";
				$_cate .= $_POST['xsmall'] ? ",".numberOnly($_POST['xsmall']) : "";
				if($max_category > 3 || $code_type == "p") {
					$_cate .= $_POST['xdepth4'] ? ','.numberOnly($_POST['xdepth4']) : '';
				}
			}elseif($ctype == "5"){
				$_cate=numberOnly($_POST['ybig']);
				$_cate .= $_POST['ymid'] ? ",".numberOnly($_POST['ymid']) : "";
				$_cate .= $_POST['ysmall'] ? ",".numberOnly($_POST['ysmall']) : "";
				if($max_category > 3 || $code_type == "p") {
					$_cate .= $_POST['ydepth4'] ? ','.numberOnly($_POST['ydepth4']) : '';
				}
			}
		}

		if($code_type == "p"){ // 상품목록
			checkBlank($_POST['list_content'], "구문을 입력해주세요.");
			checkBlank($ctype, "카테고리를 입력해주세요.");
			checkBlank($_cate, "출력할 카테고리를 입력해주세요.");

			$_user_code[$_code_num]['ctype']=$ctype;
			$_user_code[$_code_num]['cate']=$_cate;
			$_user_code[$_code_num]['orderby']=$orderby;
			$_user_code[$_code_num]['prd_sort_soldout'] = $_POST['prd_sort_soldout'];
			$_user_code[$_code_num]['prd_hide_soldout'] = $_POST['prd_hide_soldout'];
			$_user_code[$_code_num]['use_cate_info']=$use_cate_info;
			$_user_code[$_code_num]['use_cate_info2']=$use_cate_info2;
			$_user_code[$_code_num]['use_cate_info2_child']=$use_cate_info2_child;

			$_user_code[$_code_num]['product_list_imgw']=$_POST['product_list_imgw'];
			$_user_code[$_code_num]['product_list_imgh']=$_POST['product_list_imgh'];
			$_user_code[$_code_num]['product_list_cols']=$_POST['product_list_cols'];
			$_user_code[$_code_num]['product_list_rows']=$_POST['product_list_rows'];
            $_user_code[$_code_num]['product_list_maxcnt']=$_POST['product_list_maxcnt'];
			$_user_code[$_code_num]['product_list_namecut']=$_POST['product_list_namecut'];
			$_user_code[$_code_num]['product_img_fd']=$_POST['product_img_fd'];
			$_user_code[$_code_num]['over_product_img_fd']=addslashes($_POST['over_product_img_fd']);
			$_user_code[$_code_num]['paging_use']=$_POST['paging_use'];
			$_user_code[$_code_num]['product_disable_tr'] = $_POST['product_disable_tr'];
		}elseif($code_type == "c"){ // 상품분류
			checkBlank($_POST['list_content'], "구문을 입력해주세요.");
			checkBlank($ctype, "카테고리를 입력해주세요.");
			checkBlank($max_category, "최대 출력 단계를 입력해주세요.");
			checkBlank($cate_type, "카테고리 출력 유형을 입력해주세요.");

			$_user_code[$_code_num]['ctype']=$ctype;
			$_user_code[$_code_num]['cate']=$_cate;
			$_user_code[$_code_num]['use_cate_info']=$use_cate_info;
			$_user_code[$_code_num]['use_cate_info2']=$use_cate_info2;
			$_user_code[$_code_num]['use_cate_info2_child']=$use_cate_info2_child;
			$_user_code[$_code_num]['child_cate_chk']=$child_cate_chk;
			$_user_code[$_code_num]['min_category']=$min_category;
			$_user_code[$_code_num]['max_category']=$max_category;
			$_user_code[$_code_num]['cate_type']=$cate_type;
			$_user_code[$_code_num]['cate_joint']=$cate_joint;
		}elseif($code_type == 'b'){ // 게시물
			$board_name = $_POST['board_name'];
			$board_type = $_POST['board_type'];
			$board_is_notice = $_POST['board_is_notice'];

			checkBlank($_POST['list_content'], '구문을 입력해주세요.');
			checkBlank($board_name, '게시물을 입력해주세요.');

			$_user_code[$_code_num]['board_name']=$board_name;
			$_user_code[$_code_num]['board_type']=$board_type;
			$_user_code[$_code_num]['orderby']=$orderby;
			$_user_code[$_code_num]['board_is_notice']=$board_is_notice;
			$_user_code[$_code_num]['board_list_total']=$_POST['board_list_total'];
			$_user_code[$_code_num]['board_list_titlecut']=$_POST['board_list_titlecut'];
			$_user_code[$_code_num]['board_list_contentcut']=$_POST['board_list_contentcut'];
			$_user_code[$_code_num]['board_list_imgw']=$_POST['board_list_imgw'];
			$_user_code[$_code_num]['board_list_imgh']=$_POST['board_list_imgh'];
			$_user_code[$_code_num]['board_cate']=$_POST['board_cate'];
		}elseif($code_type == "i"){ // 이미지
			$_skin_dir=$root_dir."/_skin/".$_skin_name."/img";

			$_folder_dir=$_skin_dir."/banner";
			$_POST['folder_dir']=$_folder_dir;
			if(!is_dir($_folder_dir)){
				ftpMakeDir($_skin_dir, "banner");
			}

			$img_sum=$_POST['img_sum'] ? $_POST['img_sum'] : $_user_img_default_number;

			$_user_code[$_code_num]['image_link']=ucodeImageLink("", "", "", $img_sum);

			$exec="modify";
			$folder="title";
			$no_reload=1;
			include $engine_dir."/_manage/design/common_img.exe.php";

			$flash_name=$_POST['flash_name'];
			if($flash_xml_use == "Y"){

				if(!is_dir($_skin_dir."/flash/xml")){
					ftpMakeDir($_skin_dir."/flash", "xml");
				}
				checkBlank($flash_name, "플래시를 입력해주세요.");
				checkBlank($_POST['flash_scroll_time'], "진행 간격 시간을 입력해주세요.");
				checkBlank($_POST['flash_duration'], "효과 진행 시간 입력해주세요.");

				$xml="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
	<d>
		<r cd=\"00\" msg=\"정상적으로 처리되었습니다.\"/>
		<l id=\"bannerList\" speed=\"".$_POST['flash_scroll_time']."\" effectTime=\"".$_POST['flash_duration']."\">
";
				// 2010-03-05 : 이미지 개수 설정 - Han
				for($ii=0; $ii<$img_sum; $ii++){
					$_file_name=titleIMGName("ucode_".$_code_num."_".$ii, "banner");
					if(!is_file($_skin_dir."/banner/".$_file_name)) continue;
					$xml .= "
			<i id=\"".$ii."\">
				<e id=\"imgPath\"><![CDATA[".$root_url."/_skin/".$_skin_name."/img/banner/".$_file_name."]]></e>
				<e id=\"link\" target=\"".$_POST['image_target'][$ii]."\"><![CDATA[".$_POST['image_link'][$ii]."]]></e>
			</i>
					";
				}
				$xml .= "
		</l>
	</d>";

				$xml=iconv("EUC-KR", "UTF-8", $xml);

				$_file_name=str_replace(".swf", ".xml", $flash_name);
				$_filebakdir=$root_dir."/_data/flash_xml_tmp.xml";
				$of=fopen($_filebakdir, "w");
				$fw=fwrite($of, $xml);
				if(!$fw) msg("계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
				fclose($of);

				$file['name']=$_file_name;
				$file['tmp_name']=$_filebakdir;
				ftpUploadFile($_skin_dir."/flash/xml", $file, "xml");
				unlink($file['tmp_name']);

				$_user_code[$_code_num]['flash_xml_use']=$_POST['flash_xml_use'];
				$_user_code[$_code_num]['flash_name']=$flash_name;
				$_user_code[$_code_num]['flash_scroll_time']=$_POST['flash_scroll_time'];
				$_user_code[$_code_num]['flash_duration']=$_POST['flash_duration'];
			}else{
				if($flash_name){
					$_file_name=str_replace(".swf", ".xml", $flash_name);
					if(is_file($_skin_dir."/flash/xml/".$_file_name)) ftpDeleteFile($_skin_dir."/flash/xml", $_file_name);
				}
				if($msg != "") msg($msg, "reload", "parent");

			}
			$_user_code[$_code_num]['img_sum']=$_POST['img_sum'];
		}elseif($code_type == "n" || $code_type == 'bs'){ // 일반 HTML 구문
			$app_page=urldecode($_POST['app_page']);
			$edt_content=trim($_POST['list_content']);
			$ori_content=getFContent($file_src);
			if($app_page == "common"){
				$ws=@strpos(".".$ori_content, "<!-- wstart:");
				if($ws){
					$ori_content=@substr($ori_content,$ws-1);
					$edt_content=$edt_content ? $edt_content."\n".$ori_content : $ori_content;
				}
			}else{
				$ws=@strpos(".".$ori_content, "<!-- wstart:".$app_page." -->");
				$edt_content=$edt_content ? "<!-- wstart:".$app_page." -->\n".$edt_content."\n<!-- wend:".$app_page." -->" : "";
				if(!$ws){
					$edt_content=$edt_content ? $ori_content."\n".$edt_content."\n" : "";
				}else{
					$edt_content=@preg_replace("/(<!-- wstart:".$app_page." -->)(.*)(<!-- wend:".$app_page." -->)/is", $edt_content, $ori_content);
				}
			}
			$_POST['list_content']=$edt_content;

			if($code_type == 'bs') {
				$board_name = $_POST['board_name'];
				checkBlank($board_name, '게시물을 입력해주세요.');

				$_user_code[$_code_num]['board_name'] = $board_name;
				$_user_code[$_code_num]['search_column'] = numberOnly($_POST['search_column']);
			}
		}elseif($code_type == "d"){
			checkBlank($width, '가로길이를 입력해주세요.');
			checkBlank($height, '세로길이를 입력해주세요.');

			$_user_code[$_code_num]['ctype']=$ctype;
			$_user_code[$_code_num]['cate']=$_cate;
			$_user_code[$_code_num]['orderby']=$orderby;
			$_user_code[$_code_num]['use_cate_info']=$use_cate_info;
			$_user_code[$_code_num]['use_cate_info2']=$use_cate_info2;
			$_user_code[$_code_num]['use_cate_info2_child']=$use_cate_info2_child;
			$_user_code[$_code_num]['width']=$_POST['width'];
			$_user_code[$_code_num]['width']=$_POST['width'];
			$_user_code[$_code_num]['height']=$_POST['height'];
			$_user_code[$_code_num]['htype']=$_POST['htype'];
		} elseif($code_type == 'instagram') {
			$_user_code[$_code_num]['instagram_cnt'] = numberOnly($_POST['instagram_cnt']);
		} elseif ($code_type == 'is') {
			$_user_code[$_code_num]['use_yn'] = ($_POST['use_yn'] == 'Y') ? 'Y' : 'N';
			$_user_code[$_code_num]['is_datetype'] = ($_POST['is_datetype'] == 'Y') ? 'Y' : 'N';
			if ($_POST['start_date_day']) $_user_code[$_code_num]['start_date'] = strtotime($_POST['start_date_day'].' '.$_POST['start_date_h'].':'.$_POST['start_date_m'].':00');
			if ($_POST['finish_date_day']) $_user_code[$_code_num]['finish_date'] = strtotime($_POST['finish_date_day'].' '.$_POST['finish_date_h'].':'.$_POST['finish_date_m'].':59');

			if ($_POST['is_datetype'] != 'Y') {
				checkBlank($_POST['start_date_day'], '시작일을 입력해주세요.');
				checkBlank($_POST['finish_date_day'], '종료일을 입력해주세요.');
			}
		}

		if($auto_scroll == "Y"){
			$style_filter = $_POST['style_filter'];
			$scroll_box_w = $_POST['scroll_box_w'];
			$scroll_box_h = $_POST['scroll_box_h'];
			checkBlank($style_filter, "효과를 입력해주세요.");
			checkBlank($scroll_box_w, "박스 가로 사이즈를 입력해주세요.");
			checkBlank($scroll_box_h, "박스 세로 사이즈를 입력해주세요.");

			if($_POST['pause_type'] > 1) {
				$_POST['pause_type'] = $_POST['pause_type2'];
			}

			$_user_code[$_code_num]['auto_scroll']=$_POST['auto_scroll'];
			$_user_code[$_code_num]['style_filter']=$_POST['style_filter'];
			$_user_code[$_code_num]['scroll_box_w']=$_POST['scroll_box_w'];
			$_user_code[$_code_num]['scroll_box_h']=$_POST['scroll_box_h'];
			$_user_code[$_code_num]['scroll_direction']=$_POST['scroll_direction'];
			$_user_code[$_code_num]['scroll_speed']=$_POST['scroll_speed'];
			$_user_code[$_code_num]['scroll_time']=$_POST['scroll_time'];
			$_user_code[$_code_num]['board_line']=$_POST['board_line'];
			$_user_code[$_code_num]['duration']=$_POST['duration'];
			$_user_code[$_code_num]['pause_type'] = numberOnly($_POST['pause_type']);
			$_user_code[$_code_num]['pause_time'] = numberOnly($_POST['pause_time']);
			$_user_code[$_code_num]['scauto_start'] = addslashes($_POST['scauto_start']);
			$_POST['scroll_content']=stripslashes($_POST['scroll_content']);
			$_user_code[$_code_num]['scroll_content']=$_POST['scroll_content'];

			if($style_filter == 'rollv2') {
				$exec="modify";
				$edt_mode="module";
				$no_reload="1";
				$edt_content=$_POST['list_content'];
				include_once $engine_dir."/_manage/design/editor.exe.php";
			}
		}elseif($code_type != "i"){
			$exec="modify";
			$edt_mode="module";
			$no_reload="1";
			$edt_content=$_POST['list_content'];
			include_once $engine_dir."/_manage/design/editor.exe.php";
		}

		if($page_type == "p") $_user_code[$_code_num]['page_list']="@".$page_list."@";
		$msg=$_POST['user_code'] ? "{{\$".userCodeName($_code_num, 1)."}} 코드가 수정되었습니다" : "{{\$".userCodeName($_code_num, 1)."}} 코드가 생성되었습니다";

		if ($code_type == 'is') {
			include 'editor_group_banner.exe.php';
		}
	}

	ksort($_user_code);

	$file_content="<?PHP\n// 사용자 코드 설정파일 : ".date("Y-m-d H:i", $now)." 변경됨 - ".$admin[admin_id]."\n\n";
	if(is_array($_user_code)){
		foreach($_user_code as $key=>$val){
			foreach($_user_code[$key] as $key2=>$val2){
				if(!$val2) continue;
				$val2=stripslashes($val2);
				$val2=addslashes($val2);
				$val2=str_replace("\$", "\\$", $val2);
				$file_content .= "\$_user_code[$key]['$key2']=\"".$val2."\";\n";
			}
			$file_content .= "\n";
		}
	}
	$file_content .= "?>";

	$_filedir=$root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'];

	$_filebakdir=$root_dir."/_data/user_code_tmp.".$_skin_ext['g'];
	$of=fopen($_filebakdir, "w");
	$fw=fwrite($of, $file_content);
	if(!$fw) msg("계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	fclose($of);

	$file = array(
		'name' => 'user_code.'.$_skin_ext['g'],
		'tmp_name' => $_filebakdir
	);
	ftpUploadFile($root_dir."/_skin/".$_skin_name, $file, $_skin_ext['g']);
	unlink($file['tmp_name']);

	javac("
		if(typeof parent.opener.opener_group_banner != 'undefined') {
			parent.opener.location.reload();
		}
	");
	msg('', "./pop.php?body=design@editor_user.frm&type={$type}&user_code={$_code_num}", "parent");

?>