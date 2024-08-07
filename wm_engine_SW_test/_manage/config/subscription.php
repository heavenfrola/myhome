<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  정기배송 설정
	' +----------------------------------------------------------------------------------------------+*/

	// 정기배송(무기한) 일 경우 세일 회차 제외된 상태 , 추가로 고려해야할 사항

	if(!$cfg['use_sbscr']) $cfg['use_sbscr'] = "N";
	if(!$cfg['sbscr_type']) $cfg['sbscr_type'] = "A";
	if(!$cfg['sbscr_holiday_after']) $cfg['sbscr_holiday_after'] = "N";
	if(!$cfg['sbscr_order_all'] && !$cfg['sbscr_order_split']) {
		$cfg['sbscr_order_all'] = "Y";
		$cfg['sbscr_order_split'] = "Y";
	}
    $order_all_disabled = ($scfg->comp('sbscr_dlv_type', 'N')) ? 'disabled' : '';

	if(!isTable($tbl['sbscr_set'])) {
		include_once $engine_dir.'/_plugin/subScription/tbl_schema.php';
		$pdo->query($tbl_schema['sbscr_set']);
		$pdo->query($tbl_schema['sbscr_set_product']);
	}

?>
<div class="box_title first">
	<h2 class="title">정기배송 설정</h2>
</div>
<div id="select_pg" class="box_tab first">
	<ul>
        <li><a href="#basic" class="tab_basic">기본 설정</a></li>
        <li><a href="#set" class="tab_set">상품별 설정</a></li>
        <li><a href="#holiday" class="tab_holiday">휴일 설정</a></li>
    </ul>
</div>

