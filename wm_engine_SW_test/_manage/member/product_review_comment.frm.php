<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 후기 작성
	' +----------------------------------------------------------------------------------------------+*/

	$ref = numberOnly($_GET['no']);
	$data = get_info($tbl['review'], "no", $ref);
	checkBlank($data['no'], "잘못된 경로로 접근하였습니다.");

	//리뷰 조회
	$data['name']     = stripslashes($data['name']);
	$data['title']    = strip_tags(stripslashes($data['title']));
	$data['content']  = nl2br(strip_tags(stripslashes($data['content'])));
	$data['rev_pt']   = strip_tags(stripslashes($data['rev_pt']));
	$data['reg_date'] = date('Y-m-d H:i', $data['reg_date']);
	include_once $engine_dir."/_engine/include/shop.lib.php";
	$w = 300;
	$h = 300;
	if($data['upfile1']) {
		$img = prdImg(1, $data, $w, $h);
		$data['img1'] = $img[0];
		$data['imgstr1'] = $img[1];
	}
	if($data['upfile2']) {
		$img = prdImg(2, $data, $w, $h);
		$data['img2'] = $img[0];
		$data['imgstr2'] = $img[1];
	}

	//리뷰 등록 상품 조회
	if($data['pno']) {
		$prd = $pdo->assoc("select no, hash, name, partner_no from `$tbl[product]` where `no` = '$data[pno]'");
		$prd['name'] = cutStr(strip_tags(stripslashes($prd['name'])), 60);
	}

	//관리자 확인
	if($admin['level'] == 4) {
		if($prd['partner_no'] != $admin['partner_no']) msg('열람 권한이 없습니다.', 'close');
	}

?>
<div class="box_title first">
	<h2 class="title">상품 리뷰</h2>
</div>
<table class="tbl_row">
	<caption class="hidden">상품 리뷰</caption>
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<? if($prd['no']) { ?>
	<tr>
		<th scope="row">상품</th>
		<td>
			<a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$prd['name']?></a>
			<a href="./?body=product@product_register&pno=<?=$prd['no']?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="상품 수정"></a>
		</td>
	</tr>
	<? } ?>
	<tr>
		<th scope="row">작성자</th>
		<td><?=$data['name']?>(아이디:<?=$data['member_id']?> , 일시:<?=$data['reg_date']?>)</td>
	</tr>
	<tr>
		<th scope="row">제목</th>
		<td><?=$data['title']?></td>
	</tr>
	<tr>
		<th scope="row">내용</th>
		<td>
			<?=$data['content']?>
		</td>
	</tr>
	<tr>
		<th scope="row">평점</th>
		<td><?=$data['rev_pt']?></td>
	</tr>
	<? if($data['img1'] || $data['img2']) { ?>
	<tr>
		<th scope="row">이미지</th>
		<td>
			<ul class="review_thumb">
				<?
					for($i = 1; $i <= 2; $i++) {
						if(!$data['img'.$i]) continue;
				?>
				<li><a href="<?=$data['img'.$i]?>" target="_blank"><img src="<?=$data['img'.$i]?>" style="width:150px"></a></li>
				<?}?>
			</ul>
		</td>
	</tr>
	<? } ?>
</table>
<form name="" method="post" action="./" target="hidden<?=$now?>">
	<input type='hidden' name='body' value='member@product_review_comment.exe' />
	<input type='hidden' name='ref' value='<?=$ref?>' />

	<div class="box_title">
		<h2 class="title">관리자 후기 작성</h2>
	</div>
	<table class="tbl_row">
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<tr>
		<th scope="row">작성자</th>
		<td>
			<select name='member_id'>
			<?php
			$res = $pdo->iterator("select member_id, name from $tbl[member] where level=1 order by name desc");
            foreach ($res as $member_data) {
				$member_data['name'] = stripslashes($member_data['name']);
				echo "<option value='$member_data[member_id]'>$member_data[name]</option>";
			}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row">내용</th>
		<td><textarea name="content" class='txta' style='width:670px; height: 100px;'></textarea></td>
	</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="작성"></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="self.close();"></span>
	</div>
</form>