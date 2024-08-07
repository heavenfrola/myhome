<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  인터파크 상품카탈로그
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$cfg['interpartk_zoom'] = 2;
	$_stat = array(
		2 => '01',
		3 => '02',
		4 => '03',
	);

	// 상품 불러오기
	$prd		= $pdo->assoc("select * from $tbl[product] where hash='$pno'");

	if(!$prd['no']) exit('존재하지 않는 상품입니다');
	if($cfg['interpartk_zoom'] == 1 && !$prd['upfile1']) $cfg['interpartk_zoom'] = 2;

	$name		= cutStr(stripslashes($prd['name']), 60, '');
	$stat		= $_stat[$prd['stat']];
	$ea			= $prd['ea_type'] == 3 ? $prd['ea'] : 99999;
	$content	= stripslashes($prd['content2']);
	$zoomImg	= getFileDir('/_data/product').'/'.$prd['updir'].'/'.$prd['upfile'.$cfg['interpartk_zoom']];
	$dlv_prc	= $cfg['delivery_type'] == 3 && $cfg['delivery_free_limit'] > $prd['sell_prc'] ? $cfg['delivery_fee'] : 0;

	// 상품 부가 이미지
	$dimg		= array();
	$imgurl		= getFileDir('/_data/product');
	$imgsql		= $pdo->iterator("select * from $tbl[product_image] where pno='$prd[no]' and filetype=2");
    foreach ($imgsql as $data) {
		$dimg[]	= $imgurl.'/'.$data['updir'].'/'.$data['filename'];
	}
	$dimg		= implode(',', $dimg);

	printAjaxHeader();
	echo "<?xml version=\"1.0\" encoding=\"euc-kr\"?>";

?>
<result>
	<title>Interpark Product API</title>
	<description>상품 등록</description>
	<item>
		<prdStat>01</prdStat>
		<shopNo>0000100000</shopNo>
		<omDispNo><?=$cateCode?></omDispNo>
		<prdNm><![CDATA[<?=$name?>]]></prdNm>
		<hdelvMafcEntrNm><![CDATA[<?=$cfg['company_name']?>]]></hdelvMafcEntrNm>
		<prdOriginTp>국내</prdOriginTp>
		<taxTp>01</taxTp>
		<ordAgeRstrYn>N</ordAgeRstrYn>
		<saleStatTp><?=$stat?></saleStatTp>
		<saleUnitcost><?=$prd['sell_prc']?></saleUnitcost>
		<saleLmtQty><?=$ea?></saleLmtQty>
		<saleStrDts><?=date('Ymd', $prd['reg_date'])?></saleStrDts>
		<saleEndDts>99991231</saleEndDts>
		<proddelvCostUseYn>N</proddelvCostUseYn>
		<prdrtnCostUseYn>N</prdrtnCostUseYn>
		<rtndelvCost></rtndelvCost>
		<rtndelvNo></rtndelvNo>
		<prdBasisExplanEd><![CDATA[<?=$content?>]]></prdBasisExplanEd>
		<zoomImg><?=$zoomImg?></zoomImg>
		<prdPostfix></prdPostfix>
		<prdKeywd></prdKeywd>
		<brandNm></brandNm>
		<entrPoint></entrPoint>
		<minOrdQty><?=$prd['min_ord']?></minOrdQty>
		<perordRstrQty><?=$prd['max_ord']?></perordRstrQty>
		<optPrirTp>01</optPrirTp>
		<selOptName><?=$optionname?></selOptName>
		<prdOption><?=$optionitem?></prdOption>
		<addQtyUseYn></addQtyUseYn>
		<inOpt></inOpt>
		<delvCost><?=$dlv_prc?></delvCost>
		<delvAmtPayTpCom><?=$dlv_type?></delvAmtPayTpCom>
		<delvCostApplyTp>02</delvCostApplyTp>
		<freedelvStdCnt>0</freedelvStdCnt>
		<spcaseEd></spcaseEd>
		<intfreeInstmStrDts></intfreeInstmStrDts>
		<intfreeInstmEndDts></intfreeInstmEndDts>
		<listInstmMonths></listInstmMonths>
		<pointmUseYn></pointmUseYn>
		<ippSubmitYn></ippSubmitYn>
		<originPrdNo></originPrdNo>
		<shopDispInfo></shopDispInfo>
		<detailImg><![CDATA[<?=$dimg?>]]></detailImg>
		<abroadBsYn>N</abroadBsYn>
	</item>
</result>