<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  상품아이콘 관리
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir.'/_manage/product/product_icon.inc.php';
	$bottom_style = ($imode == 1) ? 'box' : 'pop';

	if(!fieldExist($tbl['product_icon'], "sort")) {
		addField($tbl['product_icon'], 'sort', 'int(5) not null default 0');
		$pdo->query("insert into $tbl[config] (name, value, reg_date, edt_date, admin_id) values ('product_icon_sort', 'Y', '$now', '$now', '$admin[admin_id]')");
	}
    //[매장지도] 매장 아이콘 추가
    $type = addslashes($_GET['type']);
    if($_GET['type'] == 'store') {
		$lsql = " and itype=9";
        $itype = 9;
	}
 ?>
<form name="mngIconFrm" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="hidden<?php echo $now;?>" onSubmit="return checkBlank(this.upfile,'찾아보기를 눌러 업로드할 아이콘을 입력해주세요.')">
	<input type="hidden" name="body" value="product@product_icon.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ino" value="">
    <input type="hidden" name="itype" value="<?php echo $itype;?>">
	<input type="hidden" name="imode" value="<?php echo $imode; ?>">
	<?php if($imode) {?>
	<div class="box_title">
		<h2 class="title">상품 아이콘 등록 관리</h2>
	</div>
	<div class="box_middle">
	<?php }?>
		<div class="product_icon_area">
			<div class="list_info left">
				<p>등록된 아이콘은 드래그를 통해 순서변경이 가능합니다.</p>
			</div>
			<ul class="product_icon_list">
				<?php
					$ii=0;
					$sql="select * from {$tbl['product_icon']} where itype='$itype' $lsql order by sort, no desc";
					$res = $pdo->iterator($sql);
                    foreach ($res as $data) {
						$ii++;
				?>
				<li class="icon_sort" data-no="<?=$data['no']?>">
					<div class="img"><?=getIconTag($data)?></div>
					<span class="box_btn_s"><a href="javascript:modifyPrdIcon('<?=$data['no']?>')">수정</a></span>
					<span class="box_btn_s"><a href="javascript:delPrdIcon('<?=$data['no']?>')">삭제</a></span>
				</li>
				<?php
					}
				?>
			</ul>
		</div>
	<?php if($imode) {?>
	</div>
	<?php }?>
	<table class="tbl_row">
		<caption class="hidden">상품 아이콘 등록 관리</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">신규 등록</th>
			<td>
				<input type="file" name="upfile" class="input input_full">
				<span class="box_btn_s blue"><input type="submit" value="등록"></span>
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript">
	$(".product_icon_list").sortable({
		'placeholder': 'placeholder',
		'cursor':'all-scroll',
		'scroll': false,
		update: function (event, ui) {
		  var icon_sort = new Array();
		  $('.icon_sort').each(function() {
			  var icon_no = $(this).data('no');
			  icon_sort.push(icon_no);
		  });

		  $.ajax({
				type:"POST",
				url:"./index.php?body=product@product_icon.exe",
				data:{"icon_sort" : icon_sort.toString(), "exec":"sort"},
				dataType:"html",
				success: function(result) {
					if(result) {
						//alert("아이콘 순서가 변경되었습니다.");
					}else {
						alert("잠시 후 다시 시도해 주세요.");
					}
				},
				error: function(e) {
					alert("잠시 후 다시 시도해 주세요.");
				}
			});
		}
	});
</script>
<?if(!$imode) {?>
<script type="text/javascript">
	$(window).ready(function () {
		icRefresh();
		setTimeout(function() {
			setPoptitle('아이콘 추가관리');
			selfResize();
		}, 1000);
	});
</script>
<?}?>