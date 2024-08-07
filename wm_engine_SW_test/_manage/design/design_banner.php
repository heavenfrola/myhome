<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  배너 관리
	' +----------------------------------------------------------------------------------------------+*/

    // 사용 스킨 및 편집 스킨
    $_cur_skin = $_edt_skin = $_skinname = array();
    if (file_exists($root_dir.'/_skin/config.cfg') == true) { // pc 스킨
        include $root_dir.'/_skin/config.cfg';
        $_cur_skin[] = $design['skin'];
        $_edt_skin[] = $design['edit_skin'];
    }
    if (file_exists($root_dir.'/_skin/mconfig.cfg') == true) { // 모바일 스킨
        include $root_dir.'/_skin/mconfig.cfg';
        $_cur_skin[] = $design['skin'];
        $_edt_skin[] = $design['edit_skin'];
    }

    //  스킨 목록 읽기
    $def = ''; // 기본 값
    $_source = array(
        '' => '공통배너',
    );
    $_source_pc = $_source_m = array();
    $dir = opendir($root_dir.'/_skin');
    while($dirname = readdir($dir)) {
        if (file_exists($root_dir.'/_skin/'.$dirname.'/skin_config.cfg') == true) {
            $_skinname[] = $dirname;
        }
    }
    sort($_skinname);
    foreach ($_skinname as $dirname) {
        $_source[$dirname] = $dirname;
        if (preg_match('/^m_/', $dirname) == true) {
            $_source_m[] = $dirname;
        } else {
            $_source_pc[] = $dirname;
        }

    }
    $source = (isset($_GET['source']) == false || isset($_source[$_GET['source']]) == false) ? $def : $_GET['source'];
    unset($_source[$source]);

	//배너 시작일,종료일 컬럼 추가
	addField($tbl['banner'], "start_date", "varchar(13) NOT NULL DEFAULT '2016-01-01-00' COMMENT '시작일'");
	addField($tbl['banner'], "finish_date", "varchar(13) NOT NULL DEFAULT '2037-12-31-23' COMMENT '종료일'");

	$bn = $_GET['bn'];
	$pgCode = $_GET['pgCode'];
	if(!$bn) $bn = 1;

	if($cfg['design_version'] == "V3"){
        if ($source == '') {
    		$code1 = "{{\$사용자배너";
        } else {
    		$code1 = '{{$스킨배너';
        }
		$code2 = "}}";
	}else{
		$code1 = "&lt;?=disBanner(";
		$code2 = ")?&gt;";
	}

	$file_url = getFileDir("_data/banner");

	$no = numberOnly($_GET['no']);
	$exec = $_GET['exec'];
	if(!$no && $exec != 'upload') { // 리스트 페이지
		// 검색
		$_search_type = array(
			'name' => '배너명',
		);

        $search_str = trim($_GET['search_str']);
		$_search_str = addslashes($search_str);
		if($_search_str && array_key_exists($_GET['search_type'], $_search_type)) {
			$_bnq .= " and name like '%$_search_str%'";
		}
		$use_banner = addslashes($_GET['use_banner']);
		if($_GET['use_banner']) {
			$use_q .= " and use_banner='$use_banner'";
			$_bnq .= $use_q;
		}

		$big = numberOnly($_GET['big']);
		$mid = numberOnly($_GET['mid']);
		$small = numberOnly($_GET['small']);
		$depth4 = numberOnly($_GET['depth4']);
		if($depth4 > 0) $_bnq .= " and depth4='$depth4'";
		else if($small > 0) $_bnq .= " and small='$small'";
		else if($mid > 0) $_bnq .= " and mid='$mid'";
		else if($big > 0) $_bnq .= " and big='$big'";

		// 정렬
		$_sort_type = array(
			1 => '등록일↑',
			2 => '등록일↓',
			3 => '배너명순',
		);
		$sort_type = numberOnly($_GET['sort_type']);
		if(!$sort_type) $sort_type = 1;
		switch($sort_type) {
			case 1 : $order_by = " no desc"; break;
			case 2 : $order_by = " no asc"; break;
			case 3 : $order_by = " name asc"; break;
		}

		$NumTotalRec = @$pdo->row("select count(*) from {$tbl['banner']} where 1 $_bnq");

        setListURL('banner');

		$_cate_cache = array();
		$cres = $pdo->iterator("select no, name from {$tbl['category']} where ctype=10");
        foreach ($cres as $cdata) {
			$_cate_cache[$cdata['no']] = stripslashes($cdata['name']);
		}

		$list_tab_qry = preg_replace("/(\?|&)use_banner=.?/", '', getURL());
		${'list_tab_active'.$use_banner} = 'class="active"';

        // 상품 리스트 및 탭 갯수
        if ($source == '') { // 공통 배너
            $q = str_replace($use_q, '', $_bnq);
            $cnt_qry = "select use_banner, count(*) as cnt from {$tbl['banner']} where 1 $q group by use_banner";
            $cntres = $pdo->iterator($cnt_qry);
            foreach ($cntres as $tmp) {
                $cnt[$tmp['use_banner']] = $tmp['cnt'];
                $cnt['total'] += $tmp['cnt'];
            }

            $sql = $pdo->iterator("select * from {$tbl['banner']} where 1 $_bnq order by $order_by");
        } else { // 스킨 배너
            getSkinBanner($source);
            $sql = array();
            foreach ($skinbanner_cfg as $idx => $tmp) {
                // 검색
                if ($depth4 > 0 && $depth4 != $tmp['depth4']) continue;
                else if ($small > 0 && $small != $tmp['small']) continue;
                else if ($mid > 0 && $mid != $tmp['mid']) continue;
                else if ($big > 0 && $big != $tmp['big']) continue;
                if ($search_str && strstr($tmp['name'], $search_str) == false) continue;

                $sql[$idx] = getSkinBanner($source, $idx);

                $cnt[$tmp['use_banner']] += 1;
                $cnt['total']++;
            }
        }

        if ($body == 'design@design_banner.exe') return;
	} else { // 등록, 수정 페이지
        if ($source == '') {
            $data = $pdo->assoc("select * from {$tbl['banner']} where no='$no'");
            $data['src_local'] = getListImgURL($data['updir'], $data['upfile1']);
        } else {
            if ($no > 0) {
                $data = getSkinBanner($source, $no);
            } else {
                getSkinBanner($source);
                $banner_no = array();
                foreach ($skinbanner_cfg as $key => $val) {
                    $banner_no[] = $key;
                }
            }
            $data['src_local'] = getListImgURL($data['updir'], $data['upfile1']);
            $data['src_local2'] = getListImgURL($data['updir'], $data['upfile2']);
        }
		$big = $data['big'];
		$mid = $data['mid'];
		$small = $data['small'];
		$depth4 = $data['depth4'];
	}

	if($big) $cw .= " or (`level`= '2' and `big` = '$big')";
	if($mid) $cw .= " or (`level`= '3' and `mid` = '$mid')";
	if($small) $cw .= " or (`level`= '4' and `small` = '$small')";
	$cres = $pdo->iterator("select no, name, level from {$tbl['category']} where ctype=10 and (`level` = '1' $cw ) order by level asc, sort asc");
    foreach ($cres as $cate) {
		$cl = $_cate_colname[1][$cate['level']];
		$sel = (${$cl} == $cate['no']) ? 'selected' : '';
		${'cate_'.$cate['level']} .= "<option value='{$cate['no']}' $sel>".stripslashes($cate['name'])."</option>";
	}

