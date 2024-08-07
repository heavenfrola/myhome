<?PHP

	if(!defined("_lib_inc")) exit;

	$no = numberOnly($_REQUEST['no']);
	$db = addslashes($_REQUEST['db']);
	$pwd = $_POST['pwd'];

	if($exec!="edit") {
		checkWriteLimit("write",1);
	}

	if($exec=="reply" || $exec=="edit") {
		if(!$no) msg(__lang_common_error_required__, "/");

		$data=$pdo->assoc("select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'");
		if(!$data[no]) msg(__lang_board_error_noParent__, "back");

		if($exec=="edit") { // 수정
			$auth=getDataAuth($data,1);
			if($auth==3 && $_SESSION['pwd'.$no] != 'OK') {
				if(isBlank($pwd)) {
					include $skin_path."edit.php";
					return;
				}
				elseif(strcmp(sql_password($pwd),stripslashes($data[pwd]))!=0) {
					msg(__lang_member_error_wrongPwd__,"back");
				}
			}

			$data[or_content]=$data[content];

			foreach($data as $key=>$val) {
				$data[$key]=stripslashes($val);
				$textbox=array('name','pwd','title','homepage','email');
				if(in_array($key,$textbox)) {
					$data[$key]=inputText($data[$key]);
				}
			}

			// 파일
			for($ii=1; $ii<5; $ii++) {
				if($data["upfile".$ii]) {
					$data["upfile".$ii]="기존 이미지 : <a href=\"".getFileDir('board/_data/skin').'/board/'.$data[up_dir].$data["upfile".$ii]."\" target=\"blank\"><b>".$data["ori_upfile".$ii]."</b></a> ";
					$data["upfile".$ii].=" <label><input type=\"checkbox\" name=\"delfile$ii\" value=\"Y\"> 삭제</label>";
				}
			}

			if($auth==1) {
				$hidden_member[2]="<!--";
				$hidden_member[3]="//-->";
			}
			elseif($auth==2) {
				$hidden_member[0]="<!--";
				$hidden_member[1]="//-->";

				$hidden_member[2]="<!--";
				$hidden_member[3]="//-->";
			}
			else {
				$hidden_member[4]="<!--";
				$hidden_member[5]="//-->";
			}
		}else if($exec == "reply"){ // 답글
			if(!(getAuth("reply")>=0 && $config[use_reply]=="Y")) msg(__lang_board_error_cannotReply__, "back");
			$parent=$data;
			$data = array();
			$data[name]=$member[$mari_set[name]];
			$data[email]=$member[email];
			$data[homepage]=$member[add_info0];
			$data[html]=1;
			if($member[level]<10) {
				$hidden_member[0]="<!--";
				$hidden_member[1]="//-->";

				$hidden_member[2]="<!--";
				$hidden_member[3]="//-->";
			}
			$data[title]=$parent[title];
			$data[secret]=$parent[secret];
		}
	}
	else { // 신규글 작성
		// 회원
		$data[name]=$member[$mari_set[name]];
		$data[email]=$member[email];
		$data[homepage]=$member[add_info0];
		$data[html]=1;

		// 비회원
		if($member[level]<10) {
			$hidden_member[0]="<!--";
			$hidden_member[1]="//-->";

			$hidden_member[2]="<!--";
			$hidden_member[3]="//-->";
		}
	}

	if($member[level]>$config[auth_upload]) {
		$hidden_member[6]="<!--";
		$hidden_member[7]="//-->";
	}

	if($member[level]>$mari_set[mng_level]) {
		$hidden_notice1="<!--";
		$hidden_notice2="//-->";
	}

	if($config['auto_secret'] == 'Y' && $member['level'] > 1) {
		$hidden_secret1="<!--";
		$hidden_secret2="//-->";
	}

	if($config[use_cate]=="Y" && $exec!="reply") {
		if($exec=="edit") {
			$cate=$data[cate];
		}
		$cate_str="<select name=\"cate\">\n";
		$cate_str.="\t<option value=\"\">".__lang_board_select_cartegory__."</option>\n";
		$c_res=$pdo->iterator("select * from `mari_cate` where `db`='$db'");
        foreach ($c_res as $c_row) {
			$cate_sel=checked($cate,$c_row[no],1);
			$cate_str.="<option value=\"$c_row[no]\" $cate_sel>$c_row[name]</option>\n";
		}
		$cate_str.="</select>\n";
		$use_cate="Y";
	}
	else {
		$hidden_cate[0]="<!--";
		$hidden_cate[1]="//-->";
		$use_cate="N";
	}

	if($_SESSION['browser_type'] == 'mobile' && $data['use_m_content'] == 'Y' && $data['m_content']) {
		$data['content'] = $data['m_content'];
	}

	$listURL = $_SESSION['bbs_rURL'];
	if(!$listURL) $listURL=$PHP_SELF.$db_que2;

	$neko_id = ($no) ? $db."_".$no : $db."_temp_".$now;

	include $skin_path."write.php";

?>