<?PHP

	$no_qcheck = true;
	ini_set('memory_limit', '-1');

	include_once $engine_dir."/_manage/product/product_search.inc.php";
	include $engine_dir."/_manage/product/product_excel_config.php";

	$sql="select p.*, b.no as prv_no,b.arcade,b.floor, b.plocation, b.ptel, b.pcell from `$tbl[product]` p $prd_join left join `$tbl[provider]` b on p.seller_idx=b.no where p.`stat`!='1' and p.`wm_sc`='0' $w order by p.`no` desc";
	$res = $pdo->iterator($sql);

	$idx = $pdo->row(str_replace("select * from","select count(*) from",$sql));

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'name' => 50,
        'hash' => 50,
        'name_referer' => 50,
        'keyword' => 50,
        'updir' => 30,
        'org_upfile' => 50,
        'upfile1' => 50,
        'upfile2' => 50,
        'upfile3' => 50,
        'content_html' => 100,
        'm_content_html' => 100,
        'content_text' => 50,
        'm_content_text' => 50
    );
    $ExcelWriter = setExcelWriter();
    $headerType = array();
	foreach ($_prd_excel_fd_selected as $key => $val){
        $field = $prd_excel_fd[$val];
        $field .= $ExcelWriter->duplicateField($_prd_excel_fd_selected, $val);
        $headerType[$field] = (@strchr($val, "_prc") || $val == "milage") ? 'price' : 'string';
        $headerStyle['widths'][] = (!empty($widths[$val])) ? $widths[$val] : 20;
	}
    $file_name = '상품목록';
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$_prd_stat=array(2=>"정상", 3=>"품절", 4=>"숨김");

    foreach ($res as $data) {
        $org = $data;
		$data['name'] = preg_replace('/<br(\s*\/)?>/', '', $data['name']);
		$data['name'] = preg_replace('/<p(\s*[^>]+)?>(.*)?<\/p>/', '$2', $data['name']);

		$rows = array();
        $original_data = $data;//치환 필요시 활용
		foreach($_prd_excel_fd_selected as $key=>$val){
			if($val == "recom_member" && $_use[recom_member] != "Y") continue;
			if(@strchr($val, "_prc") || $val == "milage") $data[$val]=parsePrice($original_data[$val]);
			if(@strchr($val, "_date")) $data[$val]=date("Y-m-d",$original_data[$val]);
			if($val == "cate"){
                $data[$val] = '';
                foreach ($_cate_colname[1] as $_fd) {
                    if ($org[$_fd] > 0) {
                        if ($data[$val]) $data[$val] .= ' > ';
                        $data[$val] .= getCateName($org[$_fd]);
                    }
                }
			}
			if($val == "ea"){
				if($data['ea_type'] == 1) $data[$val] = $pdo->row("select sum(curr_stock(complex_no)) from erp_complex_option where pno='$data[no]' and del_yn='N'");
				else {
					$data[$val]=($data[ea_type] == 2) ? "무제한" : $original_data[ea];
				}
			}
			if($val == "content_html") {
				$data[$val] = $data['content2'];
			}
			if($val == "m_content_html") {
				$data[$val] = $data['m_content2'];
			}
			if($val == "content_text") $data[$val]=strip_tags($data['content2']);
			if($val == "m_content_text") $data[$val]=strip_tags($data['m_content']);
			if($val == "stat") $data[$val]=$_prd_stat[$original_data[$val]];
			if($val == "big" || $val == "mid" || $val == "small" || $val == 'depth4') $data[$val]=getCateName($original_data[$val]);
			if($val == "no_interest" || $val == "event_sale" || $val == "member_sale" || $val == "free_delivery") $data[$val]=($original_data[$val] == "Y") ? "Y" : "N";
			if(@strchr($val, "hit_")) $data[$val]=($original_data[$val] < 1) ? 0 : $original_data[$val];
			if($val == "optinfo"){
				$_opt_sql = $pdo->iterator("select o.no, i.opno, o.name, group_concat(i.iname) as item from `$tbl[product_option_set]` o inner join `$tbl[product_option_item]` i on i.opno = o.no where o.`stat`='2' and o.`pno`='$data[no]' group by o.no order by o.`sort`");
				$_opt_info="";
                foreach ($_opt_sql as $opt_data) {
					$_opt_info .= "$opt_data[name]:$opt_data[item]|";
				}
				$data[$val]=preg_replace("/\|$/", "", $_opt_info);
			}
			if($val == "opt_info"){
				$_opt_sql = $pdo->iterator("select o.no, i.opno, o.name, group_concat(i.iname) as item from `$tbl[product_option_set]` o inner join `$tbl[product_option_item]` i on i.opno = o.no where o.`stat`='2' and o.`pno`='$data[no]' group by o.no order by o.`sort`");
				$_opt_info="";
                foreach ($_opt_sql as $opt_data) {
					$_opt_info .= "$opt_data[name] : $opt_data[item]<br />";
				}
				$data[$val]=$_opt_info;
			}
			if($val == 'storage_name' && $original_data['storage_no'] > 0) {
				$data['storage'] = getStorage($original_data);
				$data['storage_name'] = $data['storage']['name'];
			}
			if($val == 'storage_loc' && $original_data['storage_no'] > 0) {
				$data['storage'] = getStorage($original_data);
				$data['storage_loc'] = getStorageLocation($data['storage']);
			}
			if(strpos($val, '#') === 0) {
				$fno = numberOnly($val);
				$data[$val] = stripslashes($pdo->row("select value from $tbl[product_field] where pno='$data[no]' and fno='$fno'"));
			}
			$data[idx]=$idx;

            $rows[] = $data[$val];
		}
        $ExcelWriter->writeSheetRow($rows);

		$idx--;
        unset($rows);
	}

    $ExcelWriter->writeFile();
