<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  지번주소 검색결과
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$sido = addslashes(trim($_GET['sido']));
	$gugun = addslashes(trim($_GET['gugun']));
	$search = trim(addslashes($_GET['search']));
	$zip_mode = numberOnly($_GET['zip_mode']);
	$form_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['form_nm']));
	$zip_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['zip_nm']));
	$addr1_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['addr1_nm']));
	$addr2_nm = trim(preg_replace('/[^a-z0-9_]/i', '', $_GET['addr2_nm']));
    $cart_selected = (isset($_GET['cart_selected'])) ? $_GET['cart_selected'] : '';
    $sbscr = (isset($_GET['sbscr'])) ? $_GET['sbscr'] : 'N';

    // 다음 우편번호 클릭 시 배송 가능 지역 체크
    if ($_GET['mode'] == 'checkDeliveryRange') {
        $cartres = checkDeliveryRangeList();
        foreach ($cartres as $cart) {
            $ret = checkDeliveryRange($_GET['address'], $cart['partner_no']);
            if ($ret[0] == false) {
                jsonReturn(array(
                    'status' => 'faild',
                    'message' => $ret[1]
                ));
            }
        }
        jsonReturn(array(
            'status' => 'success',
            'message' => null
        ));
        exit;
    }

	//[구/군] 목록 리스트 출력
	if($mode=='getGugun') {
        exit;
	}


	//페이징 공통 사용
	include_once $engine_dir."/_engine/include/paging.php";
	if($sido || $gugun)$_sigugun = trim(addslashes($sido." ".$gugun));
	$row = 10;
	$par = "&urlfix=Y&search=$search&form_nm=$form_nm&zip_nm=$zip_nm&addr1_nm=$addr1_nm&addr2_nm=$addr2_nm&zip_mode=".$zip_mode."&sido=".urlencode($sido)."&gugun=".urlencode($gugun);
	$page = numberOnly($_GET['page']);
	if($page <= 1) $page = 1;
	if(!$block) $block = 5;
	$QueryString = $par;


	//행자부 주소API 사용
	if($cfg['juso_api_use'] == "Y" && $search) {
		$juso_confmKey = $cfg['juso_api_key'];
		$tempurl = $cfg['juso_api_url'].'?currentPage='.$page."&countPerPage=".$row."&keyword=".urlencode(trim($_sigugun." ".$search))."&resultType=xml";
		if($cfg['juso_api_server'] == 2) {
			$tempurl .= "&confmKey=".$juso_confmKey;
		}
		$sult = comm($tempurl);
		$sult = simplexml_load_string($sult, 'SimpleXMLElement', LIBXML_NOCDATA);
		$sult = json_encode($sult);
		$sult_json = json_decode($sult, TRUE);
		if($sult_json['common']['errorCode']=='0') {
			//JSON파싱중 데이터가 1(row)일때는 2차 배열이 아님
			$NumTotalRec = intval($sult_json['common']['totalCount']);
			$total_page   = ceil($NumTotalRec / $row);
			$total_remainder = $NumTotalRec % $row;

			$res = $sult_json['juso'];
			if(is_array($res[0]) == false) $res = array($res);
			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
			$PagingInstance->addQueryString($QueryString);
			$PagingResult = $PagingInstance->result($pg_dsn);

			$pg_res = $pageRes = $PagingResult['PageLink'];
			$idx = $NumTotalRec-($row*($page-1));
		}else {
			if($search)msg("[ERROR]".$sult_json['common']['errorMessage'], "close");
		}
	}

	if($_GET['single_module']) {
		include_once $engine_dir."/_engine/common/skin_index.php";
		return;
	}


    // 배송 불가 체크 대상 상품 목록
    function checkDeliveryRangeList() {
        global $tbl, $pdo;

        addField($tbl['product'], 'partner_no', 'int(10) not null default "0" comment "파트너 코드"');

        $cart_tbl = ($_GET['sbscr'] == 'Y') ? $tbl['sbscr_cart'] : $tbl['cart'];
        $_params = array();

        $qry = "select c.pno, p.partner_no from {$cart_tbl} c inner join {$tbl['product']} p on c.pno=p.no where 1 $asql ".mwhere();
        if (isset($_GET['cart_selected']) && $_GET['cart_selected']) {
            $cart_selected = explode(',', $_GET['cart_selected']);
            $placeholder = trim(str_repeat(', ?', count($cart_selected)), ',');

            $qry .= " and c.no in ($placeholder)";
            $_params = array_merge($_params, $cart_selected);
        }
        return $cartres = $pdo->iterator($qry, $_params);
    }

	common_header();

?>
<script type="text/javascript">
const form_nm = '<?=$form_nm?>';
const cart_selected = '<?=$cart_selected?>';
const sbscr = '<?=$sbscr?>';

window.onload=function (){
    selfResize(document.body.scrollWidth, document.body.scrollHeight);
    if (document.zsFrm) {
        document.zsFrm.search.focus();
    }

    $('select[name=sido], select[name=gugun]').hide();
}

function putZip(zip,addr) {
	var of = window.opener.document.getElementsByName('<?=$form_nm?>');
	if(of.length > 0) {
		of = of[0];
		of.<?=$zip_nm?>.value=zip;
		of.<?=$addr1_nm?>.value=addr;
		of.<?=$addr2_nm?>.focus();

		try {
			if(typeof window.opener.checkFormResult == 'function') {
				window.opener.checkFormResult('addr');
			}
		} catch(Exception) {}


		try {
			if(typeof window.opener.useMilage == 'function') {
				window.opener.useMilage(of,3);
			}
		} catch(Exception) {}

		window.close();
	}
}

</script>
<?php if ($scfg->comp('juso_api_use', 'D') == true) { ?>
<div class="wrap"></div>
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
    var element_wrap = document.getElementById('wrap');

    function foldDaumPostcode() {
        element_wrap.style.display = 'none';
    }

    new daum.Postcode({
        oncomplete: function(data) {
            const self = this;
            if (form_nm == 'ordFrm' || form_nm == 'nAddrFrm') {
                fetch(root_url+'/main/exec.php?exec_file=common/zip_search.php&mode=checkDeliveryRange&cart_selected='+cart_selected+'&sbscr='+sbscr+'&address='+encodeURIComponent(data.address))
                    .then((res) => res.json())
                    .then((res) => {
                        if (res.status == 'success') {
                            putZip(data.zonecode, data.address);
                        } else {
                            window.alert(res.message);
                        }
                    });
            } else {
                    putZip(data.zonecode, data.address);
                }
            }
    }).embed(element_wrap, {q:'', autoClose: false});

</script>
<?php } ?>
<?php

	// 디자인 버전 점검 & 페이지 출력
    if ($cfg['juso_api_use'] == 'D') {
        close(1);
        exit;
    }

   	include_once $engine_dir."/_engine/common/skin_index.php";

?>