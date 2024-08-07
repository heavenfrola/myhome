<?php

namespace Wing\common;

Class ErrorReport {

    private $e;
    private $e_class;
    private $detail;
    private $code;

    public function __construct($e)
    {
        $this->e = $e;
        $this->code = $e->getCode();

        // chcek qb error
        $e_class = explode('\\', get_class($e));
        $this->e_class = array_shift($e_class);
        if ($this->e_class == 'Pecee') {
            $this->code = 1000;
        }

        // detail
        $this->detail = $this->parseExceptionDetail($e);

        // db error
        if ($this->code == 1000) {
            qbErrorLog($e);
            $this->report();
        }
    }

    public function terminate()
    {
        jsonReturn(array(
            'status' => 'error',
            'message' => $this->e->getMessage(),
            'detail' => $this->detail,
            'code' => $this->code
        ));
    }

    public function report()
    {
        self::__report(
            $this->detail->exception,
            $this->e->getMessage(),
            $this->code,
            $this->detail->trace,
            $this->detail->query
        );
    }

    public static function __report($type, $message, $code, $trace, $query = null)
    {
        global $last_report_message;

        if ($last_report_message == $message) {
            return;
        }
        $last_report_message = $message;

        if ($query == 'select 1') { // ping check 시 제외
            return;
        }

        try {
            $post_args = array(
                'host' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'exception' => $type,
                'message' => $message,
                'code' => $code,
                'trace' => json_encode($trace, JSON_UNESCAPED_UNICODE),
                'query' => aes128_encode($query),
                'server_ip' => $_SERVER['SERVER_ADDR'],
				'client_ip' => $_SERVER['REMOTE_ADDR'],
				'referer' => $_SERVER['HTTP_REFERER']
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://gcdg.wisa.co.kr/receive.php');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt($ch, CURLOPT_REFERER, 'mywisa.com');
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_args);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            //
        }
    }

    private function parseExceptionDetail()
    {
        global $qb;

        $this->detail = (object) array(
            'exception' => basename(get_class($this->e))
        );
        if ($this->code == '1000') {
            $this->detail->exception = 'QueryBuilderException';
            $this->detail->query = $qb->getLastQuery()->getRawSQL();
        };

        // 사내 아이피에서만 상세 에러 내용 표시
        if (preg_match('/^(118\.129\.243|172\.72\.72)\.[0-9]+$/', $_SERVER['REMOTE_ADDR'])) {
            // query error
            if ($this->e_class == 'Pecee') {
                $this->detail->query = $this->e->getQuery()->getRawSql();
            }

            // trace
            $this->detail->trace = array(
                $this->e->getLine() . ' : ' . $this->parsePath($this->e->getFile())
            );
            foreach ($this->e->getTrace() as $trace) {
                array_push(
                    $this->detail->trace,
                    $trace['line'] . ' : ' . $this->parsePath($trace['file'])
                );
            }
        }

        return $this->detail;
    }

    private function parsePath($path)
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        $path = str_replace(__ENGINE_DIR__, '', $path);

        return $path;
    }

}