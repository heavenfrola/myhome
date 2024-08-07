<?PHP

	$urlfix = 'Y';
	include_once $engine_dir.'/_engine/include/common.lib.php';

	use AppleSignIn\ASDecoder;

	if(isset($_POST['authorization'])) {
		$browser_type = 'pc';
		$id_token = $_POST['authorization']['id_token'];
	} else {
		$browser_type = 'mobile';
		$id_token = $_POST['id_token'];
	}

	$clientUser = $cfg['apple_login_client_id'];
	$identityToken = $id_token;

    try {
        $appleSignInPayload = ASDecoder::getAppleSignInPayload($identityToken);
    } catch (UnexpectedValueException $e) {
        msg('로그인 연동 오류');
    }

	$email = $appleSignInPayload->getEmail();
	$user = $appleSignInPayload->getUser();

	if ($_POST['user']) {
        if (is_array($_POST['user']) == true) {
            $name = $_POST['user']['name']['lastName'].$_POST['user']['name']['firstName'];
        } else {
            $userdata = json_decode($_POST['user']);
            $name = $userdata->name->lastName.$userdata->name->firstName;
        }
	}

	//$isValid = $appleSignInPayload->verifyUser($clientUser);

    $snsrurl = $_SESSION["sns_login"]["rURL"];
    unset($_SESSION['sns_login']);
	$_SESSION["sns_login"]["rURL"] = $snsrurl;
	$_SESSION["sns_login"]["cid"] = $user;
	$_SESSION["sns_login"]["name"] = $name;
	$_SESSION["sns_login"]["email"] = $email;
	$_SESSION["sns_login"]["sns_type"] = 'AP';

	if($browser_type == 'pc') {
		if($email) exit('OK');
		else exit('Error');
	} else {
		if($email) msg('', '/member/apijoin.php');
		else msg('로그인 연동 오류', $root_url);
	}

?>