<?PHP

	$updir=$dir['upload']."/".$dir['icon'];

	$ino = numberOnly($_POST["ino"]);
	$itype = addslashes(trim($_POST['itype']));
	$imode = $_POST['imode'];
	$exec = $_POST['exec'];

	if($exec=="delete" && $ino) {
		$data=get_info($tbl['product_icon'],"no",$ino);
		if($data[no]) {
			$sql="delete from `".$tbl['product_icon']."` where `no`='$ino'";
			$pdo->query($sql);

			$filename=$root_dir."/".$updir."/".$data[upfile];
			deleteattachfile($updir,$data[upfile]);
			if(!$itype) {
				$sql="update `$tbl[product]` set `icons`=if(replace(icons,'@$ino@','')='', '', replace(`icons`,'@$ino@','@')) where `icons` like '%@$ino%'";
				$pdo->query($sql);
				$ems="아이콘이 삭제되었습니다";
			}
		}
	}

	if($exec=="sort") {
		if(!$_POST['icon_sort']) return;
		$_icon_sort = explode(',', $_POST['icon_sort']);
		$_icon_sort = numberOnly($_icon_sort);
		foreach($_icon_sort as $key=>$val) {
			if($val) {
				$pdo->query("update $tbl[product_icon] set sort='$key' where no='$val'");
			}
		}
		echo "OK";
		exit;
	}

	if($_FILES['upfile0']) $_FILES['upfile'] = $_FILES['upfile0'];
	if($_FILES["upfile"]["tmp_name"]) {
		include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
		wingUploadRule($_FILES['upfile'], 'prdIcon');

		if($exec=="modify" && $ino) {
			$up_filename = $pdo->row("select `upfile` from `{$tbl['product_icon']}` where `no` = '{$ino}'");
			deleteattachfile($updir,$up_filename);
			$ext = getExt($up_filename);
			$up_filename = md5($now);
		}
		else $up_filename=md5($now);

		makeFullDir($updir);
		$up_info=uploadFile($_FILES["upfile"],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");

		if($exec=="modify" && $ino) {
			$sql="UPDATE `{$tbl['product_icon']}` SET `upfile` = '$up_info[0]' where `no` = '{$ino}'";
			$ems="아이콘이 수정되었습니다";

		}else{
			$pdo->query("update $tbl[product_icon] set sort=sort+1 where itype=''");
			$sql="INSERT INTO `".$tbl['product_icon']."` ( `upfile` , `reg_date` , `itype`, `sort`) VALUES ( '$up_info[0]', '$now', '$itype', '0')";
			$ems="아이콘이 추가되었습니다";
		}
		$pdo->query($sql);
	}

	if($imode) msg($ems,"reload","parent");

?>
<script type="text/javascript">
parent.location.reload();
</script>