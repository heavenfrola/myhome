<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	@extract($_GET);
	@extract($_POST);
	@extract($_SERVER);

	// Return
	$PayMethod      = $PayMethod;           //���Ҽ���
	$M_ID           = $MID;                 //����ID
	$MallUserID     = $MallUserID;          //ȸ���� ID
	$Amt            = $Amt;                 //�ݾ�
	$name           = $name;                //�����ڸ�
	$GoodsName      = $GoodsName;           //��ǰ��
	$TID            = $TID;                 //�ŷ���ȣ
	$MOID           = $MOID;                //�ֹ���ȣ
	$AuthDate       = $AuthDate;            //�Ա��Ͻ� (yyMMddHHmmss)
	$ResultCode     = $ResultCode;          //����ڵ� ('4110' ��� �Ա��뺸)
	$ResultMsg      = $ResultMsg;           //����޽���
	$VbankNum       = $VbankNum;            //������¹�ȣ
	$FnCd           = $FnCd;                //������� �����ڵ�
	$VbankName      = $VbankName;           //������� �����
	$VbankInputName = $VbankInputName;      //�Ա��� ��
	$CancelDate     = $CancelDate;          //����Ͻ�

	//�������ä���� ���ݿ����� �ڵ��߱޽�û�� �Ǿ������ ���޵Ǹ�
	//RcptTID �� ���� �ִ°�츸 �߱�ó�� ��
	$RcptTID        = $RcptTID;             //���ݿ����� �ŷ���ȣ
	$RcptType       = $RcptType;            //���� ������ ����(0:�̹���, 1:�ҵ������, 2:����������)
	$RcptAuthCode   = $RcptAuthCode;        //���ݿ����� ���ι�ȣ

	makePGLog($MOID, 'nicepay vbank start', print_r($_REQUEST, true));

	if($ResultCode != '4110') exit("FAIL\n");

	//������ DBó��
	$ono = addslashes($MOID);
	$ord = $pdo->assoc("select ono, stat, pay_type, buyer_name, buyer_cell, pay_prc from {$tbl['order']} where ono='$ono'");
	$card = $pdo->assoc("select tno from {$tbl['vbank']} where wm_ono='$ono'");

	if($ord['stat'] != 1) exit("OK\n");
	if($card['tno'] != $TID) exit("FAIL\n");

	$erp_auto_input = 'Y'; // ��� ���ڶ� ��� ��� Ȯ�� ���·� ����
	if(orderStock($ono, 1, 2)) exit("OK\n");

	// �ֹ� ���� ����
	$pdo->query("update {$tbl['vbank']} set stat='2' where wm_ono='$ono'");
	$pdo->query("update {$tbl['order_product']} set stat='2' where ono='$ono'");
	ordChgPart($ono);
	ordStatLogw($ono, 2, 'Y');

	// �Ա�Ȯ�� SMS
	include_once $engine_dir.'/_engine/sms/sms_module.php';
	$sms_replace['buyer_name'] = $ord['buyer_name'];
	$sms_replace['ono'] = $ord['ono'];
	$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
	SMS_send_case(3, $ord['buyer_cell']);
	SMS_send_case(18);

	if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
		partnerSmsSend($ord['ono'], 18);
	}

	makePGLog($ono, 'nicepay vbank end');

	exit("OK\n");

?>