<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  위시리스트 실행
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\API\Naver\Checkout;

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	printAjaxHeader();
	checkBasic();

	$cTarget = $_POST['qd'] ? 'parent.parent' : 'parent';
	$pno = (is_array($_POST['pno']) == true) ? current($_POST['pno']) : addslashes($_POST['pno']);
	if(is_array($_POST['cno'])) $cno = array_map('numberOnly', $_POST['cno']);
	$exec = $_POST['exec'];
	if($exec != 'checkout')	{
		if($_POST['from_ajax']) {
			if(!$member['no']) {
				header('Content-type:application/json;');
				exit(json_encode(array('status'=>'faild', 'msg'=>__lang_common_error_memberOnly__)));
			}
		}
		$rURL = ($_SESSION['rURL']) ? $_SESSION['rURL']:$root_url;
		memberOnly($rURL, $cTarget);
	}
	$mwhere="and `member_no`='$member[no]'";;

	switch($exec) {
		case 'add' :
			$loop = is_array($cno) ? $cno : array($pno);
			$status = 'on';
			foreach($loop as $pno) {
				if(is_array($cno)) $pno = md5($pdo->row("select pno from $tbl[cart] where no='$pno'"));
				$wm_sc = $pdo->row("select wm_sc from $tbl[product] where hash = '$pno'");
				$wm_hash = $pdo->row("select hash from $tbl[product] where no = '$wm_sc'");	
				$pno = ($wm_sc > 0) ? $wm_hash : $pno; 
				$prd=checkPrd(addslashes($pno), false);
				$ems = getPrdBuyLevel($prd);
				if(!$ems) {
					$data = $pdo->assoc("select * from `$tbl[wish]` where `pno`='$prd[no]' and `member_no`='$member[no]'");

					if($data[no]) {
						if($_POST['from_ajax']) {
							$pdo->query("delete from $tbl[wish] where no='$data[no]'");
							$status = 'off';
						} else {
							$ems = __lang_mypage_info_alreadyWish__;
						}
					}
					else {
						$sql="INSERT INTO `$tbl[wish]` ( `pno` , `reg_date` , `member_no` ) VALUES ('$prd[no]','$now','$member[no]')";
						$pdo->query($sql);

						// 상품 증가
						ctrlPrdHit($prd[no],"hit_wish","+1");

						$ems = __lang_mypage_info_wishAdd__;
					}
				}
			}

			if($_POST['from_ajax']) {
				header('Content-type:application/json;');
				exit(json_encode(array('status'=>$status, 'msg'=>$ems)));
			}

			if(!$cTarget) $cTarget = "parent";
			if($_POST['qd']) $cTarget = 'parent.parent';
?>
<script type='text/javascript'>
if(parent.browser_type == 'mobile') {
	if(confirm('<?=$ems?>\n'+parent._lang_pack.common_confirm_wishlist)) {
		<?=$cTarget?>.location.href='<?=$root_url?>/mypage/wish_list.php';
	} else {
		location.href='about:blank';
	}
} else {
	parent.dialogConfirm(null, '<?=$ems?>\n'+parent._lang_pack.common_confirm_wishlist, {
		Ok: function() {
			<?=$cTarget?>.location.href='<?=$root_url?>/mypage/wish_list.php';
		},
		Cancel: function() {
			parent.dialogConfirmClose();
		}
	});
}
</script>
<?PHP
		break;
		case "delete" :
			$wno = numberOnly($_POST['wno']);
			$total = count($wno);
			if($wno<1) msg(__lang_mypage_error_rmWish__);

			for($ii=0; $ii<$total; $ii++) {
				$pdo->query("delete from `".$tbl['wish']."` where `no`='".numberOnly($wno[$ii])."' ".$mwhere);
			}
		break;

		case "truncate" :
			$pdo->query("delete from `".$tbl['wish']."` where 1 ".$mwhere);
		break;
		case 'checkout' :
			$checkout = new Checkout();
			$order_id = $checkout->wishlist($pno);
		break;
	}

	if($exec != 'add' && $exec != 'checkout') {
		$cTarget = $_POST['qd'] ? 'parent.parent' : 'parent';
		msg('', 'reload', $cTarget);
	}

?>