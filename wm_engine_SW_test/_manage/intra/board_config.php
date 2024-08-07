<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  인트라넷게시판권한 설정
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	function authPrint($auth) {
		global $data, $_mng_group;
		$str="<select name=\"".$auth."[]\" style=\"width:150px\">\n";
		foreach($_mng_group as $key=>$val) {
			$sel=checked($data[$auth],$key,1);
			$str.="<option value=\"$key\" $sel>".$_mng_group[$key]."</option>\n";
		}
		$str.="</select>";
		return $str;
	}

	$sql="select * from `$tbl[intra_board_config]` order by `db`";
	include $engine_dir."/_engine/include/paging.php";

	$row = numberOnly($_GET['row']);
	$page = numberOnly($_GET['page']);
	$auth = $_GET['auth'];

	if($page<=1) $page=1;
	$row=15;
	$block=10;
	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}

	$NumTotalRec = $pdo->row(str_replace("select * from", "select count(*) from", $sql));
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))

?>
<script language="JavaScript" src="<?=$root_url?>/board/common/common.js"></script>

<form name="listFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="intra@board_config.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="delete_db" value="">
	<input type="hidden" name="auth" value="<?=$auth?>">
	<div class="box_title first">
		<h2 class="title">게시판 등록/관리</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">게시판 등록/관리</caption>
		<colgroup>
			<col style="width:60px">
			<col style="width:100px">
			<?if($auth){?>
			<col span="5">
			<?}else{?>
			<col>
			<col style="width:200px">
			<col style="width:200px">
			<?}?>
		</colgroup>
		<thead>
			<tr>
				<th scope="col">no</th>
				<th scope="col">게시판명</th>
				<?if($auth){?>
				<th scope="col">목록 보기</th>
				<th scope="col">글 보기</th>
				<th scope="col">글 쓰기</th>
				<th scope="col">댓글 쓰기</th>
				<th scope="col">파일 업로드</th>
				<?}else{?>
				<th scope="col">제목</th>
				<th scope="col">등록된 글수</th>
				<th scope="col">삭제</th>
				<?}?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($res as $data) {?>
			<tr>
				<td><input type="hidden" name="no[]" id="no" value="<?=$data[no]?>"><?=$idx?></td>
				<td><?=$data['title']?></td>
				<?if($auth){?>
				<td><?=authPrint("auth_list")?></td>
				<td><?=authPrint("auth_view")?></td>
				<td><?=authPrint("auth_write")?></td>
				<td><?=authPrint("auth_comment")?></td>
				<td><?=authPrint("auth_upload")?></td>
				<?}else{?>
				<td class="left"><input type="text" name="title[]" value="<?=$data[title]?>" class="input input_full"></td>
				<td><?=$data[total_content]?></td>
				<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="intraBoardDel('<?=$data[db]?>');"></span></td>
				<?}?>
			</tr>
			<?
				$idx--;
				}
			?>
		</tbody>
	</table>
	<div class="box_bottom">
		<div class="left_area">
			<span class="box_btn blue"><input type="button" value="설정적용" onclick="document.listFrm.submit();"></span>
		</div>
        <div class="right_area">
            <?php if (!$auth) { ?>
            <span class="box_btn blue"><input type="button" value="신규 게시판 생성" onclick="newIntraBoard()"></span>
            <?php } ?>
        </div>
		<?=$pg_res?>
	</div>
</form>

<script language="JavaScript">
	function intraBoardDel(db){
		if(!confirm('해당 게시판을 삭제하시겠습니까?')) return;
		f=document.listFrm;
		f.exec.value='delete';
		f.delete_db.value=db;
		f.submit();
	}

    function newIntraBoard()
    {
        printLoading();
        $.post('./index.php', {'body': 'intra@board_config.exe', 'exec': 'create'}, function() {
            location.reload();
        });
    }
</script>