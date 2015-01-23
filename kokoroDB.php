<?php
/** *
 * @author  Israel Barragan C.
 * @version 150112
 * @since   11-Apr-2014
 * @url https://github.com/Reedyseth/kokoroDB
 */
include 'kokoroForMysql.php';
include 'kokoroForOracle.php';

abstract class kokoroDB {
	private $connection = null;
	private $db_user    = "database_user_name";
	private $db_pwd     = "password";
	private $db_host    = "your_ip_or_host";
	private $db_name    = "your_database";
	private $db_port    = "2140";
	private $db_sid     = 'name_on_tns_file';

	static function createKokoro( $dbConnection ) {
		if ( $dbConnection == "mysql" ) {
			// create mysql object.
			return new kokoroForMysql();
		} else if ( $dbConnection == "oracle" ) {
			// create mysql object.
			return new kokoroForOracle( true, true );
		} else {
			if ( empty( $dbConnection ) ) {
				throw new Exception( "Unsupport Connection. You need to provide a connection type", 1001 );
			} else {
				throw new Exception( "Unsupport Connection type", 1002 );
			}
		}
	}

	/**
	 * Gets the value of connection.
	 *
	 * @return mixed
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Gets the value of db_user.
	 *
	 * @return mixed
	 */
	public function getDB_user() {
		return $this->db_user;
	}

	/**
	 * Gets the value of db_pwd.
	 *
	 * @return mixed
	 */
	public function getDB_pwd() {
		return $this->db_pwd;
	}

	/**
	 * Gets the value of db_host.
	 *
	 * @return mixed
	 */
	public function getDB_host() {
		return $this->db_host;
	}

	/**
	 * Gets the value of db_name.
	 *
	 * @return mixed
	 */
	public function getDB_name() {
		return $this->db_name;
	}

	/**
	 * Gets the value of db_port.
	 *
	 * @return mixed
	 */
	public function getDB_port() {
		return $this->db_port;
	}

	/**
	 * Gets the value of safeTransactions.
	 *
	 * @return mixed
	 */
	public function getSafeTransactions() {
		return $this->safeTransactions;
	}

	/**
	 * Gets the value of db_sid.
	 *
	 * @return mixed
	 */
	public function getDB_sid() {
		return $this->db_sid;
	}

	/**
	 * Sets the value of db_sid.
	 *
	 * @param mixed $db_sid the db_sid
	 *
	 * @return self
	 */
	public function setDB_sid( $db_sid ) {
		$this->db_sid = $db_sid;

		return $this;
	}
}

?>
