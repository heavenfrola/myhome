<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  조직도
	' +----------------------------------------------------------------------------------------------+*/

	$no1 = $_GET['no1'];
	$no2 = $_GET['no2'];
	$_staff=array();
	$_sfd=array("no", "name", "admin_id", "team1", "team2", "position", "birth", "phone", "cell", "email", "address");
	$w="";
	if($no1 || $no2){
		if($no1) $w .= " and `team1`='$no1'";
		if($no1 && $no2) $w .= " and `team2`='$no2'";
	}
	$res = $pdo->iterator("select * from `$tbl[mng]` where `level`!='1' $w order by `name`");
    foreach ($res as $data) {
		foreach($_sfd as $key=>$val){
			$_staff[$data[no]][$val]=$data[$val];
		}
	}

	$_team=getIntraTeam();
	$_team1=array();
	foreach($_team as $key=>$val){
		$ck=0;
		foreach($_staff as $key2=>$val2){
			if($_staff[$key2][team1] == $key) $ck++;
		}
		if($ck < 1) continue;
		if(!$_team[$key][ref]){
			$_team1[$key]=$_team[$key][name];
		}
	}

?>
<div class="box_title first">
	<h2 class="title">조직도</h2>
</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">조직도</caption>
	<colgroup>
		<col style="width:60px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">no</th>
			<th scope="col">성명(아이디)</th>
			<th scope="col">직급</th>
			<th scope="col">생년월일</th>
			<th scope="col">전화번호</th>
			<th scope="col">휴대폰</th>
			<th scope="col">이메일</th>
			<th scope="col">주소</th>
		</tr>
	</thead>
	<tbody>
		<?
			function opStaffs($t1, $t2){
				global $_staff, $idx, $cfg, $engine_url, $admin;
				foreach($_staff as $key=>$val){
					if(!($_staff[$key][team1] == $t1 && $_staff[$key][team2] == $t2)) continue;
					$position=($_staff[$key][position]) ? $_staff[$key][position] : "-";

					?>
					<tr>
						<td><?=$idx?></td>
						<td class="left"><?=$_staff[$key][name]?></td>
						<td><?=$position?></td>
						<td><?=$_staff[$key][birth]?></td>
						<td><?=$_staff[$key][phone]?></td>
						<td><?=$_staff[$key][cell]?></td>
						<td><a href="mailto:<?=$_staff[$key][email]?>"><?=$_staff[$key][email]?></a></td>
						<td><?=cutStr($_staff[$key][address],15, "")?></td>
					</tr>
					<?
					$idx++;
				unset($_staff[$key]);
				}
			}
			$idx=1;
			foreach($_team1 as $key=>$val){
		?>
		<tr>
			<td colspan="8" class="left"><?=$val?></td>
		</tr>
		<?
			opStaffs($key, 0);
			foreach($_team as $key2=>$val2){
				$ck=0;
				foreach($_staff as $key3=>$val3){
					if($_staff[$key3][team1] == $key && $_staff[$key3][team2] == $key2) $ck++;
				}
				if($ck < 1) continue;
				$_team[$key2][ref] = $key;
				if($_team[$key2][ref] == $key){
				?>
				<tr>
					<td colspan="8"> &nbsp; &nbsp; → <?=$_team[$key2][name]?></td>
				</tr>
				<?
					opStaffs($key, $key2);
				}
				unset($_team[$key2]);
			}

			unset($_team[$key]);
			}

			if($idx > 1 && count($_staff)) echo "<tr><td colspan=\"8\" class=\"left\">미지정</td></tr>";
			opStaffs(0, 0);
		?>
	</tbody>
</table>