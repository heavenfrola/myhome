<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  디자인 스킨 파싱 함수 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	function contentReset($content="", $_file_name="", $skin_cm="", $w=""){
		global
            $design, $_skin_ext, $root_dir, $tbl, $pdo,
            $_replace_code,
            $_replace_datavals,
            $_replace_code_cache,
            $_replace_hangul,
            $_auto_replace,
            $_dinamic_define;

		if($content == "") return;

		$codes = array();
		if($_file_name != '' && !preg_match("/^user[0-9]+_list$/", $w)) $codes[] = $_file_name;
		if($skin_cm == '') $codes[] = 'common_module';
		$codes[] = 'common';

		foreach($codes as $block) {
			if(!is_array($_replace_code[$block])) continue;

			foreach($_replace_code[$block] as $key=>$val){
				if(empty($_replace_hangul[$block][$key])) continue;
				$_hangul=$_replace_hangul[$block][$key];
				if(isset($_dinamic_define[$block][$key])) continue;

				if(checkCodeUsed($_hangul) == false) { // 문서에 선언되지 않은 키워드는 통과
					continue;
				}

				//if(strpos("/".$_replace_datavals[$block][$w], ";".$_hangul.":")) continue;
				if(!$val || $val == '0.00'){
					$content=scanSpecialFunc($content, $_hangul);
				}
				if($val !== '') $content = str_replace("{{\$".$_hangul."}}", $val, $content);
			}
		}

		// design.ddc.php
		foreach($codes as $block) {
			if(isset($_dinamic_define[$block]) && is_array($_dinamic_define[$block])) {
				foreach($_dinamic_define[$block] as $key => $val) {
					if($val != 'Y') continue;
					$_hangul = $_replace_hangul[$block][$key];
					$content = fetchDinamicDefindCode($key, $_hangul, $content);
				}
			}
		}

		// 배너
		if(empty($_replace_code['user_banner'])) {
			$_replace_code['user_banner']=array();
			$bn_sql = $pdo->iterator("select no from {$tbl['banner']}");
            foreach ($bn_sql as $bn_arr) {
				$_tmp=disBanner($bn_arr['no'], "", "", "", "", "transparent");
				$_replace_code['user_banner'][$bn_arr['no']]=$_tmp;
			}
		}
		foreach($_replace_code['user_banner'] as $key=>$val){
			if(!$val || $val == '0.00') {
				$content=scanSpecialFunc($content, '사용자배너'.$key);
			}
			$content=@str_replace("{{\$사용자배너".$key."}}", $val, $content);
		}

		return $content;
	}

	function getFContent($file_src, $mode=1){
		global $templete_used_key;

		if(defined('_LOAD_AJAX_MODULE_') == true) {
			$afile = preg_replace('@/(CORE|MODULE)/@', '/AJAX/'._LOAD_AJAX_MODULE_.'/', $file_src);
			$file_src = $afile;
		}

		$file_content="";
		if (is_file($file_src)) {
            $file_content = file_get_contents($file_src);
        }

		if(strpos($file_src, 'member_join_frm.wsr') > 0) {
			$file_content = str_replace('function zipInput', 'function zipInputx', $file_content);
		}

		if($file_content) {
			preg_match_all('/(\{\{\$([^}{]+)\}\})|(\{\{if\(([^}]+)\)\}\})/', $file_content, $matches);
			if(is_array($templete_used_key) == false) $templete_used_key = array();
			$GLOBALS['templete_used_key'] = array_unique(array_merge($templete_used_key, $matches[2], $matches[4]));
		}

		return $file_content;
	}

	// 반복문 관련 파일 분리/병합
	function getListFContent($file_content, $code, $mode=""){
		if(!@preg_match("/_(list|img)(\.wsm)?$/", $code)) return $file_content;
		global $engine_dir;
		if($mode){
			$_content="<!-- 반복문시작 -->\n".$file_content[1]."\n<!-- 반복구문시작 -->\n";
			$_content .= $file_content[2]."\n<!-- 반복구문끝 -->\n";
			$_content .= $file_content[3]."\n<!-- 반복문끝 -->\n";
			if(trim($file_content[5])) {
				$_content .= "\n<!-- 반복구문2시작 -->\n".$file_content[5]."\n<!-- 반복구문2끝 -->\n";
			}
			if(trim($file_content[6])) {
				$_content .= "\n<!-- 반복구문3시작 -->\n".$file_content[6]."\n<!-- 반복구문3끝 -->\n";
			}
			$_content .= "\n<!-- 데이터없음시작 -->\n".$file_content[4]."\n<!-- 데이터없음끝 -->\n";
			//$_content=str_replace(chr(13).chr(13), "\n", $_content);

			$file_content=trim($_content);
		}else{
			$_content=array();
			if(!function_exists("cutFile")) include_once $engine_dir."/_engine/include/ext.lib.php";
			$_content[1]=cutFile($file_content, "<!-- 반복문시작 -->", "<!-- 반복구문시작 -->");
			$_content[1]=rtrim($_content[1]);
			$_content[2]=cutFile($file_content, "<!-- 반복구문시작 -->", "<!-- 반복구문끝 -->");
			$_content[2]=rtrim($_content[2]);
			$_content[5]=cutFile($file_content, "<!-- 반복구문2시작 -->", "<!-- 반복구문2끝 -->");
			$_content[5]=rtrim($_content[5]);
			$_content[6]=cutFile($file_content, "<!-- 반복구문3시작 -->", "<!-- 반복구문3끝 -->");
			$_content[6]=rtrim($_content[6]);
			$_content[3]=cutFile($file_content, "<!-- 반복구문끝 -->", "<!-- 반복문끝 -->");
			$_content[3]=rtrim($_content[3]);
			$_content[4]=cutFile($file_content, "<!-- 데이터없음시작 -->", "<!-- 데이터없음끝 -->");
			$_content[4]=rtrim($_content[4]);

			$file_content=$_content;
		}
		return $file_content;
	}

	function getModuleContent($w, $src="", $_file_name=""){
		global $_skin, $_replace_code, $_skin_ext, $skin_path;

		if(strpos($w, 'board_') === 0 && !$src) {
			include $_skin['dir']."/config.".$_skin_ext['g'];
			$_file_src = $_skin['dir'].'/'.$design['skin']."/MODULE/".$w.".".$_skin_ext['m'];
		} elseif(strpos($w, 'board_') === 0 && $src == 'board_skin') {
			$_file_src = $skin_path.'/'.$w;;
		} else {
			$_file_src=($src) ? $w : $_skin['folder']."/MODULE/".$w.".".$_skin_ext['m'];
		}
		$content=getFContent($_file_src, 2);
		$content=contentReset($content, $_file_name, "", $w);
		$content=getListFContent($content, $w);

		if(isset($_GET['single_module']) == true  && $w != 'product_colorchip_list') {
			if($_GET['single_module'] && $_GET['full_reload'] != 'true' && $_GET['single_module'] != 'view@list' && is_array($content)) {
				$content[1] = $content[3] = $content[4] = '';
			}
		}

		return $content;
	}

	function checkCodeUsed($code) {
		if(is_array($GLOBALS['templete_used_key']) == false) return true;
		$result = in_array($code, $GLOBALS['templete_used_key']);
		return $result;
	}

	function lineValues($w, $content, $data, $_file_name="", $_text_edit=""){
		global $_replace_datavals;
		if(!$_file_name) $_file_name=$GLOBALS['_file_name'];
		if(@is_array($content)){
			$content=$content[2];
		}

		// 문서내에 선언된 키워드만 구하기
		preg_match_all('/(\{\{\$([^}{%]+)(%[0-9])?\}\})|(\{\{if\(([^}]+)\)\}\})/', $content, $matches);
		if(!$GLOBALS['templete_used_key']) $GLOBALS['templete_used_key'] = array();
		$matches = array_unique(array_merge($GLOBALS['templete_used_key'], $matches[2], $matches[4]));

		$values=explode(";", $_replace_datavals[$_file_name][$w]);
		foreach($values as $key=>$val){
			if($val == "") continue;
			list($hangul, $fd)=explode(":", $val);

			if(in_array($hangul, $matches) == false) {
				continue;
			}

			if(empty($_scan_cache_vals[$w][$hangul])){
				$_scan_tmp=scanSpecialFunc($content, $hangul, true);
				$_scan_cache_vals[$w][$hangul]=$_scan_tmp ? $_scan_tmp : true;
			}
			if(empty($data[$fd]) || !is_array($data) || !$data[$fd] || $data[$fd] == '0.00'){
				$content=(strlen($_scan_cache_vals[$w][$hangul]) > 1) ? @str_replace($_scan_cache_vals[$w][$hangul], "", $content) : $content;
			}
			if($_text_edit && $data[$fd] && $_file_name){
				$data[$fd]=designTextEdit($data[$fd], $_text_edit, $fd);
			}
			if($hangul == "상품번호"){
				$_cal_types = array('', '%', '+', '-', '*', '/');
				foreach($_cal_types as $cal_type) {
					$_p1="/(\{\{.)(상품번호)(".$cal_type.")([0-9]+)(\}\})/is";
					$match_count=@preg_match($_p1, $content, $mached);
					if($match_count){
						$cal_num=$mached[4];
						if($cal_type) eval('$data[$fd] = $data[$fd] '.$cal_type.' $cal_num;');
						$hangul=$mached[2].$cal_type.$cal_num;
					}
					$content=@str_replace("{{\$".$hangul."}}", $data[$fd], $content);
				}
			} else {
				$content=@str_replace("{{\$".$hangul."}}", $data[$fd], $content);
			}
		}
		return $content;
	}

	function designTextEdit($_arr_value, $_text_edit, $fd=""){
		global $_skin, $_file_name;

		$_value=(!@is_array($_arr_value)) ? array($_arr_value) : $_arr_value;
		foreach($_value as $key=>$value){
			$fd=$key ? $key : @str_replace("_link", "", $fd);
			$_text_edit_fd="text_edit_".$_text_edit."_".@str_replace(".php", "", $_file_name).":".$fd;
			if(empty($_skin[$_text_edit_fd]) == false){
				$_tmp_style = $_skin[$_text_edit_fd];
				$_tmp_link=@preg_match("/(.*<a href=[^>]*>)(.*)(<\/a>)/", $value, $_tmp_link_matches);
				$_tmp_color=@preg_match("/color:([^;]*);/", $_tmp_style, $_tmp_color_matches);
				$_color_txt=$_tmp_color ? " color=\"".$_tmp_color_matches[1]."\"" : "";
				if($_tmp_link){
					$value=$_tmp_link_matches[1]."<font style=\"".$_tmp_style."\"".$_color_txt.">".$_tmp_link_matches[2]."</font>".$_tmp_link_matches[3];
				}else{
					$value= ($value) ? "<font style=\"".$_tmp_style."\"".$_color_txt.">".$value."</font>" : "";
				}
			}
			$_value[$key]=$value;
		}
		$_value=(!@is_array($_arr_value)) ? $value : $_value;
		return $_value;
	}

	function listContentSetting($tmp, $line){
		global $_replace_hangul, $_replace_code;

        if (defined('__MODULE_LOADER__') == true && $_GET['add_mode'] != 'replace_all') {
            unset($line[1], $line[3], $line[4]);
        }

		if(!@is_array($line)) return $tmp;
		$_content="";
		$tmp=trim($tmp);
		if($tmp == "") $_content=$line[4];
		else{
			$_content=$line[1];
			$_content .= $tmp;
			$_content .= $line[3];
		}
		if(isset($_GET['single_module'])) {
			$_content = preg_replace("/\{{2}([^}]+)\}{2}/", "", $_content);
		}

		return $_content;
	}

	function versionChk($version){
		global $cfg;
		if($version != $cfg['design_version']) msg("현재 디자인관리 버전에서는 지원하지 않는 기능입니다.", "back");
	}

	// 스킨유효성 체크
	function skinFormatChk($dir, $full_dir=""){
		global $root_dir, $_skin_ext;
		$_dir=$full_dir ? $dir : $root_dir."/_skin/".$dir;
		if(@file_exists($_dir."/skin_config.".$_skin_ext['g']) && @is_dir($_dir."/COMMON") && @is_dir($_dir."/CORE") && @is_dir($_dir."/MODULE")) return true;
		else return false;
	}

	// 하단에서 변수 삭제
	function designValUnset(){
		unset($GLOBALS['design'], $GLOBALS['_skin'], $GLOBALS['_layout'], $GLOBALS['_replace_code'], $GLOBALS['_replace_code_cache'], $GLOBALS['_replace_user_code'], $GLOBALS['_replace_hangul'], $GLOBALS['_auto_replace'], $GLOBALS['_code_comment'], $GLOBALS['_replace_datavals'], $GLOBALS['_edit_list'], $GLOBALS['_user_code_typec'], $GLOBALS['_scan_cache_vals'], $GLOBALS['_basic_text_style']);
	}

	function oriPageUrl($page, $joint="/"){
		global $_skin_ext;
		if(strpos($page, 'content_content') === 0) {
			$_ori_pg = str_replace('content_content_', 'content/', $page);
		} else {
			$_ori_pg=@preg_replace("/_[a-z0-9_]*\.".$_skin_ext['p']."$/", "", $page).$joint;
			$_ori_pg .= @preg_replace("/^[a-z]*_|\.".$_skin_ext['p']."$/", "", $page).".php";
		}
		return $_ori_pg;
	}

	function userCodeName($num, $hangul=0){
		global $code_type, $_code_type;
		$ctype=$_code_type ? $_code_type : $code_type;
		$txt1="리스트";
		$txt2="_list";
		if($ctype == "n" || $ctype == 'bs'){
			$txt1="HTML";
			$txt2="_html";
		}elseif($ctype == "i"){
			$txt1="이미지";
			$txt2="_img";
		}elseif($ctype == "d"){
			$txt1="프리뷰";
			$txt2="_prv";
		}
		if($hangul){
			$code="사용자".$txt1.$num;
		}else{
			$code="user".$num.$txt2;
		}
		return $code;
	}

	function editSkinName(){
		global $design, $root_dir, $_skin_ext;
		$_skin_name=($design['edit_skin']) ? $design['edit_skin'] : $design['skin'];
		// 스킨 유효성 체크
		$_skin_name=(@file_exists($root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'])) ? $_skin_name : $design['skin'];
		return $_skin_name;
	}

	function editSkinNotice($fcolor="#CC0000"){
		global $design;
		$design['edit_skin'] = editSkinName();
		$w = ($design['edit_skin'] == $design['skin']) ? "서 <u>사용 중인</u>" : " <u>미적용된</u>";
		$type = preg_replace('/[^[:alnum:]]/', '', $_GET['type']);
		$body1=($type == 'mobile') ? 'wmb' : 'design';
		$msg="<font style=\"color:".$fcolor.";\">현재 편집하시는 스킨은 <font color=\"#000000\">[".$design['edit_skin']."]</font> 이며 사이트에".$w." 스킨입니다.</font> <a href=\"./?body=".$body1."@skin&type=".$type."\" target=\"_blank\">[변경하러가기]</a>";
		return $msg;
	}

	function titleIMGName($pg="", $folder="title"){
		global $root_dir, $design, $_skin_name;
		$_title_img_ext=array("gif", "jpg", "jpeg", "bmp", "png", "GIF", "JPG", "JPEG", "BMP", "PNG");
		$_title_img_name="";
		$_skin_name=$_skin_name ? $_skin_name : $design['skin'];
		$src=$root_dir."/_skin/".$_skin_name."/img/".$folder;
		foreach($_title_img_ext as $key=>$val){
			if(@file_exists($src."/".$pg.".".$val)){
				$_title_img_name=$pg.".".$val;
				break;
			}
		}
		return $_title_img_name;
	}

	function scriptOutFormat($content=""){
		$content=@str_replace(chr(13), "", $content);
		$content=@addslashes($content);
		return $content;
	}

	function ucodeImageLink($split="", $code="", $num="", $sum=10){
		if($split && $code) return @explode("<wisa>", $code);
		elseif($num && $code) return preg_replace("/([0-9]*\+)([a-z_]*)(\+)(.*)/", "$".$num, $code);
		$tmp="";
		for($ii=0; $ii<$sum; $ii++){
			$_link=stripslashes($_POST['image_link'][$ii]);
			$_link=@str_replace("+", "", $_link);
			$_link=@str_replace('"', "", $_link);
			$_link=@str_replace("'", "", $_link);
			$tmp .= $ii."+".$_POST['image_target'][$ii]."+".$_link."<wisa>";
		}
		return $tmp;
	}

    /**
     * 스킨 if 구문 처리
     *
     * @param $content 검사할 본문
     * @param $_hangul 검사할 if 코드
     * @param $_cs 리턴 값 종류(true : if 범위 출력, false : if 제거된 본문 출력)
     * @return $_cs 값에 따라 결과 리턴
     **/
	function scanSpecialFunc($content, $_hangul, $_cs = false)
    {
        preg_match_all("/\{\{if\($_hangul\)\}\}(.*?)\{\{(end|endif)\($_hangul\)\}\}/is", $content, $_matched);

        if (count($_matched[0]) == 0) {
			return ($_cs == true) ? '' : $content;
        }

        if ($_cs == true) $content = $_matched[0][0];
        else {
            foreach ($_matched[0] as $val) {
                $content = str_replace($val, '', $content);
            }
        }

		return $content;
	}

	function fetchDinamicDefindCode($key, $hangul, $content) {
		if(strpos($content, "{{\$$hangul") == false) {
			return $content;
		}

		global $engine_dir;
		include_once $engine_dir.'/_engine/include/design.ddc.php';

		$func = 'DDC_'.$key;
		if(function_exists($func)) {
			preg_match_all('/\{\{\$'.$hangul.'(\(([^)]+)\))?\}\}/', $content, $load_dll);

			foreach($load_dll[0] as $key => $code) {
				$val = call_user_func($func, $load_dll[2][$key]);
				$content = str_replace($code, $val, $content);
			}
		}

		return $content;
	}

	function getEditPageName($pkey){
		global $_edit_list, $_skin_ext;
		foreach($_edit_list as $key=>$val){
			$r=$_edit_list[$key][$pkey.".".$_skin_ext['p']];
			if($r) break;
		}
		return $r;
	}

	function getPageAppContent($app_page, $content){
		if($app_page == "common"){
			$ws=@strpos(".".$content, "<!-- wstart:");
			if($ws) $content=@substr($content, 0, $ws-1);
		}else{
			$start = strpos($content, '<!-- wstart:'.$app_page.' -->');
			if($start === false) $content = '';
			else {
				$start += strlen('<!-- wstart:'.$app_page.' -->');
				$finish = strpos($content, '<!-- wend:'.$app_page.' -->')-$start;
				$content = substr($content, $start, $finish);
			}
		}
		return $content;
	}

	function getSkinCfg() {
		global $root_dir, $root_url, $engine_dir, $cfg, $admin, $design, $_skin_ext;

		if($cfg['design_version'] != "V3") {
			return;
		}

		$_skin['dir']=$root_dir."/_skin";

		if($_SESSION['browser_type'] == 'mobile' && $cfg['mobile_use'] == 'Y') include_once $_skin['dir']."/mconfig.".$_skin_ext['g'];
		else include_once $_skin['dir']."/config.".$_skin_ext['g'];

		// 스킨 미리보기일 경우
		if($admin['no'] && empty($_SESSION['skin_preview_name']) == false){
			$skin_preview_name=$_SESSION['skin_preview_name'];
			if(is_dir($_skin['dir']."/".$skin_preview_name)) $design['skin']=$skin_preview_name;
		}

		$_skin['folder']	=$_skin['dir']."/".$design['skin'];
		$_skin['url']=$root_url."/_skin/".$design['skin'];
		include $_skin['folder']."/skin_config.".$_skin_ext['g'];

		return $_skin;
	}

	function scanF($dir, $mode="", $dir2=""){
		global $_efile, $_del_dir, $_allow_ext, $_skin_ext;
		$odir=opendir($dir);
        if (!$odir) return;

		while($arr=readdir($odir)){
			if($arr == "." || $arr == "..") continue;
			$_file=$dir."/".$arr;
			if(is_file($_file)){
				$_allow_ext=$_allow_ext ? $_allow_ext : "|json|jpg|jpeg|gif|bmp|png|webp|js|css|".$_skin_ext['g']."|".$_skin_ext['c']."|".$_skin_ext['p']."|".$_skin_ext['m']."|xml|ttc|TTC|ttf|TTF";
				$_ext=strtolower(getExt($arr));
				if($mode == 1){ // 삭제
					ftpDeleteFile($dir, $arr);
					continue;
				}elseif($mode == 2){ // 복사
					$file['tmp_name']=$_file;
					$file['name']=$arr;
					if($dir2 != "" && strchr($_allow_ext, "|".$_ext."|")) ftpUploadFile($dir2, $file, $_allow_ext, 1);
					continue;
				}
				if(strchr("|".$_skin_ext['g']."|".$_skin_ext['c']."|".$_skin_ext['p']."|".$_skin_ext['m']."|php|", "|".$_ext."|")){
					$_content=getFContent($_file);
					$_r=funcFilter($_content, "", 1);
					if($_r){
						$_efile[] .= $arr;
						continue;
					}
				}
			}elseif(is_dir($_file)){
				if($mode == 1){
					// 삭제
					$_del_dir[]=$_file;
				}elseif($mode == 2){
					// 복사
					ftpMakeDir($dir2, $arr);
				}
				$_dir2=$dir2 ? $dir2."/".$arr : "";
				scanF($_file, $mode, $_dir2);
			}
		}
	}

	function getForceSubject($type, $value = null) {
		global $tbl, $pdo;

		switch($type) {
			case 'review' :
				$fsubject = $pdo->row("select value from {$tbl['default']} where code='review_fsubject'");
			break;
			case 'qna' :
				$fsubject = $pdo->row("select value from {$tbl['default']} where code='qna_fsubject'");
			break;
			case 'board' :
				$fsubject = $GLOBALS['config']['fsubject'];
			break;
		}

		$_tmp = '';
		$fsubject = explode("\n", $fsubject);
		$value = stripslashes($value);
		foreach($fsubject as $val) {
			$val = stripslashes(trim($val));
			if(!$val) continue;

			$sel = ($value == $val) ? 'selected' : '';
			$_tmp .= "<option value=\"".inputText($val)."\" $sel>$val</option>";
		}
		if($_tmp) $_tmp = "<select name='title'><option value=''>:: 제목을 선택해주세요 ::</option>$_tmp</select>";

		return $_tmp;
	}

	function getTsDesign($str, $_file_name) {
		global $engine_dir;

		$data = unserialize(stripslashes($str));
		if(!is_array($data)) $data = array();
		foreach($data as $key => $val) {
			$val = str_replace('＄＄', '$', $val);
			$val = stripslashes(contentReset($val, $_file_name));
			$val = mb_convert_encoding($val, 'utf8', 'euckr');
			$data[$key] = $val;
		}

		return json_encode($data);
	}

    /**
     * 카테고리 사용자리스트 출력 (Recursive)
     **/
    function __cateModuleLoop($ctype, $level, $_where, $parent = 0)
    {
        global $pdo, $tbl, $_cate_colname; // global
        global $key, $_code_name, $_line, $_max_category, $_cate_joint, $_auto_scroll, $_as_unum, $_use_cate_info; // category skin module

        $_parent = '';
        if ($parent > 0) {
            $parent_name = $_cate_colname[1][($level-1)];
            $_parent = " and {$parent_name}='$parent'";
        }
        $res = $pdo->iterator("
            select no, name, level, ctype from {$tbl['category']}
            where ctype='$ctype' and level='$level' $_where $_parent
            order by sort asc
        ");

        $_tmp = '';
        foreach ($res as $user_data) {
            $user_data = userCateData($user_data);
            $_line_value = lineValues($_code_name, $_line, $user_data);

            if ($_tmp && $_cate_joint) $_tmp .= $_cate_joint;
            $_tmp .= ($_auto_scroll == 'Y') ?
                'user_code_'.$key.'['.$_as_unum."] = '".scriptOutFormat($_line_value)."';\n" :
                $_line_value;
            $_as_unum++;

            if ($_use_cate_info != 'Y' && $_max_category > $level && $_ctype != '2') {
                $_tmp .= __cateModuleLoop($ctype, ($level+1), $_where, $user_data['no']);
            }
        }
        return $_tmp;
    }

	$_ts_default1 = "a:6:{s:12:\"digit_prefix\";s:0:\"\";s:12:\"digit_suffix\";s:0:\"\";s:4:\"hour\";s:1:\":\";s:3:\"min\";s:1:\":\";s:3:\"sec\";s:0:\"\";s:7:\"soldout\";s:8:\"판매종료\";}";
	$_ts_default2 = "a:6:{s:12:\"digit_prefix\";s:42:\"<img src=\\\'{{＄＄이미지경로}}/digit/digit_\";s:12:\"digit_suffix\";s:11:\"\\\'.png\\\' />\";s:4:\"hour\";s:3:\"시 \";s:3:\"min\";s:3:\"분 \";s:3:\"sec\";s:2:\"초\";s:7:\"soldout\";s:52:\"<img src=\\\'{{＄＄이미지경로}}/digit/soldout.png\\\' />\";}";


	// 파일확장자
	$_skin_ext['c']="wsn"; // 공통(레이아웃)
	$_skin_ext['p']="wsr"; // 페이지내용
	$_skin_ext['m']="wsm"; // 모듈
	$_skin_ext['g']="cfg"; // 설정파일(수정 시 db.lib.php 의 common_header 함수도 수정필요)

	$_user_code_form=array("p"=>"상품목록", "c"=>"상품분류", "b"=>"게시물", 'bs'=>'게시판검색', "n"=>"일반 HTML", "i"=>"이미지", "is"=>"그룹 배너", "d"=>"상품프리뷰");
	if(isset($cfg['instagram_access_token']) == true) {
		$_user_code_form['instagram'] = '인스타그램';
	}
	$_user_code_typec['p'] = "상품번호:nidx:상품의 박스 번호;상품명:name;상품명(링크포함):name_link:상품링크(A 태그)를 포함한 상품명;상품가로사이즈:w3:상품의 가로 픽셀 사이즈;상품링크:link:해당 상품의 페이지 주소 출력;상품링크(팝업):link_pop:팝업 퀵디테일용 링크주소;상품링크(프리뷰):link_frame:상품프리뷰로 링크합니다.;상품이미지:imgr:상품의 대표 이미지 출력;상품이미지(링크포함):imgr_link:상품링크(A 태그)를 포함한 대표 이미지 출력;상품이미지경로:img:상품 대표 이미지의 경로 출력;상품이미지정보:imgstr:상품의 사이즈 정보 출력 (예 - width=100 height=100);상품아이콘:icons:해당 상품에 등록된 아이콘 출력;상품아이콘(링크포함):icons_link:상품링크(A 태그)를 포함한 상품아이콘;상품가격:sell_prc;상품가격(링크포함):sell_prc_link:상품링크(A 태그)를 포함한 상품가격;회원할인가격:member_prc:회원할인이 적용될 경우 할인가격. 미적용시는 상품가격.;상품적립금:milage;상품소비자가격:normal_prc;이벤트가격:event_prc;할인후실판매가:pay_prc;할인율:total_sale_per1:할인금액/판매가 기준 할인율;판매가인하율:total_sale_per2:소비자가/판매가 기준 인하율;할인및인하율:total_sale_per3:할인금액+(소비자가-판매가)/판매가 기준 할인율;할인적용여부:is_sale:이벤트/회원할인/타임세일이 적용될 경우 Y값 출력;상품코드:code:상품코드;상품요약설명:content1:해당 상품에 등록된 요약 설명;상품질답개수:qna_cnt:해당 상품에 등록된 질문과 답변 개수;상품평개수:rev_cnt:해당 상품에 등록된 상품평 개수;상품평점수(정수):rev_avg_round;상품평평균평점:detail_review_avg:상품평의 평균 평점이 소수점 1자리 까지 표시됩니다.;상품품절:sold_out:해당 상품이 품절일 경우 'out' 문자 출력;위시리스트담김:is_wish:위시리스트에 담긴 상품인 경우 on 문자가 출력됩니다.;위시담기:wish_link:상품을 위시리스트에 담습니다.;장바구니담기:cart_link:옵션 없는 상품을 장바구니에 담습니다.;SNS연동주소(트위터):sns_twitter_url:소셜 네트워크 서비스(SNS) 트위터 연동 주소;SNS연동주소(페이스북):sns_facebook_url:소셜 네트워크 서비스(SNS) 페이스북 연동 주소;참고상품명:name_referer;단독배송여부:dlv_alone:단독배송설정된 상품일 경우 Y를 출력합니다.;타임세일진행여부:ts_use;타임세일타이머:ts_timer;타임세일금액:timesale_prc;기타입력사항1:etc1;기타입력사항2:etc2;기타입력사항3:etc3;사용자1:user1;사용자2:user2;사용자3:user3;참조상품가격:sell_r_prc;참조상품소비자가격:normal_r_prc;참조상품적립금:r_milage;참조회원할인가격:member_r_prc;참조할인후실판매가:pay_r_prc;참조이벤트가격:event_r_prc;참조타임세일금액:timesale_r_prc;위시리스트횟수:hit_wish:상품리스트의 위시횟수;시스템코드:hash;원본상품번호:parent;오늘출발:naver_today_start;오늘출발주문마감시간:naver_today_time;검색키워드:keyword;판매가대체문구사용:sell_prc_consultation_use;";
	if($cfg['xbig_mng'] == 'Y') {
		$_user_code_typec['p'] .= '이분류코드대:xbig:'.$cfg['xbig_name'].'의 대분류 코드번호 출력;';
		$_user_code_typec['p'] .= '이분류코드중:xmid:'.$cfg['xbig_name'].'의 중분류 코드번호 출력;';
		$_user_code_typec['p'] .= '이분류코드소:xsmall:'.$cfg['xbig_name'].'의 소분류 코드번호 출력;';
	}
	if($cfg['ybig_mng'] == 'Y') {
		$_user_code_typec['p'] .= '삼분류코드대:ybig:'.$cfg['ybig_name'].'의 대분류 코드번호 출력;';
		$_user_code_typec['p'] .= '삼분류코드중:ymid:'.$cfg['ybig_name'].'의 중분류 코드번호 출력;';
		$_user_code_typec['p'] .= '삼분류코드소:ysmall:'.$cfg['ybig_name'].'의 소분류 코드번호 출력;';
	}
	if($cfg['use_bs_list_addimg'] == 'Y') {
		$_user_code_typec['p'] .= '상품전체이미지리스트:add_imgs;';
	}
	if($cfg['use_colorchip_cache'] == 'Y') {
		$_user_code_typec['p'] .= '이미지칩리스트:product_colorchip_list:컬러칩 목록을 출력합니다.:editable;';
	}
    $_user_code_typec['p'] .= '상품대이미지:upfile1_tag:상품의 대 이미지 출력;';
    $_user_code_typec['p'] .= '상품대이미지(링크포함):upfile1_link:상품의 대 이미지 출력;';
    $_user_code_typec['p'] .= '상품중이미지:upfile2_tag:상품의 중 이미지 출력;';
    $_user_code_typec['p'] .= '상품중이미지(링크포함):upfile2_link:상품의 중 이미지 출력;';
    $_user_code_typec['p'] .= '상품소이미지:upfile3_tag:상품의 소 이미지 출력;';
    $_user_code_typec['p'] .= '상품소이미지(링크포함):upfile3_link:상품의 소 이미지 출력;';
    if (isset($cfg['mng_add_prd_img']) == false || empty($cfg['mng_add_prd_img']) == true) {
        $cfg['mng_add_prd_img'] = 0;
    }
    $cfg['mng_add_prd_img'] = (int) $cfg['mng_add_prd_img'];
    for ($i = 1; $i <= $cfg['mng_add_prd_img']; $i++) {
        $_user_code_typec['p'] .= '추가이미지'.$i.':upfile'.($i+3).'_tag:상품의 추가이미지 '.$i.' 출력;';
        $_user_code_typec['p'] .= '추가이미지'.$i.'(링크포함):upfile'.($i+3).'_link:상품링크(A태그)를 포함한 상품의 추가이미지'.$i.' 출력;';
    }


	$_user_code_typec['c']="분류명:name;분류명(링크포함):name_link;분류링크:link;분류명(다중링크포함):name_link2;분류링크(다중분류):link2;분류이미지:imgr;분류이미지(링크포함):imgr_link;분류이미지경로:img_src;분류오버이미지경로:imgr_src;분류고유코드:no;분류상품수:total_prd;분류레벨:level;";
	$_user_code_typec['b']="글고유번호:no;글제목:title;글제목(링크포함):title_link;글내용:content;글내용(태그포함):content2;분류명:cate;글링크:link;글링크(레이어):layer_link;첨부이미지1:upfile1;첨부이미지1(링크포함):upfile1_link;첨부이미지2:upfile2;첨부이미지2(링크포함):upfile2_link;이미지개수:img_cnt:첨부파일 및 본문에 사용된 이미지 개수;상품명:prdname;상품명(링크포함):prdname_link;상품이미지:prdimg;상품이미지(링크포함):prdimg_link;상품이미지(파일명):prdimg_path;글작성자:name;글작성일:ymd;글작성일시:symd;새글아이콘:new_i:최신글일 경우 출력 아이콘;링크1:link1;추가항목1:temp1;추가항목2:temp2;추가항목3:temp3;추가항목4:temp4;추가항목5:temp5;추가항목6:temp6;추가항목7:temp7;추가항목8:temp8;추가항목9:temp9;추가항목10:temp10;상품평점수아이콘:rev_pt_icon:상품평점수를 아이콘으로 표기;상품평점수:rev_pt:상품평점수 표기;관련상품링크:prd_link:관련상품링크;조회수:hit;";
	$_user_code_typec['instagram'] = "순서:idx;이미지아이디:id;링크:link;썸네일주소:thumbnail;이미지주소:image;미디어출력:media_tag;";
	$_user_code_typec['review'] = "상품평번호:rev_idx:내림차순 글번호;상품평고유번호:no;상품평제목:title:상품평 조회 페이지 링크를 포함한 제목 출력;상품평제목(링크없음):title_nolink:상품평 조회 페이지 링크를 포함하지 않은 제목 출력;상품평파일아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;새글아이콘:new_i:최신글일 경우 출력 아이콘;상품평작성자:name;상품평등록일:reg_date:상품평 등록일(년/월/일);상품평등록일2:reg_date2:상품평 등록일(월/일);상품평카테고리:cate:카테고리 정보가 있을 경우 출력;상품평점수(텍스트):rev_pt:상품의 평가를 숫자로 출력;상품평점수:star:상품의 평가를 별(최고 5개) 이미지로 출력;상품평첨부파일1:img1:첫번째 첨부 이미지 출력;상품평첨부파일2:img2:두번째 첨부 이미지 출력;이미지개수:img_cnt:첨부파일 및 본문에 사용된 이미지 개수;상품평내용:content:상품평 상세 내용 출력;상품평내용(태그없음):content_plain;상품평요약내용:content_short;상품평보기링크:title_layer_link;상품평수정링크태그:edit_link:상품평 수정이 가능할 경우 수정 링크(A 태그) 출력;상품평수정링크태그(레이어):edit_link_layer;상품평삭제링크태그:del_link:상품평 삭제가 가능할 경우 삭제 링크(A 태그) 출력;상품평추천하기링크:recommend_link;상품평비추천링크:disrecommend_link;상품평추천수:recommend_y;상품평비추천수:recommend_n;상품평댓글수:total_comment_str;_상품평댓글리스트반복문:comment_list;_상품평댓글회원등록폼:comment_form_login;_상품평댓글비회원등록폼:comment_form_logout;첨부파일존재유무:file_exist:첨부 파일이 존재 유무에 따라 yes 또는 no 문자 출력;링크:link2:해당 게시물의 페이지 주소 출력;상품평조회수:hit;상품평베스트:best:베스트 상품평으로 설정된 경우 best 문자 출력;상품평인기:hot:인기 상품평으로 설정된 경우 hot 문자 출력;상품평번호2:rev_idx2:오름차순 글번호;상품명:prd_name;상품이미지:prd_img:상품정보가 존재할 경우 해당 상품의 소 이미지 출력;구성상품명:components_name;구성상품링크:components_link;";
	$_user_code_typec['bs'] = '게시판검색폼시작:bs_form_start;게시판검색폼끝:bs_form_end;게시판명:board_name;';
	$_user_code_typec['is'] = '배너이미지(링크포함):banner_full;배너이미지주소:front_url;롤오버이미지주소:rollover_url;링크주소:link;링크타켓:target;추가텍스트:text;배너아이디:id;';

	// 사용자 이미지 최대 출력 개수
	$_user_img_number=30; // 100 미만으로 잡아야함
	$_user_img_default_number=10;

	$_layout_name=array("{{T}}"=>"상단", "{{L}}"=>"좌측", "{{M}}"=>"중앙", "{{Q}}"=>"우측", "{{B}}"=>"하단", "{{C}}"=>"페이지내용");


	// 사이트 레이아웃별 테이블 구조 정보

	$_layout['fixed']="
{{T}}
{{L}}
{{M}}
{{Q}}
{{B}}
";
	$_layout[1]="
			<tr>
				<td colspan=\"3\" class=\"mall_top_menu\">{{T}}</td>
			</tr>
			<tr>
				<td class=\"mall_left_menu\">{{L}}</td>
				<td class=\"mall_middle_content\">{{M}}</td>
				<td class=\"mall_quick_menu\">{{Q}}</td>
			</tr>
			<tr>
				<td colspan=\"3\" class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[2]="
			<tr>
				<td colspan=\"2\" class=\"mall_top_menu\">{{T}}</td>
				<td class=\"mall_quick_menu\" rowspan=\"3\">{{Q}}</td>
			</tr>
			<tr>
				<td class=\"mall_left_menu\">{{L}}</td>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td colspan=\"2\" class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[3]="
			<tr>
				<td rowspan=\"3\" class=\"mall_left_menu\">{{L}}</td>
				<td class=\"mall_top_menu\">{{T}}</td>
				<td rowspan=\"3\" class=\"mall_quick_menu\">{{Q}}</td>
			</tr>
			<tr>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[4]="
			<tr>
				<td class=\"mall_top_menu\">{{T}}</td>
				<td rowspan=\"3\" class=\"mall_left_menu\">{{L}}</td>
				<td rowspan=\"3\" class=\"mall_quick_menu\">{{Q}}</td>
			</tr>
			<tr>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[5]="
			<tr>
				<td colspan=\"2\" class=\"mall_top_menu\">{{T}}</td>
				<td rowspan=\"3\" class=\"mall_quick_menu\">{{Q}}</td>
			</tr>
			<tr>
				<td rowspan=\"2\" class=\"mall_left_menu\">{{L}}</td>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[6]="
			<tr>
				<td colspan=\"2\" class=\"mall_top_menu\">{{T}}</td>
			</tr>
			<tr>
				<td class=\"mall_left_menu\">{{L}}</td>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td colspan=\"2\" class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[7]="
			<tr>
				<td class=\"mall_top_menu\">{{T}}</td>
			</tr>
			<tr>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[8]="
			<tr>
				<td rowspan=\"3\" class=\"mall_left_menu\">{{L}}</td>
				<td class=\"mall_top_menu\">{{T}}</td>
			</tr>
			<tr>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[9]="
			<tr>
				<td class=\"mall_top_menu\">{{T}}</td>
				<td rowspan=\"3\" class=\"mall_left_menu\">{{L}}</td>
			</tr>
			<tr>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[10]="
			<tr>
				<td colspan=\"2\" class=\"mall_top_menu\">{{T}}</td>
			</tr>
			<tr>
				<td rowspan=\"2\" class=\"mall_left_menu\">{{L}}</td>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[11]="
			<tr>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
";
	$_layout[12]="
			<tr>
				<td class=\"mall_middle_content\">{{M}}</td>
			</tr>
			<tr>
				<td class=\"mall_copyright\">{{B}}</td>
			</tr>
";
	$_layout[13]="{{T}}{{Q}}{{L}}{{M}}{{B}}";



	// 편집 페이지 목록
	if(isset($_GET['type']) && $_GET['type'] == 'mobile') { // 모바일
		$_edit_list['메인페이지']=array(
		"main_index.".$_skin_ext['p']=>"메인페이지",
		"intro_index.".$_skin_ext['p']=>"인트로페이지",
		);
		$_edit_list['회사정보']=array(
		"content_company.".$_skin_ext['p']=>"회사 소개",
		"content_guide.".$_skin_ext['p']=>"이용 안내",
		"content_customer.".$_skin_ext['p']=>"고객센터 메인",
		"content_join_rull.".$_skin_ext['p']=>"회원 가입 약관",
		"content_privacy.".$_skin_ext['p']=>"개인 정보 처리 방침",
		"content_uselaw.".$_skin_ext['p']=>"이용 약관",
		);
		$_edit_list['주문관련']=array(
		"shop_cart.".$_skin_ext['p']=>"장바구니",
		"shop_cart_chgOption.".$_skin_ext['p']=>"장바구니 옵션변경",
		"shop_order.".$_skin_ext['p']=>"주문서",
		"shop_order_finish.".$_skin_ext['p']=>"주문 완료",
		"shop_cart_layer1.".$_skin_ext['p']=>"퀵카트1",
		"shop_cart_layer2.".$_skin_ext['p']=>"퀵카트2",
		);
		if($cfg['use_sbscr']=='Y') {
			$_edit_list['주문관련'] = array_merge($_edit_list['주문관련'], array(
				"shop_cart_sbscr.".$_skin_ext['p']=>"정기배송 장바구니",
			));
		}

		$_edit_list['상품관련']=array(
		"shop_big_section.".$_skin_ext['p']=>"상품 리스트",
		"shop_detail.".$_skin_ext['p']=>"상품 상세 내용",
		"shop_detail_private.".$_skin_ext['p']=>"상품 상세 내용(개인결제)",
		"shop_detail_prdcpn.".$_skin_ext['p']=>$_cpn_stype[5]." 선택 리스트",
		"shop_detail_frame.".$_skin_ext['p']=>"상품 퀵프리뷰",
		"shop_detail_popup.".$_skin_ext['p']=>"상품 퀵프리뷰(팝업)",
		"shop_detail_popup_private.".$_skin_ext['p']=>"상품 퀵프리뷰(팝업-개인결제)",
		"shop_click_prd.".$_skin_ext['p']=>"최근 본 상품 리스트",
		"shop_search_result.".$_skin_ext['p']=>"상품 검색 결과 리스트",
		"shop_zoom.".$_skin_ext['p']=>"상품 확대 이미지",
		"shop_category.".$_skin_ext['p']=>"상품 카테고리",
		"shop_promotion.".$_skin_ext['p']=>"프로모션 기획전",
		"shop_notify_restock.".$_skin_ext['p']=>"재입고 알림 신청",
		);
		if($cfg['use_sbscr']=='Y') {
			$_edit_list['상품관련'] = array_merge($_edit_list['상품관련'], array(
				"shop_detail_subscription.".$_skin_ext['p']=>"정기배송 상품상세 레이어",
			));
		}

		$_edit_list['회원관련']=array(
		"member_edit_step1.".$_skin_ext['p']=>"회원 정보 수정 1단계 비밀번호 확인",
		"member_edit_step3.".$_skin_ext['p']=>"회원 정보 수정 완료",
		"member_find_step1.".$_skin_ext['p']=>"아이디 비밀번호 찾기",
		"member_search_id_pwd.".$_skin_ext['p']=>"아이디 비밀번호 찾기 인증",
		"member_modify_pwd.".$_skin_ext['p']=>"비밀번호 변경",
		"member_join_frm.".$_skin_ext['p']=>"회원 가입/정보 수정",
		"member_join_step1.".$_skin_ext['p']=>"회원 가입 1단계 약관 동의",
		"member_join_step3.".$_skin_ext['p']=>"회원 가입 완료",
		"member_login.".$_skin_ext['p']=>"회원 로그인",
		"member_apijoin.".$_skin_ext['p']=>"SNS 회원 가입 폼",
		"member_apijoin_noterms.".$_skin_ext['p']=>"SNS 회원 통합 폼",
		);
		$_edit_list['마이페이지']=array(
		"mypage_mypage.".$_skin_ext['p']=>"마이페이지 메인",
		"mypage_order_paytype.".$_skin_ext['p']=>"마이페이지 결제방식 변경",
		"mypage_counsel_list.".$_skin_ext['p']=>"고객 상담 리스트",
		"mypage_counsel_step1.".$_skin_ext['p']=>"고객 상담 입력",
		"mypage_counsel_step2.".$_skin_ext['p']=>"고객 상담 완료",
		"mypage_coupon_down_list.".$_skin_ext['p']=>"쿠폰 리스트",
		"mypage_sccoupon.".$_skin_ext['p']=>"소셜쿠폰 사용",
		"mypage_sccoupon_list.".$_skin_ext['p']=>"소셜쿠폰 사용리스트",
		"mypage_emoney.".$_skin_ext['p']=>"예치금 내역",
		"mypage_milage.".$_skin_ext['p']=>"적립금 내역",
		"mypage_order_detail.".$_skin_ext['p']=>"주문서 상세 내용",
		"mypage_order_list.".$_skin_ext['p']=>"주문서 리스트",
		"mypage_wish_list.".$_skin_ext['p']=>"찜한 상품/위시 리스트",
		"mypage_qna_list.".$_skin_ext['p']=>"나의 상품 질문과 답변",
		"mypage_review_list.".$_skin_ext['p']=>"나의 상품 이용 후기",
		"mypage_notify_restock.".$_skin_ext['p']=>"재입고 알림 신청 내역",
		"mypage_attend_list.".$_skin_ext['p']=>"출석체크",
		"mypage_withdraw_step1.".$_skin_ext['p']=>"회원 탈퇴 신청",
		"mypage_withdraw_step2.".$_skin_ext['p']=>"회원 탈퇴 완료",
		);
		if($cfg['use_sbscr'] == 'Y') {
			$_edit_list['마이페이지'] = array_merge($_edit_list['마이페이지'], array(
				"mypage_order_sbscr_list.".$_skin_ext['p']=>"정기배송 주문서 리스트",
				"mypage_order_sbscr_detail.".$_skin_ext['p']=>"정기배송 주문서 상세 내용",
				"mypage_sbscr_dlv_edit.".$_skin_ext['p']=>"정기배송 주문서 주소 변경",
			));
		}

		$_edit_list['게시판정보']=array(
		"shop_product_qna.".$_skin_ext['p']=>"상품별 질문과 답변",
		"shop_product_qna_list.".$_skin_ext['p']=>"모든 상품 질문과 답변",
		"shop_product_qna_mod_frm.".$_skin_ext['p']=>"상품 질문과 답변 수정",
		"shop_product_qna_secret.".$_skin_ext['p']=>"상품 질문과 답변 비밀번호 확인",
		"shop_product_review.".$_skin_ext['p']=>"상품별 이용 후기",
		"shop_product_review_list.".$_skin_ext['p']=>"모든 상품 이용 후기",
		"shop_product_preview_list.".$_skin_ext['p']=>"상품 포토 후기",
		"shop_product_review_mod_frm.".$_skin_ext['p']=>"상품 이용 후기 수정",
		"shop_product_review_detail.".$_skin_ext['p']=>"상품 이용 후기 상세 레이어",
		"shop_product_review_pwd.".$_skin_ext['p']=>"상품 이용 후기 비밀번호 입력 레이어",
		"board_index.".$_skin_ext['p']=>"게시판",
		);

		// 링크로 사용되지 않는 페이지
		$_idvy_not_used_list=array(
		"member_join_frm.".$_skin_ext['p'],
		"shop_product_qna_mod_frm.".$_skin_ext['p'],
		"shop_product_qna_secret.".$_skin_ext['p'],
		"shop_product_qna_secret.".$_skin_ext['p'],
		"shop_product_review_mod_frm.".$_skin_ext['p'],
		"board_index.".$_skin_ext['p'],
		"shop_big_section.".$_skin_ext['p'],
		"shop_detail.".$_skin_ext['p'],
		"shop_zoom.".$_skin_ext['p'],
		"coordi_coordi_view.".$_skin_ext['p'],
		);

		// 공통 이미지 편집가능한 폴더명
		$_skin_common_img=array(
		"logo"=>"로고",
		"title"=>"타이틀",
		"bg"=>"사이트배경",
		"button"=>"버튼",
		"common"=>"공통",
		"main"=>"메인",
		"shop"=>"상품,주문",
		"member"=>"회원",
		"mypage"=>"마이페이지",
		"etc"=>"기타",
		"email"=>"이메일",
		);
	} else {
		$_edit_list['메인페이지']=array(
		"main_index.".$_skin_ext['p']=>"메인페이지",
		"intro_index.".$_skin_ext['p']=>"인트로페이지",
		);
		$_edit_list['회사정보']=array(
		"content_company.".$_skin_ext['p']=>"회사 소개",
		"content_guide.".$_skin_ext['p']=>"이용 안내",
		"content_customer.".$_skin_ext['p']=>"고객센터 메인",
		"content_join_rull.".$_skin_ext['p']=>"회원 가입 약관",
		"content_privacy.".$_skin_ext['p']=>"개인 정보 처리 방침",
		"content_uselaw.".$_skin_ext['p']=>"이용 약관",
		);
		$_edit_list['주문관련']=array(
		"shop_cart.".$_skin_ext['p']=>"장바구니",
		"shop_cart_chgOption.".$_skin_ext['p']=>"장바구니 옵션변경",
		"shop_order.".$_skin_ext['p']=>"주문서",
		"shop_order_finish.".$_skin_ext['p']=>"주문 완료",
		"shop_cart_layer1.".$_skin_ext['p']=>"퀵카트1",
		"shop_cart_layer2.".$_skin_ext['p']=>"퀵카트2",
		);
		if($cfg['use_sbscr']=='Y') {
			$_edit_list['주문관련'] = array_merge($_edit_list['주문관련'], array(
				"shop_cart_sbscr.".$_skin_ext['p']=>"정기배송 장바구니",
			));
		}

		$_edit_list['상품관련']=array(
		"shop_big_section.".$_skin_ext['p']=>"상품 리스트",
		"shop_detail.".$_skin_ext['p']=>"상품 상세 내용",
		"shop_detail_private.".$_skin_ext['p']=>"상품 상세 내용(개인결제)",
		"shop_detail_prdcpn.".$_skin_ext['p']=>"개별상품 쿠폰 선택 리스트",
		"shop_detail_frame.".$_skin_ext['p']=>"상품 퀵프리뷰",
		"shop_detail_popup.".$_skin_ext['p']=>"상품 퀵프리뷰(팝업)",
		"shop_detail_popup_private.".$_skin_ext['p']=>"상품 퀵프리뷰(팝업-개인결제)",
		"shop_click_prd.".$_skin_ext['p']=>"최근 본 상품 리스트",
		"shop_price_search.".$_skin_ext['p']=>"가격별 상품 리스트",
		"shop_search_result.".$_skin_ext['p']=>"상품 검색 결과 리스트",
		"shop_zoom.".$_skin_ext['p']=>"상품 확대 이미지",
		"shop_promotion.".$_skin_ext['p']=>"프로모션 기획전",
		/*"shop_product_request.".$_skin_ext['p']=>"상품 추천 메일 폼",*/
		"shop_notify_restock.".$_skin_ext['p']=>"재입고 알림 신청",
		);
		if($cfg['use_sbscr']=='Y') {
			$_edit_list['상품관련'] = array_merge($_edit_list['상품관련'], array(
				"shop_detail_subscription.".$_skin_ext['p']=>"정기배송 상품상세 레이어",
			));
		}

		$_edit_list['회원관련']=array(
		"member_edit_step1.".$_skin_ext['p']=>"회원 정보 수정 1단계 비밀번호 확인",
		"member_edit_step3.".$_skin_ext['p']=>"회원 정보 수정 완료",
		"member_find_step1.".$_skin_ext['p']=>"아이디 비밀번호 찾기",
		"member_search_id_pwd.".$_skin_ext['p']=>"아이디 비밀번호 찾기 인증",
		"member_modify_pwd.".$_skin_ext['p']=>"비밀번호 변경",
		"member_join_frm.".$_skin_ext['p']=>"회원 가입/정보 수정",
		"member_join_step1.".$_skin_ext['p']=>"회원 가입 1단계 약관 동의",
		"member_join_step3.".$_skin_ext['p']=>"회원 가입 완료",
		"member_login.".$_skin_ext['p']=>"회원 로그인",
		"member_apijoin.".$_skin_ext['p']=>"SNS 회원 가입 폼",
		"member_apijoin_noterms.".$_skin_ext['p']=>"SNS 회원 통합 폼",
		//"member_namecheck.".$_skin_ext['p']=>"실명 인증",
		);

		$_edit_list['마이페이지']=array(
		"mypage_mypage.".$_skin_ext['p']=>"마이페이지 메인",
		"mypage_order_paytype.".$_skin_ext['p']=>"마이페이지 결제방식 변경",
		"mypage_counsel_list.".$_skin_ext['p']=>"고객 상담 리스트",
		"mypage_counsel_step1.".$_skin_ext['p']=>"고객 상담 입력",
		"mypage_counsel_step2.".$_skin_ext['p']=>"고객 상담 완료",
		"mypage_coupon_down_list.".$_skin_ext['p']=>"쿠폰 리스트",
		"mypage_sccoupon.".$_skin_ext['p']=>"소셜쿠폰 사용",
		"mypage_sccoupon_list.".$_skin_ext['p']=>"소셜쿠폰 사용리스트",
		"mypage_emoney.".$_skin_ext['p']=>"예치금 내역",
		"mypage_milage.".$_skin_ext['p']=>"적립금 내역",
		/* 차후처리
		"mypage_msg_list.".$_skin_ext['p']=>"쪽지 리스트",
		"mypage_msg_view.".$_skin_ext['p']=>"쪽지 보기",
		*/
		"mypage_order_detail.".$_skin_ext['p']=>"주문서 상세 내용",
		"mypage_order_list.".$_skin_ext['p']=>"주문서 리스트",
		"mypage_wish_list.".$_skin_ext['p']=>"찜한 상품/위시 리스트",
		"mypage_qna_list.".$_skin_ext['p']=>"나의 상품 질문과 답변",
		"mypage_review_list.".$_skin_ext['p']=>"나의 상품 이용 후기",
		"mypage_notify_restock.".$_skin_ext['p']=>"재입고 알림 신청 내역",
		"mypage_withdraw_step1.".$_skin_ext['p']=>"회원 탈퇴 신청",
		"mypage_withdraw_step2.".$_skin_ext['p']=>"회원 탈퇴 완료",
		);
		if($cfg['use_sbscr'] == 'Y') {
			$_edit_list['마이페이지'] = array_merge($_edit_list['마이페이지'], array(
				"mypage_order_sbscr_list.".$_skin_ext['p']=>"정기배송 주문서 리스트",
				"mypage_order_sbscr_detail.".$_skin_ext['p']=>"정기배송 주문서 상세 내용",
				"mypage_sbscr_dlv_edit.".$_skin_ext['p']=>"정기배송 주문서 주소 변경",
			));
		}

		$_edit_list['게시판정보']=array(
		"shop_product_qna.".$_skin_ext['p']=>"상품별 질문과 답변",
		"shop_product_qna_list.".$_skin_ext['p']=>"모든 상품 질문과 답변",
		"shop_product_qna_mod_frm.".$_skin_ext['p']=>"상품 질문과 답변 수정",
		"shop_product_qna_secret.".$_skin_ext['p']=>"상품 질문과 답변 비밀번호 확인",
		"shop_product_review.".$_skin_ext['p']=>"상품별 이용 후기",
		"shop_product_review_list.".$_skin_ext['p']=>"모든 상품 이용 후기",
		"shop_product_preview_list.".$_skin_ext['p']=>"상품 포토 후기",
		"shop_product_review_mod_frm.".$_skin_ext['p']=>"상품 이용 후기 수정",
		"shop_product_review_detail.".$_skin_ext['p']=>"상품 이용 후기 상세 레이어",
		"shop_product_review_pwd.".$_skin_ext['p']=>"상품 이용 후기 비밀번호 입력 레이어",
		"board_index.".$_skin_ext['p']=>"게시판",
		"./?body=board@board_new_list"=>"게시판 설정",
		"./?body=design@board"=>"게시판 스킨 편집",
		);
		$_edit_list['기타']=array(
		"common_zip_search.".$_skin_ext['p']=>"우편번호 찾기",
		"common_street_zip_search.".$_skin_ext['p']=>"도로명 주소 우편번호 찾기",
		"shop_poll_list.".$_skin_ext['p']=>"설문 조사",
		"mypage_attend_list.".$_skin_ext['p']=>"출석체크",
		"common_product_select.".$_skin_ext['p']=>"게시물 관련상품 찾기",
		"common_product_selected.".$_skin_ext['p']=>"게시물 관련상품 등록",
		"mail_order_product_list.".$_skin_ext['m']=>"메일주문상품목록",
		);

		//[매장지도] 페이지 편집 추가
		$_edit_list['오프라인 매장']=array(
			"store_location.".$_skin_ext['p']=>"오프라인 매장 안내",
			"store_location_info.".$_skin_ext['p']=>"오프라인 매장 상세",
		);


		// 내용외에 상단, 하단, 왼쪽메뉴등 출력안하는 페이지
		$_popup_list=array(
		"common_zip_search.php",
		"common_street_zip_search.php",
		"member_sms_find.php",
		"mypage_msg_list.php",
		"mypage_msg_view.php",
		"shop_zoom.php",
		"intro_index.php",
		"shop_product_request.php",
		);

		// 링크로 사용되지 않는 페이지
		$_idvy_not_used_list=array(
		"member_join_frm.".$_skin_ext['p'],
		"shop_product_qna_mod_frm.".$_skin_ext['p'],
		"shop_product_qna_secret.".$_skin_ext['p'],
		"shop_product_qna_secret.".$_skin_ext['p'],
		"shop_product_review_mod_frm.".$_skin_ext['p'],
		"board_index.".$_skin_ext['p'],
		"shop_big_section.".$_skin_ext['p'],
		"shop_detail.".$_skin_ext['p'],
		"shop_zoom.".$_skin_ext['p'],
		"coordi_coordi_view.".$_skin_ext['p'],
		);

		// 공통 이미지 편집가능한 폴더명 - 사용중인 스킨의 /img 하단
		$_skin_common_img=array(
		"logo"=>"로고",
		"title"=>"타이틀",
		"bg"=>"사이트배경",
		"button"=>"버튼",
		"common"=>"공통",
		"main"=>"메인",
		"shop"=>"상품,주문",
		"member"=>"회원",
		"mypage"=>"마이페이지",
		"flash"=>"플래시",
		"etc"=>"기타",
		"email"=>"이메일",
		);

		// 텍스트 편집 가능한 페이지
		$_basic_text_style=array(
		"상품 관련"=>array("main_index|shop_big_section|shop_cart|mypage_order_detail"=>"상품명:name;상품가격:sell_prc;상품요약설명:content1;", "shop_detail"=>"상품명:detail_prd_name;상품코드:detail_prd_code;판매가격:detail_sell_prc;소비자가격:detail_nml_prc;적립금:detail_prd_milage;이벤트금액:detail_event_prc;상품요약설명:detail_content1;상품상세설명:detail_content2;배송정보:detail_content3;반품취소안내:detail_content4;AS안내:detail_content5;"),
		"게시판 관련"=>array("main_index|board_index|shop_detail|shop_product_qna|shop_product_qna_list|shop_product_review|shop_product_review_list"=>"글제목:title;작성자:name;작성일:reg_date;"),
		//"상품 분류 관련"=>array(),
		);
	}

?>