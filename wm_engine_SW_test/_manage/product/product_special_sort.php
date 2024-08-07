<?PHP

/**
 * 기획전 상품 정렬
 **/

if (isset($ctype) == false) $ctype = 2;
$ctype = (isset($_GET['ctype']) == true) ? $_GET['ctype'] : $ctype;
$cno = (int) $_GET['cno'];

if (in_array($ctype, array('2', '6')) == false) $ctype = '2';
${'active_ctype'.$ctype} = 'active';

// 기획전 목록
$res = $pdo->iterator("select * from {$tbl['category']} where ctype='$ctype' order by sort asc, no desc");
foreach ($res as $data) {
    $selected = ($data['no'] == $cno) ? 'selected' : '';
    $_ebig .= "\n<option value='{$data['no']}' $selected>".stripslashes($data['name'])."</option>";
}

// 기획전 내의 상품 목록
$res = array();
if ($cno > 0) {
    $cno = numberOnly($_GET['cno']);
    $ename = ($ctype == '2') ? 'ebig' : 'mbig';

    $w  = " and l.ctype='$ctype' and l.nbig='$cno'";
    $w .= " and p.$ename like '%@$cno@%'";

    $res = $pdo->iterator("
        select
            p.no, p.stat, p.wm_sc, p.updir, p.upfile3, p.w3, p.h3,
            p.name, p.reg_date, p.sell_prc, hit_order,
            p.hit_sales, p.hit_wish, p.hit_cart, p.hit_view,
            l.idx as sortidx, l.sort_big,
            (if(p.ea_type=1, (select sum(qty) from erp_complex_option where pno=p.no and del_yn='N'), 0)) as stock_qty
        from {$tbl['product']} p inner join {$tbl['product_link']} l on p.no=l.pno
        where stat in (2,3,4) $w
        order by l.`sort_big` asc
    ");
    if (is_object($res) == true) $prd_ea = $res->rowCount();
    if ($prd_ea == 0) {
        $message = '기획전내에 상품이 없습니다.';
    }
} else {
    $message = '상품을 정렬할 분류를 검색해 주세요.';
    $res = array();
}

function parser(&$res)
{
    global $_prd_stat;

    if ($res instanceof Wing\DB\PDOIterator == false) {
        return false;
    }

    $data = $res->current();
    $res->next();
    if ($data == false) return false;

    // 품절 및 숨김 상태
    $data['stat_str'] = ($data['stat'] != 2) ? $_prd_stat[$data['stat']] : '';

    // 이미지
    $data['upfile'] = getListImgURL($data['updir'], $data['upfile3']);
    $data['imgstr'] = setImageSize($data['w3'], $data['h3'], 50, 50);

    // 카테고리
    $data['cstr'] = getCateName($data['big']);
    if ($data['mid']) $data['cstr'] .= ' > '.getCateName($data['mid']);
    if ($data['small']) $data['cstr'] .= ' > '.getCateName($data['small']);

    // 상품 데이터
    $data['name'] = cutStr(stripslashes($data['name']), 50, '...');

    return $data;
}

?>
<form id="search" name="scFrm" method="get" action="./index.php">
	<input type="hidden" name="body" value="product@product_special_sort">
	<input type="hidden" name="ctype" value="<?=$ctype?>">

	<div class="box_title first">
		<h2 class="title">기획전 상품진열순서</h2>
	</div>
	<div class="box_middle sort">
		<ul class="tab_sort">
			<li class="<?=$active_ctype2?>"><a href="?body=<?=$_GET['body']?>&ctype=2">기획전</a></li>
			<li class="<?=$active_ctype6?>"><a href="?body=<?=$_GET['body']?>&ctype=6">모바일 기획전</a></li>
		</ul>
	</div>
	<table class="tbl_row">
		<colgroup>
			<col style="width:15%;">
		</colgroup>
		<tr>
			<th scope="row">기획전 상품진열순서</th>
			<td>
                <div class="searching_select">
				<select name="cno">
					<option value="">::선택::</option>
					<?=$_ebig?>
				</select>
                </div>
			</td>
		</tr>
	</table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn"><input type="reset" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
    </div>
</form>
<br>

<div class="frame_sort" style="min-height: 400px;">
	<form id="cateSortFrm" method="post" action="./" target="hidden<?=$now?>">
		<input type="hidden" name="body" value="product@product_special_sort.exe">
		<input type="hidden" name="cno" value="<?=$cno?>">

        <div class="box_tab first">
            <ul>
                <li><a class="active">전체<span><?=$prd_ea?></span></a></li>
            </ul>
        </div>
        <div class="box_middle left">
            <ul class="list_info">
                <li>상품이 많은 쇼핑몰의 경우 처리시간이 오래 걸리거나 사이트가 순간적으로 느려질 수 있으므로 가급적 접속자가 많은 시간을 피해 진행해 주시기 바랍니다.</li>
                <li>이동하실 상품을 클릭한 뒤 키보드 ↑ ↓ 키로 이동 가능하며, ctrl키와 shift키로 복수의 상품을 선택하여 이동시킬 수 있습니다.</li>
            </ul>
        </div>

		<div id="prdSort">
			<table id="table_goodsSorting" class="tbl_col">
				<caption class="hidden">기획전 리스트</caption>
				<colgroup>
					<col style="width:70px">
					<col style="width:70px">
					<col>
					<col style="width:70px">
					<col style="width:65px">
					<col style="width:65px">
					<col style="width:65px">
					<col style="width:65px">
					<col style="width:65px">
					<col style="width:65px">
				</colgroup>
				<thead>
					<tr>
						<th scope="col">번호</th>
						<th scope="col">이미지</th>
						<th scope="col">상품명</th>
						<th scope="col">가격</th>
						<th scope="col">조회</th>
						<th scope="col">담기</th>
						<th scope="col">관심</th>
						<th scope="col">주문</th>
						<th scope="col">판매</th>
						<th scope="col">재고</th>
					</tr>
				</thead>
				<tbody>
					<?php while ($data = parser($res)) { ?>
					<tr class="movable" id="<?=$data['sortidx']?>">
						<input type="hidden" name="pno[]" value="<?=$data['sortidx']?>">
						<td><?=(++$ii)?></td>
                        <td>
                            <?php if ($data['upfile']) { ?>
                            <img src="<?=$data['upfile']?>" <?=$data['imgstr'][2]?>>
                            <?php } ?>
                        </td>
						<td class="left" style="word-break:break-all;">
                            <div class="box_setup">
                                <strong><?=$data['name']?>
                                <?php if ($data['stat'] == '3' || $data['stat'] == '4') { ?>
                                <span class="p_color2">- <?=$data['stat_str']?></span></strong>
                                <?php } ?>
                                <p class="cstr"><?=$data['cstr']?></p>
                                <span class="box_btn_s btnp">
                                    <a href="./?body=product@product_register&pno=<?=$data['no']?>" target="_blank">수정</a>
                                </span>
                            </div>
						</td>
						<td><?=parsePrice($data['sell_prc'], true)?><?=$cfg['currency_type']?></td>
						<td><?=number_format($data['hit_view'])?></td>
						<td><?=number_format($data['hit_cart'])?></td>
						<td><?=number_format($data['hit_wish'])?></td>
						<td><?=number_format($data['hit_order'])?></td>
						<td><?=number_format($data['hit_sales'])?></td>
                        <td><?=number_format($data['stock_qty'])?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
            <?php if (isset($message) == true) { ?>
            <div class="box_middle2">
                <?=$message?>
            </div>
            <?php } ?>
		</div>
		<div id="quickmenu" style="position:absolute; right:50px;">
			<ul>
				<li><img src="<?=$engine_url?>/_manage/image/arrow_up.gif" class="move_btn_up"></li>
				<li class="ea"><input type="text" id="step" name="step" value="1" class="input"> 칸 이동</li>
				<li><img src="<?=$engine_url?>/_manage/image/arrow_down.gif" class="move_btn_dn"></li>
				<li>
					<span class="box_btn_s"><input type="button" value="최상" class="move_btn_top"></span>
					<span class="box_btn_s"><input type="button" value="최하" class="move_btn_bottom"></span>
				</li>
				<li><span class="box_btn_s gray full"><input type="button" value="삭제" onclick="deleteProduct();"></span></li>
			</ul>
			<div class="box_btn gray"><input type="button" value="초기화" onclick="location.reload();"></div>
			<div class="box_btn blue"><input type="button" onclick="sorting_exe()" value="적용하기"></div>
		</div>
	</form>
</div>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Slider.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_manage/_js/productSort.js"></script>
<script type="text/javascript">
    var f = document.getElementById('cateSortFrm');

    var R2S = new R2Slider('quickmenu', 'R2S', 1, 10);
    R2S.limitTop = $(f).offset().top;
    R2S.limitBottom = 50;
    R2S.slide();

    // 정렬 내용 저장
	function sorting_exe(){
		var sortingArray = [];
		var deleteArray = [];

		$('#table_goodsSorting tr.movable').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			sortingArray.push(pno);
		});
		$('#table_goodsSorting tr.delete').each(function() {
			var pno = $(this).attr('id');
			if( !pno ) return;
			deleteArray.push(pno);
		});

		printLoading();

		var url = '/_manage/';
		var data = 'body=product@product_special_sort.exe';
		data += '&sortingArray=' + sortingArray.join('|');
		data += '&deleteArray=' + deleteArray.join('|');
		data += '&cno=' + f.cno.value;

		$.ajax({
			url: url,
			type: 'post',
			data: data,
			success: function(data) {
				if( data == 'OK' ) {
					alert('정렬순서가 변경되었습니다.');
					reset_numbering();
				}
				else if( data == 'ERROR1' ){
					alert("상품 업데이트부터 하십시오.");
				}
				else if( data == 'ERROR2' ){
					alert("정렬할 상품이 없습니다.");
				}
				else if( data == 'ERROR3' ){
					alert("기획전이 정확하지 않습니다.");
				}
				else{
					alert('정렬순서 변경에 실패하였습니다.');
				}
			},
			complete: function(r) {
				removeLoading();
			}
		});
	}

	function reset_numbering(){
        $('.movable.delete').remove();

		var i = 1;
		$('#table_goodsSorting tr.movable').each(function() {
			$(this).find("td:first").html(i);
			i++;
		});
	}

    function deleteProduct() {
        $('.movable.soldout').each(function() {
            $(this).addClass('delete').hide();
        });
    }

	$(document).ready(function(){
		$("#overlay").css({ "width" : (screen.width - 25) + 'px', "height" : $(document).height() + 'px'});
	});
</script>