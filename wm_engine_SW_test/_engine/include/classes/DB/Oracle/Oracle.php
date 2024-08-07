<?PHP

	namespace Wing\DB\Oracle;

	class Oracle {
		public $errmsg;
		protected $conn;
        protected $pdo;

        public function __construct()
        {
            $this->pdo = $GLOBALS['pdo'];
        }

		public function connect($conn) {
			$dbstr ="(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = $conn[ip])(PORT = $conn[port]))
			(CONNECT_DATA =
			(SERVER = DEDICATED)
			(SERVICE_NAME = $conn[service])
			(INSTANCE_NAME = $conn[service])))";

			$this->conn = oci_connect($conn['user'], $conn['pwd'], $dbstr, $conn['charset']);
			$error = oci_error();
			if($error) {
				$this->errmsg = $error['message'];
				return false;
			}

			return true;
		}

		public function query($query, $cdata = null) {
			$this->errmsg = '';
			$this->qry = $query;

			$res = oci_parse($this->conn, $query);
			if($cdata) {
				$clob = oci_new_descriptor($this->conn, OCI_D_LOB);
				oci_bind_by_name($res, ':clob', $clob, -1, OCI_B_CLOB);
			}
			oci_execute($res, OCI_NO_AUTO_COMMIT);
			if($clob) {
				$clob->save($cdata);
				$clob->free();
			}

			if($this->error($res)) {
				oci_rollback($this->conn);

				echo '<div>'.$this->qry.'</div>';
				echo '<div>'.$this->errmsg.'</div>';
				return false;
			}

			if(preg_match('/^(update|insert)/', trim($query))) {
				foreach($_POST as $key => $val) {
					$postdata .= "[$key] $val\n";
				}
				$postdata = addslashes($postdata);
				$_qry = addslashes($query);
				$this->pdo->query("insert into oracle_log (date, postdata, qry) values ('$GLOBALS[now]', '$postdata', '$_qry')");
			}

			$r = oci_commit($this->conn);
			if(!$r) echo 'commit_error';

			return $res;
		}

		public function assoc($query) {
			$res = $this->query($query);
			if($res) {
				return oci_fetch_assoc($res);
			}
			return false;
		}

		public function row($query) {
			$res = $this->query($query);
			if($res) {
				$data = oci_fetch_row($res);
				return $data[0];
			}
			return false;
		}

		public function error($res = null) {
			$error = @oci_error($res);

			$this->errmsg = null;
			if($error) {
				$this->errmsg = $error['message'];

				$_qry = addslashes($this->qry);
				$postdata = addslashes($this->errmsg);
				$this->pdo->query("insert into oracle_log (date, type, postdata, qry) values ('$GLOBALS[now]', 'E', '$postdata', '$_qry')");
			}
			return $this->errmsg;
		}
	}

?>