?>
<?php if (!$no && $exec != 'upload') { ?>
<form method="get" action="./index.php">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">

	<div class="box_title first">
		<h2 class="title">배너 관리</h2>
	</div>

	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_type, 'search_type', 2, '', $search_type)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">배너 검색</caption>
			<colgroup>
				<col style="width:15%;">
				<col>
			</colgroup>
            <tr>
                <th scope="row" rowspan="3">스킨 선택</th>
                <td>
                    <ul class="list_quarter">
                        <li><label><input type="radio" name="source" value="" <?=checked('', $source)?>> 모든스킨</label></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <ul class="list_quarter">
                        <?php foreach ($_source_pc as $skinname) { ?>
                        <li>
                            <label><input type="radio" name="source" value="<?=$skinname?>" <?=checked($skinname, $source)?>> <?=$skinname?></label>
                            <?php if (in_array($skinname, $_cur_skin) == true) { ?>
                            <span class="box_highlight">사용</span>
                            <?php } ?>
                        </li>
                        <?php } ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <ul class="list_quarter">
                        <?php foreach ($_source_m as $skinname) { ?>
                        <li>
                            <label><input type="radio" name="source" value="<?=$skinname?>" <?=checked($skinname, $source)?>> <?=$skinname?></label>
                            <?php if (in_array($skinname, $_cur_skin) == true) { ?>
                            <span class="box_highlight">사용</span>
                            <?php } ?>
                        </li>
                        <?php } ?>
                    </ul>
                </td>
            </tr>
			<tr>
				<th scope="row">배너분류</th>
				<td>
					<select name="big" onchange="chgCateInfinite(this, 2, '')">
						<option value="">::대분류::</option>
						<?=$cate_1?>
					</select>
					<select name="mid" onchange="chgCateInfinite(this, 3, '')">
						<option value="">::중분류::</option>
						<?=$cate_2?>
					</select>
					<select name="small" onchange="chgCateInfinite(this, 4, '')">
						<option value="">::소분류::</option>
						<?=$cate_3?>
					</select>
					<?php if ($cfg['max_cate_depth'] >= 4) { ?>
					<select name="depth4">
						<option value="">::세분류::</option>
						<?=$cate_4?>
					</select>
					<?php } ?>
				</td>
			</tr>
			<tr class="order_cell">
				<th scope="row">정렬순서</th>
				<td>
					<?=selectArray($_sort_type, 'sort_type', 2, '', $sort_type)?>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&type=<?=$type?>'"></span>
		</div>
	</div>
