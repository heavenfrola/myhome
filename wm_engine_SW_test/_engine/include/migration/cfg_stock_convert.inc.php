<?php

/**
 * 재고사용중이 아닌 상품이 있을 경우 재고 생성
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

$res = $pdo->iterator("select no, name from {$tbl['product']} where stat in (2, 3, 4) and ea_type=2 and (prd_type=1 or prd_type='') order by no desc");
foreach ($res as $data) {
    echo $data['name'].'<br>';

    $opt_data = getOptionComplex($data['no']);

    foreach ($opt_data as $key => $val) {
        $key = makeComplexKey($key);
        createComplex(
            $data['no'],
            $key,
            null,
            0,
            'converted'
            , 'N'
        );
    }
    $pdo->query("update {$tbl['product']} set ea_type=1 where no='{$data['no']}'");
}

function getOptionComplex($pno) {
    global $tbl, $pdo;

	$opt_data = array();
	$set_name = array();
	$ores = $pdo->iterator("
        select no, name
            from {$tbl['product_option_set']}
            where pno='$pno' and necessary in ('Y', 'C') and otype!='4B'
            order by sort desc
    ");
    foreach ($ores as $key => $oset) {
		$set_name[] = stripslashes($oset['name']);
		$_temp = $opt_data;
		$res2 = $pdo->iterator("
            select * from {$tbl['product_option_item']}
                where pno='$pno' and opno='{$oset['no']}'
                order by sort asc
        ");
        foreach ($odata as $key => $res2) {
			$iname = stripslashes($odata['iname']);
			if($odata['ori_no']) $odata['no'] = $odata['ori_no'];
			if(count($opt_data) == 0) {
				$_temp[$odata['no']] = $iname;
			} else {
				foreach($opt_data as $key => $val) {
					$_temp[$key.'_'.$odata['no']] = $iname.'<ss>'.$val;
					unset($_temp[$key]);
				}
			}
		}
		$opt_data = $_temp;
	}
	$total_complex = count($opt_data);
	if($total_complex == 0) {
		$set_name[] = '-';
		$opt_data[''] = '옵션없음';
	}
	krsort($set_name);

    return $opt_data;
}