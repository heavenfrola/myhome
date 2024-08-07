<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 데이터 다운로드
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$db = addslashes($_GET['db']);
	$no = numberOnly($_GET['no']);
	$idx = numberOnly($_GET['idx']);

	$data = $pdo->assoc("select * from `mari_board` where `db`='$db' and `no`='$no'");
	if(!$data['no']) msg('존재하지 않는 게시물입니다.');

	$config = $pdo->assoc("select auth_view from `mari_config` where db='$db'");
	if($config['auth_view'] < $member['level']) msg('다운로드 권한이 없습니다.');

	$updir = $data['up_dir'];
	$upfile = $data['upfile'.$idx];
	$ori_upfile = $data['ori_upfile'.$idx];
	$filepath = $root_dir.'/board/'.$updir.'/'.$upfile;
	$filesize = filesize($filepath);
	$ext = strtolower(getExt($upfile));


	// 권한및 보안체크
	if(!$updir || !$upfile) msg('업로드된 파일이 없습니다.');
	if($filesize < 1) msg('정상적인 파일이 아닙니다.');
	if(in_array($ext, array('php', 'wisa', 'html', 'htm', 'php3', 'js'))) msg('다운로드 권한이 없습니다.');
	if($data['secret'] == 'Y' && $member['level'] > 1 && $member['no'] != $data['member_no']) msg('다운로드 권한이 없습니다.');


	// 다운로드 실행
	Header("Content-Type: file/unknown");
	Header("Content-Disposition: attachment; filename=".$ori_upfile);
	Header("Content-Length: ".$filesize);
	header("Content-Transfer-Encoding: binary ");
	Header("Pragma: no-cache");
	Header("Expires: 0");
	flush();

	if($fp = fopen($filepath, "r")) {
		echo fread($fp, $filesize);
	}
	fclose($fp);

?>