<div id="pannel_basic" style="display: none">
    <form name="sbscrFrm" method="post" target="hidden<?=$now?>" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="printLoading()">
        <input type="hidden" name="body" value="config@config.exe">
        <input type="hidden" name="config_code" value="subscription">

        <div class="box_sort"></div>

        <table class="tbl_row">
            <caption class="hidden">정기배송 설정</caption>
            <colgroup>
                <col style="width:15%">
                <col>
            </colgroup>
            <tr>
                <th scope="row" rowspan="2">정기배송 사용</th>
                <td>
                    <label for="use_sbscr_y" class="p_cursor"><input type="radio" name="use_sbscr" id="use_sbscr_y" value="Y" <?=checked($cfg['use_sbscr'],'Y')?>>사용함</label>
                    <label for="use_sbscr_n" class="p_cursor"><input type="radio" name="use_sbscr" id="use_sbscr_n" value="N" <?=checked($cfg['use_sbscr'],'N')?>>사용안함</label>
                </td>
            </tr>
            <tr>
                <td>
                    정기배송 사용 시 배송주기, 배송요일, 기간 설정을
                    <label for="sbscr_type_p" class="p_cursor">
                        <input type="radio" name="sbscr_type" id="sbscr_type_p" value="P" <?=checked($cfg['sbscr_type'],'P')?> onClick="subtypeCheck('P');">상품별
                        <a href="#set" class="setup"></a>
                    </label>
                    <label for="sbscr_type_a" class="p_cursor"><input type="radio" name="sbscr_type" id="sbscr_type_a" value="A" <?=checked($cfg['sbscr_type'],'A')?> onClick="subtypeCheck('A');">
                    일괄</label> 로 설정합니다.
                </td>
            </tr>
            <tr>
                <th scope="row">결제 방식</th>
                <td>
                    <label for="sbscr_order_all" class="p_cursor"><input type="checkbox" name="sbscr_order_all" id="sbscr_order_all" value="Y" <?=checked($cfg['sbscr_order_all'],'Y')?> <?=$order_all_disabled?>>일괄결제</label>
                    <label for="sbscr_order_split" class="p_cursor"><input type="checkbox" name="sbscr_order_split" id="sbscr_order_split" value="Y" <?=checked($cfg['sbscr_order_split'],'Y')?>>정기결제</label>
                </td>
            </tr>
            <tr>
                <th scope="row">주문 방식</th>
                <td>
                    <label for="sbscr_cart_type">
                        장바구니에서 정기배송 상품을 다른 정기배송 상품과
                        <select name="sbscr_cart_type">
                            <option value="T" <?=checked($cfg['sbscr_cart_type'],'T',1)?>>함께</option>
                            <option value="S" <?=checked($cfg['sbscr_cart_type'],'S',1)?>>따로</option>
                        </select> 주문합니다.
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">첫 배송 날짜 지정</th>
                <td>
                    <label for="sbscr_first_date">
                        주문하는 날부터
                        <select name="sbscr_first_date">
                            <?php
                            for($ii=0;$ii<=5;$ii++) {
                                $title = ($ii > 0) ? $ii.'일 이후' : '오늘부터';
                            ?>
                            <option value="<?=$ii?>" <?=checked($cfg['sbscr_first_date'],$ii,1)?>><?=$title?></option>
                            <?php } ?>
                        </select> 로 첫 배송일 지정
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row" rowspan="2">주문서 생성</th>
                <td>
                    배송 시작
                    <label for="sbscr_order_create">
                        <select name="sbscr_order_create">
                            <?php for($ii=1;$ii<=5;$ii++) { ?>
                            <option value="<?=$ii?>" <?=checked($cfg['sbscr_order_create'],$ii,1)?>><?=$ii?>일 전</option>
                            <?php } ?>
                        </select>
                        주문서 생성
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    공휴일일 경우
                    <label for="sbscr_holiday_create">
                        <select name="sbscr_holiday_create">
                            <?php for($ii=1;$ii<=5;$ii++) { ?>
                            <option value="<?=$ii?>" <?=checked($cfg['sbscr_holiday_create'],$ii,1)?>><?=$ii?>일</option>
                            <?php } ?>
                        </select>
                        <select name="sbscr_holiday_after">
                            <option value="N" <?=checked($cfg['sbscr_holiday_after'],'N',1)?>>이전</option>
                            <option value="Y" <?=checked($cfg['sbscr_holiday_after'],'Y',1)?>>이후</option>
                        </select>
                        주문서 생성
                    </label>
                </td>
            </tr>
        </table>
        <div class="box_middle2 left">
            <ul class="list_msg">
                <li>공휴일일 경우 주문서 생성일은 최소 3일전을 추천드리며, 배송 출발 일자에 따라 조절해주시면 됩니다.</li>
            </ul>
        </div>
        <div class="box_bottom">
            <span class="box_btn blue"><input type="submit" value="확인"></span>
        </div>
    </form>

    <div id="sbscrallFrm">
        <div class="box_title">
            <h2 class="title">정기배송 일괄 설정</h2>
        </div>
        <?php include $engine_dir."/_manage/product/sub_set.frm.php"; ?>
    </div>
</div>

<!-- 정기배송 세트 설정 -->
<?php require 'subscription_set.php'; ?>

<!-- 정기배송 휴일 설정 -->
<?php require 'subscription_holiday.php'; ?>

<script type="text/javascript">
	var subtype = "<?=$cfg['sbscr_type']?>";
	$(document).ready(function() {
		var use_sbscr = "<?=$cfg['use_sbscr']?>";
		subtypeCheck(subtype);
		usesbscrCheck(use_sbscr);
	});
	function usesbscrCheck(t) {
		if(t=='N') {
			$('#sbscrallFrm').hide();
			$('#sbscrsetFrm').hide();
		}else {
			subtypeCheck(subtype);
		}
	}
	function subtypeCheck(t) {
		if(t=='A') {
			$('#sbscrallFrm').show();
			$('#sbscrsetFrm').hide();
		}else {
			$('#sbscrallFrm').hide();
			$('#sbscrsetFrm').show();
		}
	}

    function tabover(hash)
    {
        if (!hash) {
            hash = (location.href.indexOf('#') > 0) ? location.href.replace(/.*#/, '') : 'basic';
        }

        $('#pannel_basic, #pannel_set, #pannel_holiday').not('#pannel_'+hash).hide();
        $('#pannel_'+hash).show();

        $('#select_pg a').removeClass('active');
        $('.tab_'+hash).addClass('active');
    }

    $(function() {
        $('#select_pg a').on('click', function() {
            tabover(this.href.replace(/.*#/, ''));
        });

        $(window).on('hashchange', function() {
            tabover();
        });
        tabover();
    });
</script>