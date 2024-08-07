<?PHP

	if($_SESSION['browser_type'] == 'mobile') {
		$i=0;
		$mSql="select `no`, `name` from `{$tbl['category']}` where `ctype` = 6 order by `no` asc ";
		$mRes = $pdo->iterator($mSql);
        foreach ($mRes as $mData) {
			$i++;
			$_replace_code['common_module']["m_cate_name{$i}"]="";
			$_replace_hangul['common_module']["m_cate_name{$i}"]="기획전명{$i}";
			$_code_comment['common_module']["m_cate_name{$i}"]="해당 기획전명{$i}";
		}
	}

?>