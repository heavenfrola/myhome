<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name][board_name]=$config[title];
	$_replace_code[$_file_name][board_skin_url]=$skin_url;
	$_replace_code[$_file_name][board_list_url]="<a href=\"/board/?page=$page&db=$db\">";
	$_replace_code[$_file_name][board_write_url]=$link[write];

	// 댓글
	if($mari_mode == "view@list") $ajax_comment = 'Y';
	$_replace_code[$_file_name][board_cm_writer]=$cdata[name];
	$_replace_code[$_file_name][board_cm_reg_date]=$cdata[reg_date];
	$_replace_code[$_file_name][board_cm_content]=$cdata[content];
	$_replace_code[$_file_name][board_cwrite_form_start]="<form name=\"\" method=\"post\" action=\"./\" target=\"hidden$now\" onSubmit=\"return checkMariComment(this)\" class=\"cmtWrite\">
<input type=\"hidden\" name=\"mari_mode\" value=\"write@comment_exec\">
<input type=\"hidden\" name=\"mari_blog\" value=\"$ajax_comment\">
<input type=\"hidden\" name=\"no\" value=\"$_GET[no]\">
$hidden_db";
	$_replace_code[$_file_name][board_cwrite_form_end]="</form>";
	$_replace_code[$_file_name][board_cwrite_member_hids]=$hidden_member[0];
	$_replace_code[$_file_name][board_cwrite_member_hide]=$hidden_member[1];

	if($mari_mode == "view@view"){
		$data=$board_data;

		$_replace_code[$_file_name][board_del_url]=$link[tmp_del];
		$_replace_code[$_file_name][board_mod_url]=$link[tmp_edit];
		$_replace_code[$_file_name][board_reply_url]=$link[tmp_reply];
		$_replace_code[$_file_name][board_title] = nl2br(mb_strimwidth(stripslashes($data['title']), 0, 100));
		$_replace_code[$_file_name][board_content]=$data[content];
		$_replace_code[$_file_name][board_writer]=$data[name];
		$_replace_code[$_file_name][board_reg_date]= $config['date_type_view'] ? parseDateType($config['date_type_view'], $data[reg_date]) : date("Y-m-d H:i:s",$data[reg_date]);
		$_replace_code[$_file_name][board_hit]=$data[hit];
		$_replace_code[$_file_name][board_email]=$data[email];
		$_replace_code[$_file_name][board_homepage]=$data[homepage];
		$_replace_code[$_file_name][board_link1]=$data[link1];
		$w = '';
		$a = '';
		if($cate) {
			$w .= " and `cate` = '$cate'";
			$a .= "&cate=$cate";
		}
		if($search_str) {
			$w .= " and `$search` like '%$search_str%'";
			$a .="&search=$search&search_str=$search_str";
		}
		if($member['level'] != 1) $w .= " and `hidden` = 'N'";
		$next_no = $pdo->row("select `no` from `mari_board` where `no` > '$data[no]' and `db` = '$data[db]' $w order by `no` asc limit 1");
		$_replace_code[$_file_name][board_next_page]= $next_no ? $root_url.'/board/?db='.$data[db].'&no='.$next_no.'&mari_mode='.$mari_mode.$a : '';
		$pre_no = $pdo->row("select `no` from `mari_board` where `no` < '$data[no]' and `db` = '$data[db]' $w order by `no` desc limit 1");
		$_replace_code[$_file_name][board_pre_page]=$pre_no ? $root_url.'/board/?db='.$data[db].'&no='.$pre_no.'&mari_mode='.$mari_mode.$a : '';

		for($ii = 1; $ii <= 4; $ii++) {
			$_replace_code[$_file_name]['board_upfile'.$ii] = $data['file_link'.$ii].$data['ori_upfile'.$ii]."</a>";
			$_replace_code[$_file_name]['board_upfile'.$ii.'_hids'] = $hidden_file[($ii-1)*2];
			$_replace_code[$_file_name]['board_upfile'.$ii.'_hide'] = $hidden_file[(($ii-1)*2)+1];

			if($data['upfile'.$ii]){
				$_replace_code[$_file_name]['board_upfile'.$ii.'_img'] = $data['file_link'.$ii]."<img src=\"".$data['file_url'.$ii]."\" /></a>";
			}

			$_replace_code[$_file_name]['board_dn'.$ii.'_url'] = $data['upfile'.$ii] ? "<a href='$root_url/main/exec.php?exec_file=common/download.php&db=$db&no=$no&idx=$ii' target='hidden{$now}'>" : "<a style='display:none;'>";
		}

		if($config[use_cate]=="Y" && $data[cate]){
			$sql = "select `name` from `$mari_set[mari_cate]` where `no`='$data[cate]'";
			$data[cate_name] = $pdo->row($sql);
			$_replace_code[$_file_name][board_cate_name]=$data[cate_name];
		}

		if($config['tmp_name']) {
			$tmp_name = unserialize($config['tmp_name']);
		}
		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_code[$_file_name]['board_temp'.$i] = $data['temp'.$i];
			$_replace_code[$_file_name]['board_temp_name'.$i] = $tmp_name['temp'.$i];
		}


		// 관련상품
		$_tmp = '';
		$_line = $root_dir.'/board/_skin/'.$config['skin'].'/products_list.php';
		if(file_exists($_line) && file_exists($root_dir.'/board/_skin/'.$config['skin'].'/skin.ini')) {
			$_line = getFContent($_line, 2);
			$_line = contentReset($_line, 'board_index.php');
			$_line = getListFContent($_line, 'board_refPrd_list');

			$skinconfig = parse_ini_file($root_dir.'/board/_skin/'.$config['skin'].'/skin.ini', true);
			$skinconfig = $skinconfig['refPrd'];

			$w = $skinconfig['width'] > 0 ? $skinconfig['width'] : 100;
			$h = $skinconfig['height'] > 0 ? $skinconfig['height'] : 100;
			$strlen = $skinconfig['strlen'];

			$_pno = explode(',', $data['pno']);
			foreach($_pno as $_no) {
				if(!$_no) continue;
				$data = prdOneData($pdo->assoc("select * from $tbl[product] where no='$_no' and stat in (2,3)"), $w, $h, 3);
				if(!$data['no']) continue;
				if($strlen > 0) {
					$data['name'] = cutstr($data['name'], $strlen);
				}
				$_tmp .= lineValues("board_refPrd_list", $_line, $data);
			}
			$_tmp = listContentSetting($_tmp, $_line);
		}
		$_replace_code[$_file_name]['board_refPrd_list'] = $_tmp;
	}
	if($mari_mode == "view@list" || (($config['list_mode'] == '1' || $config['list_mode'] == '3') && $mari_mode == 'view@view')){
		$_replace_code[$_file_name]['board_list_total'] = number_format($NumTotalRec);;
		$_replace_code[$_file_name][board_new_icon]=$new_icon;
		if($config[use_cate]=="Y"){
			$_line = getModuleContent('board_cate2_list.wsm', 'board_skin');
			$_tmp = '';
			$_replace_code[$_file_name][board_cate_list]="<div class=\"cateList\"><ul>";
			$c=0;
			$c_res=$pdo->iterator("select * from `mari_cate` where `db`='$db' order by `sort`");
            foreach ($c_res as $c_row) {
				$c++;

				$c_row['selected'] = ($c_row['no'] == $_GET['cate']) ? 'selected' : '';
				$c_row['link'] = makeQueryString(true, 'page', 'cate').'&cate='.$c_row['no'];
				$_tmp .= lineValues('board_cate2_list', $_line, $c_row);

				if($cate==$c_row[no]) {
					$c_row[name]="<strong>$c_row[name]</strong>";
				}
				$_replace_code[$_file_name][board_cate_list] .= "<li><a href=\"./?db=$db&cate=$c_row[no]\">$c_row[name]</a></li>";
			}
			$_replace_code[$_file_name][board_cate_list] .= "</ul></div>";
			$_replace_code[$_file_name]['board_cate2_list'] = listContentSetting($_tmp, $_line);
			unset($c_row, $c_res, $c, $_tmp, $_line);

			$_replace_code[$_file_name][board_cate_select_list].="<form id=\"selectCateFrm\" method=\"post\" action=\"./\">";
			$_replace_code[$_file_name][board_cate_select_list].="<select name=\"selectCate\" onchange=\"moveCate('$db')\">";
			$c_res=$pdo->iterator("select * from `mari_cate` where `db`='$db' order by `sort`");

			$_replace_code[$_file_name][board_cate_select_list] .= "<option value=''>분류선택</option>";

            foreach ($c_res as $c_row) {
				if($cate==$c_row[no]) {
					$selected = "selected";
				}
				$_replace_code[$_file_name][board_cate_select_list].="<option name=\"$c_row[name]\" value=\"$c_row[no]\" ".checked($cate,$c_row[no],$c_row[no]).">$c_row[name]</option>";
			}
			unset($c_row,$c_res);
			$_replace_code[$_file_name][board_cate_select_list] .= "</select></form>";
		}
		$_replace_code[$_file_name][board_search_form_start]="<form method=\"get\">
<input type=\"hidden\" name=\"cate\" value=\"$cate\">
$hidden_db
";
		$_replace_code[$_file_name][board_search_form_end]="</form>";
		$_replace_code[$_file_name][board_search_radio]="<label><input type=\"radio\" name=\"search\" ".checked($search,"name").checked($search,"")." value=\"name\"> 작성자</label>
		<label><input type=\"radio\" name=\"search\" ".checked($search,"title")." value=\"title\"> 제목</label>
		<label><input type=\"radio\" name=\"search\" ".checked($search,"content")." value=\"content\"> 내용</label>
";
		$_replace_code[$_file_name][board_search_txt]=$old_search_str;

		$_replace_code[$_file_name][board_search_design] = getModuleContent("board_search_design");

		$_tmp="";
		$_line=getModuleContent("board_search_design");

		$selected = array();

		$selected[name_check] = checked($search,"name").checked($search,"");
		$selected[title_check] = checked($search,"title");
		$selected[content_check] = checked($search,"content");
		$_tmp .= lineValues("board_search_design", $_line, $selected);

		$_tmp=listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name][board_search_design]=$_tmp;

		$_replace_code[$_file_name][board_search_name_check] = checked($search,"name",'name').checked($search,"");
		$_replace_code[$_file_name][board_search_title_check] = checked($search,"title",'title');
		$_replace_code[$_file_name][board_search_content_check] = checked($search,"content",'content');

		if($config['tmp_name'] != '') {
			$tmp_name = unserialize($config['tmp_name']);
		}
		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_code[$_file_name]['board_temp'.$i]=stripslashes($data['temp'.$i]);
			$_replace_code[$_file_name]['board_temp_name'.$i]=stripslashes($tmp_name['temp'.$i]);
		}
	}
	if($mari_mode == "write@del" || $mari_mode == "write@write@edit" || $mari_mode == "view@view"){

		$_target=($mari_mode == "write@del") ? " target=\"hidden$now\"" : "";
		$_exec=($mari_mode == "write@del") ? "_exec" : "";

		$_replace_code[$_file_name][board_pwd_form_start]="<form method=\"post\" action=\"./\" $_target onSubmit=\"return checkPassword(this)\" style=\"margin:0px\">
<input type=\"hidden\" name=\"mari_mode\" value=\"".$mari_mode.$_exec."\">
<input type=\"hidden\" name=\"no\" value=\"$no\">
<input type=\"hidden\" name=\"listURL\" value=\"$listURL\">
$hidden_db
";
		$_replace_code[$_file_name][board_pwd_form_end]="</form>";

	}
	if(preg_match("/^write@write/", $mari_mode)){
		if(!$config['use_editor']) $config['use_editor'] = 3;

		$_replace_code[$_file_name][board_write_form_start]="
<form id=\"wrtFrm\" name=\"wrtFrm\" method=\"post\" action=\"./\" target=\"hidden$now\" onSubmit=\"return checkMariWrite(this)\" style=\"margin:0px\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"mari_mode\" value=\"write@write_exec@$exec\">
<input type=\"hidden\" name=\"no\" value=\"$no\">
<input type=\"hidden\" name=\"listURL\" value=\"$listURL\">
<input type=\"hidden\" name=\"use_cate\" value=\"$use_cate\">
<input type=\"hidden\" name=\"tmp_no\" value=\"$now\"><!--에디터1-->
<input type=\"hidden\" name=\"content\" value=\"\"><!--에디터2-->
<input type=\"hidden\" name=\"html\" value=\"$config[use_editor]\"><!--에디터3-->
<input type=\"hidden\" name=\"pno\" value=\"$data[pno]\">
$hidden_db
";
		$_replace_code[$_file_name][board_write_form_end]="</form>";

		if($config['use_editor'] == 3) {
			seVerchk();
			$_replace_code[$_file_name][board_write_form_end] .= "
			<script type=\"text/javascript\" src=\"".$engine_url."/_engine/R2Na/R2Na.js\"></script>
			<script type=\"text/javascript\" src=\"".$engine_url."/_engine/smartEditor/js/HuskyEZCreator.js\"></script>
			<script type='text/javascript'>
				var editor_code=\"".$neko_id."\";
				var editor_gr=\"board_".$db."\";
				var editor = new R2Na(\"content2\", \"\", \"\");
				editor.initNeko('".$neko_id."', 'board_".$db."', \"img\");
			</script>
			";
		}

		if($db && $cfg['usecap_'.$db]=="Y" && $cfg['captcha_key']) {
			$_replace_code[$_file_name][board_write_form_end] .= "
			<script type='text/javascript'>
				if($('#grecaptcha_element').size()==1) {
					var onloadCallback = function() {
						grecaptcha.render('grecaptcha_element', {
							'sitekey' : '$cfg[captcha_key]'
						});
					};
				}
			</script>
			<script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>";
		}

		$_replace_code[$_file_name][board_writer_hids]=$hidden_member[0];
		$_replace_code[$_file_name][board_writer_hide]=$hidden_member[1].$data[name];
		$_replace_code[$_file_name][board_pwd_hids]=$hidden_member[2];
		$_replace_code[$_file_name][board_pwd_hide]=$hidden_member[3];
		$_replace_code[$_file_name][board_upfile_hids]=$hidden_member[6];
		$_replace_code[$_file_name][board_upfile_hide]=$hidden_member[7];
		$_replace_code[$_file_name][board_cate_str]=$cate_str;
		$_replace_code[$_file_name][board_notice_hids]=$hidden_notice1;
		$_replace_code[$_file_name][board_notice_hide]=$hidden_notice2;
		$_replace_code[$_file_name][board_secret_hids]=$hidden_secret1;
		$_replace_code[$_file_name][board_secret_hide]=$hidden_secret2;
		$_replace_code[$_file_name][board_img_hids]=$hidden_member[6];
		$_replace_code[$_file_name][board_img_hide]=$hidden_member[7];
		$_replace_code[$_file_name][board_writer]=$data[name];
		$_replace_code[$_file_name][board_pwd]=$pwd;
		$_replace_code[$_file_name][board_email]=$data[email];
		$_replace_code[$_file_name][board_homepage]=$data[homepage];
		$_replace_code[$_file_name][board_link1]=$data[link1];
		$_replace_code[$_file_name][board_title]=$data[title];
		for($ii = 1; $ii <= 4; $ii++) {
			$_replace_code[$_file_name]['board_upfile'.$ii]=$data['upfile'.$ii];
		}
		$_replace_code[$_file_name][board_content]=$data[content];
		$_replace_code[$_file_name][board_temp01]=$data[temp1];
		if($config[tmp_name] != "") {
			$tmp_name = unserialize($config[tmp_name]);
		}
		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_code[$_file_name]['board_temp'.$i]=$data['temp'.$i];
			$_replace_code[$_file_name]['board_temp_name'.$i]=$tmp_name['temp'.$i];
		}
		$_replace_code[$_file_name][board_notice]=" ".checked($data[notice],"Y");
		$_replace_code[$_file_name][board_secret]=" ".checked($data[secret],"Y");
		$_replace_code[$_file_name][board_imgfr_url]="./?mari_mode=write@file_frm_exec&db=$db&no=$no&tmp_no=$now";

		// 제목 강제지정
		$_tmp = '';
		if($member['level'] > 1 && $config['use_fsubject'] == 'Y' && trim($config['fsubject'])) {
			$_tmp = getForceSubject('board', $data['title']);
		}
		if(!$_tmp) {
			$_tmp = getModuleContent('board_title_sel');
			$_tmp = lineValues('board_title_sel', $_tmp, $data);
		}
		$_replace_code[$_file_name]['board_title_sel'] = $_tmp;
		$_replace_code[$_file_name]['board_products_list'] = "<div id='board_product_list_div'>$_tmp</div><script type='text/javascript'>$(window).ready(function(){refProductSelectOK();});</script>";

		//캡차
		if(!preg_match("/@edit$/", $mari_mode) && $member['level']!=1){
			if($db && $cfg['captcha_key']) {
				$_captcha_use = "<div style='display: inline-block' id='grecaptcha_element'></div>";
				if($member['no']) {
					if($cfg['usecap_'.$db]=="Y" && $cfg['usecap_member_'.$db]=="Y") {
						$_replace_code[$_file_name]['board_captcha_use'] = $_captcha_use;
					}
				}else {
					if($cfg['usecap_'.$db]=="Y" && $cfg['usecap_nonmember_'.$db]=="Y") {
						$_replace_code[$_file_name]['board_captcha_use'] = $_captcha_use;
					}
				}
			}
		}
	}
	if($mari_mode == "write@comment_del"){

		$_replace_code[$_file_name][board_cpwd_form_start]="<form method=\"post\" action=\"./\" target=\"hidden$now\" onSubmit=\"return checkPassword(this)\" style=\"margin:0px\">
<input type=\"hidden\" name=\"mari_mode\" value=\"".$mari_mode."_exec\">
<input type=\"hidden\" name=\"no\" value=\"$no\">
<input type=\"hidden\" name=\"listURL\" value=\"$listURL\">
$hidden_db
";
		$_replace_code[$_file_name][board_cpwd_form_end]="</form>";

	}
	if($mari_mode == "write@file_frm_exec"){

		$_replace_code[$_file_name][board_imgfile_form_start]="<form name=\"mfup_frm\" method=\"post\" action=\"".$_SERVER[PHP_SELF]."?body=config@product_common.exe\" target=\"hidden$now\" style=\"margin:0px\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"mari_mode\" value=\"write@file_exec\">
<input type=\"hidden\" name=\"del_file\" value=\"\">
<input type=\"hidden\" name=\"db\" value=\"$db\">
<input type=\"hidden\" name=\"no\" value=\"$no\">
<input type=\"hidden\" name=\"tmp_no\" value=\"$tmp_no\">
";
		$_replace_code[$_file_name][board_imgfile_form_end]="</form>";
		$_replace_code[$_file_name][board_imgfile_up_url]="javascript:document.mfup_frm.submit();";

	}

?>