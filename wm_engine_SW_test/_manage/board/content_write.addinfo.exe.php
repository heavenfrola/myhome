<?PHP

	if(!$config) {
		$db = $data['db'] = addslashes($_GET['db']);
		$config = $pdo->assoc("select * from `mari_config` where `db` = '$db'");
	}

	if(!$config['tmp_name']) return;

	$tmp_name = unserialize(stripslashes($config['tmp_name']));
	if(is_array($tmp_name)) {
		foreach($tmp_name as $key => $val) {
			if(!$val) $val = '추가항목'.numberOnly($key);
			$tmp_name[$key] = stripslashes($val);
		}
	}

?>
<?for($i = 1; $i <= $cfg['board_add_temp']; $i++) {?>
<?if($tmp_name['temp'.$i]) {?>
<tr class="tr_addinfo">
	<th scope="row"><?=$tmp_name['temp'.$i]?></th>
	<td colspan="2">
		<textarea name="temp<?=$i?>" class="txta" style="width:80%; height: 70px;"><?=stripslashes($data['temp'.$i])?></textarea>
	</td>
</tr>
<?}?>
<?}?>