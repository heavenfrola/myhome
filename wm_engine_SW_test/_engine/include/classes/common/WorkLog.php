<?php

/**
 * 관리자 작업 로그 저장
 **/

namespace Wing\common;

require_once __ENGINE_DIR__.'/_manage/intra/work_log.inc.php';

class WorkLog {

    private $timestamp;

    public function __construct()
    {
        global $pdo, $tbl;

        $this->pdo = $pdo;
        $this->tbl = $tbl['work_log'];
        $this->timestamp = time();

        if (isTable($this->tbl) == false) {
            require __ENGINE_DIR__.'/_config/tbl_schema.php';
            $this->pdo->query($tbl_schema['work_log']);
        }
    }

    /**
     * 작업 로그 생성
     **/
    public function createLog($page, $pkey, $title_fn, $old, $new, $explain = array())
    {
        global $admin;

        // 제목
        $title = $new[$title_fn];

        // 변경된 필드 검색
        $difference = array();
        foreach ($new as $key => $val) {
            if (in_array($key, $explain) == true) continue; // 변경 체크 예외 필드
            if (strcmp($val, $old[$key]) !== 0) {
                $difference[$key] = $new[$key];
            }
        }
        if (count($difference) == 0) return false;
        $difference = json_encode_pretty($difference);

        // request 데이터 저장
        $post_args = json_encode_pretty($_POST);
        $get_args = json_encode_pretty($_GET);

        $res = $this->pdo->query("
            insert into {$this->tbl}
            (page, pkey, title, snapshot, difference, post_args, get_args, timestamp, reg_date, admin_id, admin_no, remote_addr)
            values
            (?, ?, ?, ?, ?, ?, ?, ?, now(), ?, ?, ?)
        ", array(
            $page, $pkey, $title, null, $difference, $post_args, $get_args, $this->timestamp, $admin['admin_id'], $admin['no'], $_SERVER['REMOTE_ADDR']
        ));
        return $res;
    }

    /**
     * 데이터 파싱
     **/
    public function parse(&$res)
    {
        global $tbl, $pdo, $_dic;

        $data = $res->current();
        if (!$data) return false;

        switch($data['page']) {
            case $tbl['product'] :
                $file_dir = getFileDir($data['updir']);
                $is = setImageSize($data['w3'], $data['h3'], 50, 50);
                $data['imgstr'] = "<img src='$file_dir/{$data['updir']}/{$data['upfile3']}' class='prdimgs' $is[2]>";
                $data['link'] = '?body=product@product_register&pno='.$data['pkey'];
            break;
            case $tbl['mari_board'] :
                $file_dir = getListImgURL('board/'.$data['up_dir'], $data['upfile1']);
                if ($data['upfile1']) {
                    $data['imgstr'] = "<img src='$file_dir' class='prdimgs' style='max-height: 50px'>";
                }
                $data['link'] = '?body=board@content_view&no='.$data['pkey'];
            break;
        }

        $data['title2'] = $data['title'];
        if ($data['cnt'] > 1) {
            $data['title2'] = sprintf('%s 외 <strong class="p_color2">%s건</strong>', $data['title'], (int) $data['cnt']-1);
        }

        $data['class'] = (is_null($data['no']) == true) ? 'deleted' : '';

        // 변경 내역
        $data['diff'] = array();
        $_difference = json_decode($data['difference']);
        foreach ($_difference as $key => $val) {
            $nm = (array_key_exists($key, $_dic[$data['page']]) == true) ? $_dic[$data['page']][$key] : $key;
            $val = $this->replaceData($data['page'], $key, $val);

            $data['diff'][] = array($nm, $val);
        }

        $res->next();
        return $data;
    }

    private function replaceData($page, $key, $val)
    {
        global $tbl;

        if ($page == $tbl['product']) {
            switch($key) {
                case 'stat' :
                    return $GLOBALS['_prd_stat'][$val];
                break;
                case 'del_stat' :
                    return $GLOBALS['_prd_stat'][$val];
                break;
            }
        }
        $val = htmlentities($val, null, _BASE_CHARSET_);
        if (strlen($val) == 10 && preg_match('/date/', $key) == true) { // 날짜
            $val = date('Y-m-d H:i', $val);
        } else if ($val && preg_match('/^[0-9]*\.?[0-9]*$/', $val) == true) { // 액수
            $val = parsePrice($val, true);
        }

        return $val;
    }

}