</form>
<!-- 검색 총합 -->

<?php } ?>
<!-- //검색 총합 -->
<form method="post" name="bnn_frm" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="return ckBanner(this);">
	<input type="hidden" name="body" value="design@design_banner.exe">
	<input type="hidden" name="bn" value="<?=$bn?>">
	<input type="hidden" name="pgCode" value="<?=$pgCode?>">
	<input type="hidden" name="start_date"  value="" />
	<input type="hidden" name="finish_date" value="" />
	<input type="hidden" name="exec">
	<input type="hidden" name="source" value="<?=$source?>">
	<?PHP
		if($no != null || $exec == 'upload'){
			if($exec == 'upload'){
                if ($source == '') {
                    $data['no'] = $pdo->row("select max(`no`)+1 from {$tbl['banner']}");
                    if(!$data['no']) $data['no'] = 1;
                } else {
                    $data['no'] = (is_array($banner_no) == true && count($banner_no) > 0) ? max($banner_no)+1 : 1;
                }
				$dateSH=0;
				$dateFH=23;
				$dateSM=0;
				$dateFM=59;
			}else{
				if($data['no'] == null) msg('해당 배너가 더이상 존재하지 않습니다.', 'back');
				if($data['obj_type'] == 4) {
					$data['content'] = $data['maptext'];
					$data['maptext'] = '';
				}

				$begin =explode("-",$data['start_date']);
				$finish=explode("-",$data['finish_date']);
				$dateSH = $begin[3] ? $begin[3] : 0;
				$dateFH = $finish[3] ? $finish[3] : 23;
				$dateSM = $begin[4] ? $begin[4] : 0;
				$dateFM = $finish[4] ? $finish[4] : 59;
			}
			if(!$data['start_date'] && !$data['finish_date']) $use_date = 'N';

	?>
	<input type="hidden" name="no" value="<?=$data['no']?>">
	<input type="hidden" name="source" value="<?=$source?>">
	<div class="box_title first">
		<h2 class="title">배너 관리</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">배너 관리</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">코드</th>
			<td><?=$code1.$data['no'].$code2?></td>
		</tr>
		<tr>
			<th scope="row">배너 명</th>
			<td><input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_full" maxlength="50"></td>
		</tr>
        <?php if (!$_GET['no']) { ?>
        <tr>
            <th scope="row" rowspan="3">스킨 선택</th>
            <td>
                <ul class="list_quarter">
                    <li><label><input type="radio" name="source" value="" <?=checked('', $source)?>> 모든 스킨</label></li>
                </ul>
            </td>
        </tr>
        <tr>
            <td>
                <ul class="list_quarter">
                    <?php foreach ($_source_pc as $skinname) { ?>
                    <li>
                        <label><input type="radio" name="source" value="<?=$skinname?>" <?=checked($skinname, $source)?>> <?=$skinname?></label>
                        <?php if (in_array($skinname, $_cur_skin) == true) { ?>
                        <span class="box_highlight">사용</span>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ul>
            </td>
        </tr>
        <tr>
            <td>
                <ul class="list_quarter">
                    <?php foreach ($_source_m as $skinname) { ?>
                    <li>
                        <label><input type="radio" name="source" value="<?=$skinname?>" <?=checked($skinname, $source)?>> <?=$skinname?></label>
                        <?php if (in_array($skinname, $_cur_skin) == true) { ?>
                        <span class="box_highlight">사용</span>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ul>
            </td>
        </tr>
        <?php } ?>
		<tr>
			<th scope="row">배너분류</th>
			<td>
				<select name="big" onchange="chgCateInfinite(this, 2, '')">
					<option value="">::대분류::</option>
					<?=$cate_1?>
				</select>
				<select name="mid" onchange="chgCateInfinite(this, 3, '')">
					<option value="">::중분류::</option>
					<?=$cate_2?>
				</select>
				<select name="small" onchange="chgCateInfinite(this, 4, '')">
					<option value="">::소분류::</option>
					<?=$cate_3?>
				</select>
				<?php if ($cfg['max_cate_depth'] >= 4) { ?>
				<select name="depth4">
					<option value="">::세분류::</option>
					<?=$cate_4?>
				</select>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">기간</th>
			<td>
				<label><input type="checkbox" name="use_date" value="N" <?=checked($use_date, 'N')?> onclick="dateUnlimit(this)"> 무제한</label>
			</td>
		</tr>
		<tr>
			<td>
				<input type="text" name="start_date_day" value="<?=subStr($data['start_date'],0,10)?>" size="10" readonly class="input datepicker">
				<?=dateSelectBox(0,23,"start_date_h",$dateSH)?> 시 <?=dateSelectBox(0,59,"start_date_m",$dateSM)?> 분 ~
				<input type="text" name="finish_date_day" value="<?=subStr($data['finish_date'],0,10)?>" size="10" readonly class="input datepicker"> <?=dateSelectBox(0,23,"finish_date_h",$dateFH)?> 시 <?=dateSelectBox(0,59,"finish_date_m",$dateFM)?> 분
			</td>
		</tr>
		<tr>
			<th scope="row">사용 여부</th>
			<td>
				 <label class="p_cursor"><input type="radio" name="use_banner" value="Y" <?=checked($data['use_banner'],"Y").checked($data['use_banner'],"")?>> 사용함</label>
				 <label class="p_cursor"><input type="radio" name="use_banner" value="N" <?=checked($data['use_banner'],"N")?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">링크</th>
			<td>
				<select name="link_type" onchange="showmap('<?=$key?>', this.value);" style="width:130px;">
					<option value="1" <?=checked($data['link_type'],1,1)?>>URL직접기입</option>
					<option value="2" <?=checked($data['link_type'],2,1)?>>상품</option>
					<option value="3" <?=checked($data['link_type'],3,1)?>>분류</option>
					<option value="4" <?=checked($data['link_type'],4,1)?>>이미지맵</option>
				</select>
				<input type="text" name="link[]" value="<?=htmlspecialchars($data['link']);?>" class="input input_full">
				<span id="prdpop" style="display:<?=($data['link_type'] == 2) ? "inline" : "none";?>">
					<div class="box_btn_s" style="display:inline;"><input type="button" value="상품선택" onClick="psearch.open();"></div>
				</span>
				<div id="mapid" style="display:<?=($data['link_type'] == 4) ? "inline" : "none";?>">
					<span class="box_btn_s" style="margin:5px 0;"><input type="button" value="맵생성하기" onClick="crtmap(this.form);"></span><br>
					<textarea name="maptext" class="txta input_txta"><?=$data['maptext']?></textarea>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">링크 타겟</th>
			<td>
				 <select name="target" style="width:130px;">
					<option value="" <?=checked($data['target'],"",1)?>>같은 창</option>
					<option value="_blank" <?=checked($data['target'],"_blank",1)?>>새창</option>
					<option value="_parent" <?=checked($data['target'],"_parent",1)?>>부모</option>
					<option value="_top" <?=checked($data['target'],"_top",1)?>>최상</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">배너 타입</th>
			<td>
				<select name="obj_type" onChange="displayObj(this.value, this.form)" style="width:130px;">
					<option value="1" <?=checked($data['obj_type'],1,1)?>>이미지</option>
					<option value="3" <?=checked($data['obj_type'],3,1)?>>이미지오버</option>
					<option value="4" <?=checked($data['obj_type'],4,1)?>>텍스트</option>
				</select>
			</td>
		</tr>
		<tr class="browsefile">
			<th scope="row">파일 업로드</th>
			<td>
				<?php if($data['upfile1']) { echo "<span class=\"box_btn_s\"><a href=\"{$data['src_local']}\" target=\"_blank\">기존파일</a></span>"; } ?>
				일반 파일 : <input type="file" name="upfile1" class="input input_full"><br>
				<div style="display:<?=($data['obj_type'] == 3) ? "block" : "none";?>" id="bannerLay">
				<?php if($data['upfile2']){ echo "<span class=\"box_btn_s\"><a href=\"{$data['src_local2']}\" target=\"_blank\">기존파일</a></span>"; } ?>
				오버 파일 : <input type="file" name="upfile2" class="input input_full">
				</div>
			</td>
		</tr>
		<tr class="textinput" style="display:none;">
			<th scope="row">텍스트입력</th>
			<td>
				<textarea name="content" class="input input_txta"><?=stripslashes($data['content'])?></textarea>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><a href="<?=getListURL('banner')?>">취소</a></span>
	</div>

	<?php } else { ?>

	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체<span><?=number_format($cnt['total'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&use_banner=Y" <?=$list_tab_activeY?>>사용<span class="cnt_use_Y"><?=number_format($cnt['Y'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&use_banner=N" <?=$list_tab_activeN?>>미사용<span class="cnt_use_N"><?=number_format($cnt['N'])?></span></a></li>
		</ul>
	</div>
	<div class="box_sort">

	</div>
	<table class="tbl_col">
		<colgroup>
			<col style="width:50px">
			<col style="width:200px">
			<col>
			<col>
			<col style="width:80px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.bnn_frm.no,this.checked)"></th>
				<th scope="col">코드</th>
				<th scope="col">분류</th>
				<th scope="col">배너명</th>
				<th scope="col">사용</th>
				<th scope="col">미리보기</th>
			</tr>
		</thead>
		<tbody>
			<?PHP
                foreach ($sql as $key => $data) {
                    if ($source == 'skin') $data['no'] = $key;
					if(!$data['use_banner']) $data['use_banner'] = 'Y';
					if($data['updir'] && $data['upfile1']) {
						$file = getListImgURL($data['updir'], $data['upfile1']);
					} else {
						$file = '';
					}
					$cstr = $_cate_cache[$data['big']];
					if($data['mid'] > 0) $cstr .= ' > '.$_cate_cache[$data['mid']];
					if($data['small'] > 0) $cstr .= ' > '.$_cate_cache[$data['small']];

					$use_on = ($data['use_banner'] == 'Y') ? 'on' : '';
					$expired = ($data['finish_date'] && strtotime($data['finish_date']) < $now) ? 'Y' : '';
			?>
			<tr>
				<td><input type="checkbox" name="no[]" id="no"class="list_check" value="<?=$data['no']?>"></td>
				<td><?=$code1.$data['no'].$code2?></td>
				<td class="left"><?=$cstr?></td>
				<td class="left"><a href="?body=<?=$body?>&no=<?=$data['no']?>&source=<?=$source?>"><strong><?=$data['name']?></strong></a></td>
				<td>
					<div class="switch <?=$use_on?>" onclick="toggleUseBanner(<?=$data['no']?>, $(this), '<?=$source?>')" data-expired="<?=$expired?>"></div>
				</td>
				<td>
					<?php if ($file) { ?>
					<a href="<?=$file?>" target="_blank"><img src="<?=$file?>" style="height: 50px; max-width: 100px;"></a>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div id="btn_bottom" class="box_bottom">
		<span class="box_btn blue"><input type="button" value="추가" onclick="location.href='./?body=<?=$body?>&exec=upload&pgCode=<?=$pgCode?>&bn=<?=$bn?>&source=<?=$source?>';"></span>
		<?php if ($NumTotalRec) { ?>
		<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="delBanner();"></span>
		<?php } ?>
	</div>
	<?PHP
		preg_match('/MSIE ([0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $agent);
		settype($agent[1], 'integer');
		if($agent[1] > 6 || $agent[1] == 0) {
	?>
	<div id="fastBtn">
		<span class="box_btn blue"><input type="button" value="추가" onclick="location.href='./?body=<?=$body?>&exec=upload&pgCode=<?=$pgCode?>&bn=<?=$bn?>&source=<?=$source?>';"></span>
		<?php if ($NumTotalRec) { ?>
		<span class="box_btn gray"><input type="button" value="선택 삭제" onclick="delBanner();"></span>
		<?php } ?>
	</div>
	<?php } ?>

	<?php } ?>
</form>

<?php if (!$no && $exec != 'upload') { ?>
<div id="controlTab">
    <ul class="tabs">
        <li class="selected">배너 복사</li>
    </ul>

    <!-- 배너 복사 -->
    <div class="context">
        <form method="post" onsubmit="return copyBanner(this)">
            <input type="hidden" name="body" value="design@design_banner.exe">
            <input type="hidden" name="exec" value="copy">
            <input type="hidden" name="source" value="<?=$source?>">
            <input type="hidden" name="selected" value="">

            <div class="box_middle3 left">
                <select name="cpmode">
                    <option value="1">선택한 배너를</option>
                    <option value="2">검색된 모든 배너를</option>
                </select>
            </div>
            <table class="tbl_row tbl_row2">
                <colgroup>
                    <col style="width:15%;">
                    <col>
                </colgroup>
                <tbody>
                    <tr>
                        <th scope="row"> 배너 복사</th>
                        <td>
                            <?=selectArray($_source, 'target', false)?> 스킨으로 복사합니다.
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="box_bottom">
                <span class="box_btn blue"><input type="submit" value="확인"></span>
            </div>
        </form>
    </div>
    <!-- 배너 복사 -->
</div>
<?php } ?>

<script type="text/javascript">
	// FIXED 슬라이드 저장버튼
	function refineFastBtn() {
		$('#fastBtn').css('left', $('#contentArea').css('margin-left')).width($('#contentTop').innerWidth()+100);
	}

	function toggleFastBtn() {
		var doc = document.documentElement.scrollTop > document.body.scrollTop ? document.documentElement : document.body;
		var fastBtn = $('#fastBtn');

		if(fastBtn.css('opacity') == 1) fastBtn.css('opacity', '.8');

		if(doc.scrollTop > $('#btn_bottom').offset().top-$(window).height()) {
			if(fastBtn.css('opacity') > 0) {
				fastBtn.animate({"opacity":"0"}, {"queue":false}).css('display','none');
			}
		} else {
			if(fastBtn.css('opacity') == 0) {
				fastBtn.animate({"opacity":".8"}, {"queue":false}).css('display','');
			}
		}
	}

	if($('#fastBtn').length > 0) {
		$(document).ready(function() {
			toggleFastBtn();
			refineFastBtn();

			$('#contentArea').change(refineFastBtn);
		});

		$(window).bind({
			"resize": refineFastBtn,
			"scroll": toggleFastBtn
		});
	}

	function ckBanner(f){
		if(!checkBlank(f.name,"배너명을 입력해주세요.")) return false;
		if(f.use_date.checked != true) {
			if(!checkBlank(f.start_date_day,'시작일을 입력해주세요.')) return false;
			if(!checkBlank(f.finish_date_day,'종료일을 입력해주세요.')) return false;
			f.start_date.value=f.start_date_day.value+"-"+f.start_date_h.value+"-"+f.start_date_m.value;
			f.finish_date.value=f.finish_date_day.value+"-"+f.finish_date_h.value+"-"+f.finish_date_m.value;
		}
		if(f.start_date.value > f.finish_date.value) {
			alert('시작일은 종료일 이전이어야합니다.');
			return false;
		}
        printLoading();
	}

	function displayObj(val, f) {
		var idx = idx;
		if(val == 2 && f.link_type.value == 4){
			alert("이미지맵일 경우는 플래시를 사용할 수 없습니다.");
			f.obj_type.value = 1;
			return;
		}

		var smenu = $('#bannerLay');
		if(val == '3') $(smenu).show();
		else $(smenu).hide();

		if(val == 4) {
			$('.textinput').show();
			$('.browsefile').hide();
			$('#mapid').hide();
			$(f.link_type).find('[value=4]').attr('disabled', true);
		} else {
			$('.textinput').hide();
			$('.browsefile').show();
			$(f.link_type).find('[value=4]').attr('disabled', false);
		}
	}
	var f = document.getElementsByName('bnn_frm')[0];
	if(f.obj_type) {
		displayObj(f.obj_type.value, f);
	}

	function showmap(key, val){
		obj = $('#mapid');
		obj2 = $('#prdpop');

		if(val == 4) $(obj).show();
		else $(obj).hide();
		if(val == 2) $(obj2).show();
		else $(obj2).hide();
	}

	function crtmap(f){
		var mnm = "umbanner_<?=$data['no']?>";
		f["link[]"].value = mnm;
		var tgt = f.target.value;
		var mtext = "<map name=\""+mnm+"\" id=\""+mnm+"\">\n\
	<area shape=\"\" href=\"\" coords=\"\" target=\""+tgt+"\">\n\
	</map>";
		f.maptext.value = mtext;
	}

    var psearch = new layerWindow('product@product_inc.exe');
    psearch.psel = function(pno)
    {
        $('input[name="link[]"]').val(pno);
        this.close();
    }

	function delBanner(){
		f = document.bnn_frm;
		if(!checkCB(f["no[]"], "삭제하실 배너를 선택해주세요.")) return;
		if(!confirm("선택하신 배너를 삭제하시겠습니까?")) return;
		f.exec.value = "delete";
		f.submit();
	}

	function dateUnlimit(o) {
		var f = o.form;
		if(o.checked == true) {
			f.start_date_day.value = '';
			f.finish_date_day.value = '';
		}
	}

	function toggleUseBanner(no, o, source) {
		$.post('?body=design@design_banner.exe', {'exec':'toggle', 'no':no, 'cnt_qry':"<?=base64_encode($cnt_qry)?>", 'source': source, 'accept_json': 'Y'}, function(r) {
			if(r.changed == 'Y') {
				if(o.attr('data-expired') == 'Y') {
					window.alert('종료일이 지난 배너를 사용함으로 설정하셨습니다.');
				}
				o.addClass('on');
                Y = 1;
			} else {
				o.removeClass('on');
                Y = -1;
			}

            $('.cnt_use_Y').html(parseInt($('.cnt_use_Y').text())+Y)
            $('.cnt_use_N').html(parseInt($('.cnt_use_N').text())-Y)
		});
	}

    (chgSource = function() {
        var o = $('select[name=source]');
        if (o.val() == '') {
            $('.order_cell').show();
        } else {
            $('.order_cell').hide();
        }
    })();
    $('select[name=source]').change(chgSource);

    // 스킨 복사
    function copyBanner(f) {
        if (f.cpmode.value == '1') {
            let selected = '';
            $('.list_check:checked').each(function() {
                if (selected) selected += ',';
                selected += this.value;
            });
            if (selected == '') {
                window.alert('복사할 배너를 선택해주세요.');
                return false;
            }
            f.selected.value = selected;
        }

        printLoading();
        $.post('./index.php', $(f).serialize(), function(r) {
            location.href = '?body=design@design_banner&source='+f.target.value;
        });
        return false;
    }
</script>