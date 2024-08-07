<?php

/**
 * SmartWing API / Common trait
 */

namespace Wing\API\Booster\Common;

Trait Common {

    protected $pdo;
    protected $tbl;
    protected $tbl_schema;
    protected $engine_dir;

    public function init()
    {
        global $pdo, $tbl, $tbl_schema, $scfg, $engine_dir;

        $this->pdo = &$pdo;
        $this->qb  = &$qb;
        $this->tbl = &$tbl;
        $this->tbl_schema = &$tbl_schema;
        $this->cfg = &$scfg;
        $this->engine_dir = &$engine_dir;
    }

    /**
     * API의 최종 실행 결과를 출력하고 세션을 종료한다.
     *
     * @param $array 출력할 내용. status=(success|error) 를 반드시 포함해야한다.
     */
    public function out(Array $array)
    {
        jsonReturn($array);
    }

    /**
     * request type이 허용 된 방식인지 체크한다.
     */
    protected function allowRequest(array $request)
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], $request)) {
            http_response_code(400);

            $this->out(array(
                'status' => 'error',
                'message' => '잘못된 요청입니다.',
                'code' => 9997
            ));
        }
        return true;
    }

    /**
     * 부관리자의 권한을 체크하고 API를 강제 중단한다.
     *
     * @param string 대분류코드
     * @param string 메뉴m코드
     **/
    protected function permission($category, $code)
    {
        global $admin;

        // 최고 관리자
        if ($admin['level'] == '1' || $admin['level'] == '2') {
            return true;
        }

        // 카테고리 체크
        $auth = explode('@', trim($admin['auth'], '@'));
        if (!in_array($category, $auth)) {
            http_response_code(405);

            $this->out(array(
                'status' => 'error',
                'message' => '접근 권한이 없습니다.',
                'category' => $category,
                'code' => 9998
            ));
        }

        // 세부 접근권한 체크
        $auth = $this->pdo->row("select {$category} from {$this->tbl['mng_auth']} where admin_no=?", array(
             $admin['no']
        ));
        $auth = explode('@', trim($auth, '@'));
        if (!in_array($code, $auth)) {
            http_response_code(405);

            $this->out(array(
                'status' => 'error',
                'message' => '접근 권한이 없습니다.',
                'mcode' => $code,
                'code' => 9998
            ));
        }
        return true;
    }

    /**
     * 문자열 escape 처리
     * html, slashes
     * @param $str
     * @return string
     */
    public function escape($str)
    {
        return addslashes(htmlspecialchars($str));
    }

    /**
     * 문자열 unescape 처리
     * html, slashes
     * @param $str
     * @return string
     */
    public function unescape($str)
    {
        return stripslashes(htmlspecialchars_decode($str));
    }



}