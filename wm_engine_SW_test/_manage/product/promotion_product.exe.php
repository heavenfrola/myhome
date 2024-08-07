<?PHP

	printAjaxHeader();

	$exec = $_REQUEST['exec'];
	if($exec=='delete') {
		$pgrp_no = numberOnly($_REQUEST['pgrp_no']);
		$prno = numberOnly($_REQUEST['prno']);
		$pdo->query("delete from $tbl[promotion_link] where pgrp_no='$pgrp_no' and prm_no='$prno'");
		exit;
	}else if($exec=='product_delete') {
		$pno = numberOnly($_REQUEST['pno']);
		$pgrp_no = numberOnly($_REQUEST['gno']);
		$pdo->query("delete from $tbl[promotion_pgrp_link] where pgrp_no='$pgrp_no' and pno='$pno'");
		exit;
	}

	$_tmp_sort = explode("|", $_REQUEST['pno']);

	if(count($_tmp_sort) < 1) {
		exit;
	}

	$gno = numberOnly($_REQUEST['gno']);
	$pgrp_nm = addslashes($_REQUEST['pgrp_nm']);
	$banner_text = addslashes($_REQUEST['banner_text']);
	$reg_date = date("Y-m-d H:i:s", $now);

	if(!$_REQUEST['pno']) {
		if($gno) {
		  $pdo->query("delete from {$tbl['promotion_pgrp_link']} where pgrp_no='$gno'");
		}
	}

	$data = $pdo->assoc("select * from $tbl[promotion_pgrp_list] where no='$gno'");

	$asql = "";
	$asql2 = "";
	$asql3 = "";
	$updir = $dir['upload']."/promotion";
	makeFullDir($updir);
	for($ii = 1; $ii <= 2; $ii++) {
		if($updir && ($_REQUEST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii]['tmp_name'])) {
			deletePrdImage($data, $ii, $ii);
			$asql.=" , `upfile".$ii."`=''";
		}
		$banner_upfile = "";
		if($_FILES['upfile'.$ii]['tmp_name']) {
			$up_filename = md5($ii+time());
			$up_info = uploadFile($_FILES['upfile'.$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp|swf|flv");
			$banner_upfile = $up_info[0];
			$asql.=" , `upfile".$ii."`='".$banner_upfile."'";
			$asql2 .= ", `upfile".$ii."`";
			$asql3 .= ", '$banner_upfile'";
		}
	}
	if($asql) {
		$asql .= " , `updir`='$updir'";
		$asql2 .= ", `updir`";
		$asql3 .= ", '$updir'";
	}

    if (fieldExist($tbl['promotion_pgrp_list'], 'link') == false) {
        addField($tbl['promotion_pgrp_list'], 'link_type', 'char(1) not null default "1"');
        addField($tbl['promotion_pgrp_list'], 'link', 'varchar(255) not null default ""');
        addField($tbl['promotion_pgrp_list'], 'target', 'enum("", "_blank", "_parent", "_top") not null default ""');
    }
    $link_type = addslashes($_REQUEST['link_type']);
    $link = addslashes($_REQUEST['link']);
    $target = addslashes($_REQUEST['target']);

	if($gno) {
        $asql .= ", link_type='$link_type', link='$link', target='$target'";
        $pdo->query("delete from {$tbl['promotion_pgrp_link']} where pgrp_no='$gno' and pno not in (".implode(',', $_tmp_sort).")");
		$pdo->query("update $tbl[promotion_pgrp_list] set `pgrp_nm`='$pgrp_nm', `banner_text`='$banner_text' $asql where no='$gno'");
	}else {
        $asql2 .= ", link_type, link, target";
        $asql3 .= ", '$link_type', '$link', '$target'";
		$pdo->query("insert into $tbl[promotion_pgrp_list] (`pgrp_nm`, `banner_text`, `admin_no`, `admin_id`, `reg_date` $asql2) VALUES ('$pgrp_nm', '$banner_text', '$admin[no]', '$admin[admin_id]', '$reg_date' $asql3)");
        $gno = $pdo->lastInsertId();
	}
	$count = 0;
	foreach($_tmp_sort as $k => $v) {
		$count++;
		$new_sort_no = $k+1;
		$oldgno = $pdo->row("select no from $tbl[promotion_pgrp_link] where `pno`='$v' and `pgrp_no`='$gno'");
		if($oldgno) {
			$pdo->query("update $tbl[promotion_pgrp_link] set `sort`='$new_sort_no' where `no`='$oldgno'");
		}else {
			$pdo->query("insert into $tbl[promotion_pgrp_link] (`pgrp_no` , `pno` , `sort`) VALUES ('$gno', '$v', '$new_sort_no')");
		}
	}

	echo $gno;
	exit();

?>