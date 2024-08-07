<?PHP

/**
 * [매장지도] 리스트
 */

    include $engine_dir."/_engine/include/paging.php";

    $page = numberOnly($_GET['page']);
    if($page <= 1) $page = 1;
    if(!$row) $row = 15;
    $block = 20;

    //지역 리스트
    $_arr_sido = $_kakao_store_handler->getStoreAddr('sido');

    $where="";
    $_qry_arr = array();

    $_partner_no = ($admin['level'] == 4) ? $admin['partner_no']:0;
    $_qry_arr[':partner_no'] = $_partner_no;
    $where .= " and partner_no=:partner_no";

    $_search_key = array(
        'title' =>'상호명',
        'phone' =>'전화번호',
        'cell' =>'휴대전화',
        'owner' =>'대표자명',
    );

    $search_str = addslashes($_GET['search_str']);
    $search_key = addslashes($_GET['search_key']);

    if($search_str) {
        foreach($_search_key as $sname => $v) {
			if($search_key == $sname) {
                $_qry_arr[':'.$sname] = "%".$search_str."%";
				$where .= " and ".$sname." like :".$sname;
			}
		}
    }

    $sido = addslashes($_GET['sido']);
    if($sido) {
        $_qry_arr[':sido'] = $sido;
        $where .= " and sido=:sido";
    }

    $hidden = addslashes($_GET['hidden']);
    if($hidden) {
		$_qry_arr[':hidden'] = $hidden;
		$where .= " and hidden=:hidden";
	}

    $stat = numberOnly($_GET['stat']);
    if($stat) {
        $_qry_arr[':stat'] = $stat;
        $where .= " and stat=:stat";
    }

    $xls_query = makeQueryString('page', 'body');

    $sql = "select * from {$tbl['store_location']} where 1 $where order by no desc";

    $NumTotalRec = $pdo->rowCount($sql, $_qry_arr);
    $PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
    $PagingInstance->addQueryString(makeQueryString('page'));
    $PagingResult = $PagingInstance->result($pg_dsn);

    $pg_res = $PagingResult['PageLink'];
    $res = $pdo->iterator($sql.$PagingResult['LimitQuery'], $_qry_arr);
    $idx = $NumTotalRec-($row*($page-1));

    if($body == 'config@store_location_excel.exe') return;
?>

    <form name="slFrm" method="get" action="./" id="search">
        <input type="hidden" name="body" value="config@store_location">
    <div class="box_title first">
        <h2 class="title">오프라인 매장 검색</h2>
    </div>

    <div id="search">
        <div class="box_search">
            <div class="box_input">
                <div class="select_input shadow full">
                    <div class="select">
						<?php echo selectArray($_search_key,"search_key",2,"",$search_key); ?>
                    </div>
                    <div class="area_input">
                        <input type="text" name="search_str" value="<?php echo inputText($search_str); ?>" class="input" placeholder="검색어를 입력해주세요.">
                    </div>
                </div>
            </div>
        </div>
        <table class="tbl_search">
            <caption class="hidden">오프라인 매장 검색</caption>
            <colgroup>
                <col style="width:15%">
                <col style="width:30%">
                <col style="width:15%">
                <col style="width:30%">
            </colgroup>
            <tr>
                <th scope="row">지역</th>
                <td colspan="3">
					<?php echo selectArray($_arr_sido,"sido",2,"선택",$sido); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">노출 여부</th>
                <td>
					<?php echo radioNewArray($_store_config_hidden, 'hidden', 2, '전체', $hidden,''); ?>
                </td>
                <th scope="row">상태</th>
                <td>
					<?php echo radioNewArray($_store_config_stat, 'stat', 2, '전체', $stat,''); ?>
                </td>
            </tr>
        </table>
        <div class="box_bottom top_line">
            <span class="box_btn blue"><input type="submit" value="검색"></span>
            <span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
        </div>
    </div>
</form>
<br>
<form name="sLocationSortFrm" id="sLocationSortFrm" method="post" action="./" target="hidden<?php echo $now; ?>">
    <input type="hidden" name="body" value="config@store_location.exe">
    <input type="hidden" name="exec" value="">
    <div class="box_title">
        <strong id="total_prd"><?php echo number_format($NumTotalRec); ?></strong>개의 매장이 검색되었습니다.
        <div class="btns">
            <span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=config@store_location_excel.exe<?=$xls_query?>'"></span>
        </div>
    </div>
	<table class="tbl_col">

		<caption class="hidden">매장 관리</caption>
		<colgroup>
			<col style="width:50px">
            <col style="width:50px">
            <col style="width:300px">
			<col >
            <col style="width:150px">
            <col style="width:150px">
            <col style="width:60px">
			<col style="width:150px">
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" class="check_all" onclick="checkAll(document.sLocationSortFrm.check_pno,this.checked)"></th>
                <th scope="col">번호</th>
				<th scope="col">상호명</th>
                <th scope="col">주소</th>
                <th scope="col">전화번호</th>
                <th scope="col">대표자명</th>
                <th scope="col">상태</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
            <?php if ($NumTotalRec == 0) { ?>
                <tr>
                    <td colspan="8"><p class="nodata">등록된 오프라인 매장이 없습니다.</p></td>
                </tr>
            <?php } ?>
			<?php
                foreach ($res as $data) {
					$_addr = array();
                    $_addr[] = $data['zipcode'];
					$_addr[] = $data['addr1'];
					$_addr[] = $data['addr2'];
                    $addr = @implode(" ",$_addr);

					$data['phone'] = pregNumber($data['phone']);
					$data['cell'] = pregNumber($data['cell']);
           ?>
			<tr id="sno_<?php echo $data['no'];?>" class="fieldset">
				<td><input type="checkbox" name="no[]" id="check_pno" value="<?php echo $data['no'];?>" class="check_one"></td>
                <td><?php echo $idx;?></td>
				<td class="left"><strong><?php echo stripslashes($data['title']);?></strong></td>
                <td class="left"><?php echo $addr;?></td>
                <td class="left"><?php echo $data['phone'];?></td>
                <td class="left"><?php echo $data['owner'];?></td>
                <td class="left"><?php echo $_store_config_stat[$data['stat']];?></td>
				<td>
                    <span class="box_btn_s"><a href="?body=config@store_location_register&no=<?=$data['no']?>">수정</a></span>
                    <span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeDefinition(<?=$data['no']?>);"></span>
				</td>
			</tr>
			<?php $idx--; }?>
		</tbody>
	</table>
	<div class="box_bottom" style="height: 30px;">
		<div class="left_area">
			<span class="box_btn_s icon delete"><input type="button" value="선택삭제" onclick="removeDefinition(this.form)"></span>
		</div>
		<div class="right_area">
			<span class="box_btn_s icon setup"><a href="?body=config@store_location_register">추가</a></span>
		</div>
	</div>
    <!-- 페이징 & 버튼 -->
    <div class="box_bottom"><?php echo $pg_res; ?></div>
    <!-- //페이징 & 버튼 -->
</form>


<script type="text/javascript">
    function removeDefinition(f) {
        var param = null;
        if(typeof f == 'object') {
            if($('.check_one:checked').length == 0) {
                window.alert('삭제할 데이터를 선택해주세요.');
                return false;
            }
            f.exec.value='remove';
            param = $(f).serialize();
        } else {
            var form = document.getElementById('sLocationSortFrm');
            param = {'body':form.body.value, 'exec':'remove', 'no[]':f};
        }

        if(confirm('선택한 매장을 삭제하시겠습니까?')) {
            printLoading();
            $.post('./index.php', param, function(r) {
                location.reload();
            });
        }
    }

</script>