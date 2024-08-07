<?PHP

	checkBasic();

	if($exec=="sort") {
		$_tmp_sort=explode("@",$new_sort);
		for($ii=0; $ii<count($_tmp_sort); $ii++) {
			$_tmp_tmp_sort=explode("::",$_tmp_sort[$ii]);
			$no=$_tmp_tmp_sort[0]; // 번호
			$sort=$_tmp_tmp_sort[1]; // 순서
			if($no=="dummy" && $ctype=="2") {
				if(!$cno) {
					$cno=$pdo->row("select max(`no`) from `".$tbl['category']."`");
					if(!$cno) $cno=1000;
				}
				$cno++;
				$sql="INSERT INTO `".$tbl['category']."` ( `no` , `name` , `big` , `mid` , `level` , `hidden` , `access_member` , `cols` , `rows` , `template` , `use_top` , `top_prd` , `ctype` , `cut_title` , `prd_type` , `sort` , `code`) ";
				$sql.="VALUES ( '$cno', 'dummy', '$big','$mid','1', 'N', '', '', '', '', '', '', '$ctype', '', '1', '$sort', 'dummy')";
				$pdo->query($sql);
			}
			else {
				$pdo->query("update `$tbl[category]` set `sort`='$sort' where `no`='$no'");
			}
		}

		// 삭제
		$_tmp=explode("::",$del_dummy);
		foreach($_tmp as $key=>$val) {

			if($val) {
				$pdo->query("delete from `".$tbl['category']."` where `no`='$val'");
			}
		}


		msg("","reload","parent.parent");
	}

?>