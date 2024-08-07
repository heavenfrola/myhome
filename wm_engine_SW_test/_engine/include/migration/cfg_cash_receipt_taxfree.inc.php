<?php

/**
 *  현금영수증 비과세 사용
 **/

global $scfg;

if ($scfg->comp('use_cash_receipt_taxfree', 'Y') == false) {
	addField($tbl['cash_receipt'], 'taxfree_amt', 'int(10) not null default "0" after amt4');
	addField($tbl['cash_receipt_log'], 'remote_addr', 'varchar(15) not null default "" after `system`');

	$scfg->import(array(
		'use_cash_receipt_taxfree',
		'Y'
	));
}

?>