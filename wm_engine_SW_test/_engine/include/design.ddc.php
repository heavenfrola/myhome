<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  현재 로그인한 회원의 작성한 qna 개수 출력
	' +----------------------------------------------------------------------------------------------+*/
	function DDC_total_member_qna($param = null){
		global $tbl, $member, $pdo;

		if($member['no']) {
			if($param == '답변완료') $w .= " and answer_date > 0 and answer != ''";
			$val = $pdo->row("select count(*) from $tbl[qna] where member_no='$member[no]' $w");
		}
		$val = number_format($val);

		return $val;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  현재 로그인한 회원의 쿠폰수 출력
	' +----------------------------------------------------------------------------------------------+*/
	function DDC_member_coupon() {
		global $tbl, $member, $pdo;

		if(!$member['no']) return 0;

		$fdate = date("Y-m-d",time());
		$coupon_num = $pdo->row("select count(*) from {$tbl['coupon_download']} where member_no='{$member['no']}' and `member_id` = '{$member['member_id']}' and `use_date` = 0 and (`ufinish_date` >= '$fdate' || ufinish_date = '')");
		return $coupon_num ? $coupon_num : 0;
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  문자열 내의 태그 제거
	' +----------------------------------------------------------------------------------------------+*/
	function DDC_striptags($str) {
		return strip_tags($str);
	}


	/* +----------------------------------------------------------------------------------------------+
	' | 회원 가입시 추가항목 분류별 출력
	' +----------------------------------------------------------------------------------------------+*/
	function DDC_join_addfd_list($cate) {
		$_mbr_add_info = $GLOBALS['_mbr_add_info'];

		if(is_array($_mbr_add_info)){
			$_tmp = '';
			$_line = getModuleContent('join_addfd_list');
			foreach($_mbr_add_info as $key => $val) {
				if($val['cate'] != $cate) continue;

				$_jaddfd['name'] = $val['name'];
				$_jaddfd['value'] = memberAddFrm($key);
				$_jaddfd['add_img'] = $val['upfile1'] ? "<img src=".$root."/".$val['updir']."/".$val['upfile1'].">" : $val['name'];
				$_jaddfd['is_required'] = ($val['ncs'] == 'Y') ? 'required' : '';
				$_tmp .= lineValues('join_addfd_list', $_line, $_jaddfd);
			}

			if($_tmp) {
				$_tmp = listContentSetting($_tmp, $_line);
				$_tmp = str_replace('{{$추가항목분류명}}', $cate, $_tmp);
				return $_tmp;
			}
		}

		return '';
	}

?>