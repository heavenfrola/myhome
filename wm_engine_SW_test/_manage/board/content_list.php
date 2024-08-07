<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시물 관리
	' +----------------------------------------------------------------------------------------------+*/

	$mng = numberOnly($_GET['mng']);
	if($mng==2) {
		$table="mari_comment";
		$fld1="내용";
		$fld2="content";
		$fld3="ref";
		$board_title = "댓글 관리";

		$_search_type['ref'] = '부모글';
	}
	else {
		$table="mari_board";
		$fld1="제목";
		$fld2="title";
		$fld3="no";
		$board_title = "게시물 관리";
	}

	$board=array();
	$res = $pdo->iterator("select * from `mari_config` order by `no`");
    foreach ($res as $data) {
		$board[$data[db]]=$data[title];
	}

	if($mng!=2) $_search_type[title]='제목';
	$_search_type[content]='내용';
	$_search_type[name]='이름';
	$_search_type[member_id]='아이디';

	$search_type = addslashes($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str!="") {
		$w.=" and `$search_type` like '%$search_str%'";
	}
	$db = addslashes(trim($_GET['db']));
	if($db) {
		$w.=" and `db`='$db'";

		$config = $pdo->assoc("select * from mari_config where db='$db'");
	}

	if($mng == 1 || !$mng) $odby="`ref` desc, `step` asc"; else $odby="`no` desc";
	if($config['use_sort'] == 'Y') $odby = 'notice!="Y", reg_date desc, no desc';
	$sql="select * from `$table` where 1 $w order by $odby";

	if($body == 'board@board_excel.exe') return;

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from $table where 1 $w");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

	$ssid = session_id();
	$_SESSION['list_url'] = getURL();

	$xls_query = makeQueryString('page', 'body');

