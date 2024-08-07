<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  qna, 1:1상담, 후기 미리보기
	' +----------------------------------------------------------------------------------------------+*/

	$rno = addslashes(trim($_GET['rno']));
	$type = addslashes(trim($_GET['type']));
	$field = addslashes(trim($_GET['field']));

	if($type=='qna') {
		$pre_tbl = $tbl['qna'];
		$sfield = "content, answer";
		$field1 = "content";
		$field2 = "answer";
		$title1 = "Q";
		$title2 = "A";
		if($field=='memo') {
			$sfield = "mng_memo";
			$field1 = "mng_memo";
			$field2 = "";
			$title1 = "내용";
			$title2 = "";
		}
	}else if($type=='cs') {
		$pre_tbl = $tbl['cs'];
		$sfield = "content, reply";
		$field1 = "content";
		$field2 = "reply";
		$title1 = "Q";
		$title2 = "A";
		if($field=='memo') {
			$sfield = "mng_memo";
			$field1 = "mng_memo";
			$field2 = "";
			$title1 = "내용";
			$title2 = "";
		}
	}else if($type=='review') {
		$pre_tbl = $tbl['review'];
		$sfield = "title as content, content as answer";
		$field1 = "content";
		$field2 = "answer";
		$title1 = "제목";
		$title2 = "내용";
	}else if($type=='comment') {
		$pre_tbl = $tbl['review_comment'];
		$sfield = "content";
		$field1 = "content";
		$field2 = "";
		$title1 = "내용";
		$title2 = "";
	}

	$pre_data = $pdo->assoc("select $sfield from $pre_tbl where no='$rno'");
	$pre_data['content'] = strip_tags(stripslashes($pre_data[$field1]));
	$pre_data['answer'] = strip_tags(stripslashes($pre_data[$field2]));

?>
<?if($pre_data['content'] || $pre_data['answer']) {?>
	<ul class="memberPreview<?if($type=='review' || $type=='comment' || $field=='memo') {?> review<?}?>">
		<li>
			<p>
				<span><?=$title1?></span>
				<?=$pre_data['content']?>
			</p>
			<?if($pre_data['answer']) {?>
			<p class="answer">
				<span><?=$title2?></span>
				<?=$pre_data['answer']?>
			</p>
			<?}?>
		</li>
	</ul>
<?}?>