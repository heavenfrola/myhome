<?php

/**
 *  스마트윙 admin UI 3 / $cfg['pay_type'] 설정을 삭제하고 새로운 설정으로 마이그레이션
 **/
if (isset($cfg['pay_type']) == true && $cfg['pay_type_migration'] != 'Y') {
    $__cfg['pay_type_migration'] = 'Y';
	switch ($cfg['pay_type']) {
		case '1' :
			$__cfg['pay_type_1'] = 'Y';
			$__cfg['pay_type_2'] = 'Y';
			$__cfg['pay_type_4'] = 'Y';
			break;
		case '2' :
			$__cfg['pay_type_1'] = 'Y';
			$__cfg['pay_type_4'] = 'Y';
			break;
		case '3' :
			$__cfg['pay_type_2'] = 'Y';
			$__cfg['pay_type_4'] = 'Y';
			break;
	}
	if (isset($__cfg) == true) $scfg->import($__cfg);
}

?>