?>
<form name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="mng" value="<?=$mng?>">
	<div class="box_title first">
		<h2 class="title"><?=$board_title?></h2>
	</div>
	<div class="box_search box_search2">
		<div class="box_input">
			<div class="select_input shadow">
				<div class="select">
					<?=selectArray($board,"db",2,"게시판선택",$db)?>
					<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
				</div>
				<div class="area_input">
					<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
				</div>
			</div>
		</div>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET[body]?>'"></span>
	</div>
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 게시물이 검색되었습니다.
		<div class="btns">
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=board@board_excel.exe<?=$xls_query?>'"></span>
		</div>
	</div>
	<div class="box_middle">
		<iframe src="<?=$ori_root_url?>/_manage/?body=board@mng_login.frm&ssid=<?=$ssid?>&admin_no=<?=$admin['no']?>" name="mng_login_ifrm" width="100%" height="34px" scrolling="no" frameborder="0"></iframe>
	</div>
	<table class="tbl_col">
		<caption class="hidden">게시물 관리 리스트</caption>
		<colgroup>
			<col style="width:60px">
			<col style="width:60px">
			<col style="width:200px">
			<col>
			<col style="width:50px">
			<?if($mng != 2) {?>
			<col style="width:50px">
			<?}?>
			<col style="width:180px">
			<col style="width:180px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">게시판</th>
				<th scope="col"><?=$fld1?></th>
				<th scope="col">본문</th>
				<?if($mng != 2) {?>
				<th scope="col">댓글</th>
				<?}?>
				<th scope="col">작성자</th>
				<th scope="col">작성일시</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$RE="[RE] ";
				if($GLOBALS[_reply_img]) $RE="<img src=\"".$GLOBALS[_reply_img]."\">";
                foreach ($res as $data) {
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
					$comment=($data[total_comment]>0) ? "[".$data[total_comment]."]" : "";
					if($data[level]>0) {
						$wid=$data[level]*13;
						$t_head="<img src=\"$root_url/image/spacer.gif\" width=$wid height=1>".$RE." ";
					}
					else {
						$t_head="";
					}
					$data[title]=$t_head.$data[title];

					$_content_link=$root_url."/board/?db=".$data[db]."&mari_mode=view@view&no=".$data[$fld3];
					$_link="./?body=board@content_view&no=".$data[$fld3];

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['name'] = strMask($data['name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }

					if($NumTotalRec==$idx) {
						addPrivacyViewLog(array(
							'page_id' => 'board',
							'page_type' => 'list',
							'target_id' => $data['member_id'],
							'target_cnt' => $NumTotalRec
						));
					}
					$sno = ($data['notice'] == 'Y') ? '공지' : $idx;
			?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>"></td>
				<td><?=$sno?></td>
				<td><a href="<?=$root_url?>/board/?db=<?=$data[db]?>" target="_blank"><?=$board[$data[db]]?></a></td>
				<td class="left">
					<?php if ($data['secret'] == 'Y') { ?>
					<img src="<?=$engine_url?>/_manage/image/icon/secret_r.gif">
					<?php } ?>
					<?php if ($data['hidden'] == 'Y') { ?>
					<img src="<?=$engine_url?>/_manage/image/icon/hidden_r.png">
					<?php } ?>
					<a href="<?=$_link?>"><?=cutStr(stripslashes($data[$fld2]),45)?></a> <span class="explain"><?=$comment?></span>
				</td>
				<td><a href="<?=$_content_link?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="본문 글 내용 바로가기"></a></td>
				<?if($mng != 2) {?>
				<td>
					<?if($data['total_comment'] > 0) {?>
					<a href="?body=board@content_list&mng=2&search_type=ref&search_str=<?=$data['no']?>"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="댓글보기"></a>
					<?}?>
				</td>
				<?}?>
				<td class="left" <?=align('member_01')?>>
					<a href="javascript:;" onClick="viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>')"><b><?=$data[name]?></b></a>
					<?if($data[member_no]){?>
					<a href="javascript:;" onClick="viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>')"> (<?=$data['member_id_v']?>) <?=blackIconPrint('',$data)?></a>
					<?}?>
				</td>
				<td<?if($data[ip]) {?> onmouseover="showToolTip(event,'<b>IP 주소</b> : <?=$data[ip]?>')" onmouseout="hideToolTip();"<?}?>><?=date("Y/m/d H:i:s",$data[reg_date])?></td>
			</tr>
			<?
					$idx--;
				}
			?>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<?if($NumTotalRec && $_GET['mng'] != 2){?>
		<select name="next_db">
			<?foreach($board as $key=>$val) {?>
			<option value="<?=$key?>">"<?=$val?>" 게시판으로</option>
			<?}?>
		</select>
		<span class="box_btn_s icon move"><input type="button" value="선택이동" onclick="moveArticle();"></span>
		<span class="box_btn_s icon copy"><input type="button" value="선택복사" onclick="copyArticle();"></span>
		<?}?>
		<span class="box_btn_s icon delete"><input type="button" value="선택삭제" onclick="delBBS('<?=$cfg['use_trash_bbs']?>');"></span>
		<div class="right_area">
			<span class="box_btn blue"><input type="button" value="게시물 등록" onclick="goM('board@content_write');"></span>
		</div>
	</div>
	<div class="box_bottom">
		<?=$pg_res?>
	</div>
</form>

<script type="text/javascript">
	var f=document.prdFrm;
	function delBBS(is_trash){
		if(!checkCB(f.check_pno,"삭제할 게시물을 선택해주세요.")) return;
		var msg = (is_trash == 'Y') ?
			'선택한 게시물을 휴지통으로 이동시키겠습니까?\n휴지통에 이동된 게시물은 설정 된 기간 경과 후 자동으로 영구삭제 됩니다.' :
			'선택하신 게시물을 삭제하시겠습니까?';
		if(!confirm(msg)) return;
		f.exec.value='delete';
		f.body.value='board@content.exe';
		f.target=hid_frame;
		f.method='post';
		f.submit();
	}
	function moveArticle(){
		if(!checkCB(f.check_pno,"이동할 게시물을 선택해주세요.")) return;
		if (!confirm('\n 선택하신 게시물을 이동하시겠습니까?            \n')) return;
		f.exec.value='move';
		f.body.value='board@content.exe';
		f.target=hid_frame;
		f.method='post';
		f.submit();
	}
	function copyArticle(){
		if(!checkCB(f.check_pno,"복사할 게시물을 선택해주세요.")) return;
		if (!confirm('답변글은 복사되지 않습니다.\n선택하신 게시물을 복사하시겠습니까?\n')) return;
		f.exec.value='copy';
		f.body.value='board@content.exe';
		f.target=hid_frame;
		f.method='post';
		f.submit();
	}
</script>