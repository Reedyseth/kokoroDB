<?php

/**
 *
 *
 * @author  Israel Barragan C.
 * @version 150112
 * @since   12-Jan-2015
 * @name kokoroForOracle .php
 */
class kokoroForOracle extends kokoroDB implements IkokoroDB {
	private $conn             = null;
	private $connection       = null;
	private $dt               = null;
	private $data             = null;
	private $errorMessage     = array();
	private $useTNSName       = false;
	private $safeTransactions = null;

	/**
	 * The constructor will set the TNS Name usage and the Safe Transaction.
	 *
	 * @param null $useTNSName Optional parameter, if not parameter is provided then the connection uses the connection
	 *                         settings. If the the parameter is define the connection will be perform using the TNS
	 *                         Name. This TNS Name has to be define on your tnsnames.ORA file.
	 * @param bool $safeTxn
	 */
	function __construct( $useTNSName = null, $safeTxn = false ) {
		if ( $useTNSName != null ) {
			$this->setUseTNSName( $useTNSName );
		}
		if ( $safeTxn == true ) {
			$this->setSafeTransactions( true );
		} else {
			$this->setSafeTransactions( false );
		}
		$this->create_connection();
	}

	function create_connection() {
		try {
			if ( $this->getUseTNSName() ) {
				// Oracle Connection with TNS Name.
				$conn = new PDO( 'oci:dbname=' . $this->getDB_sid(),
					$this->getDB_user(), $this->getDB_pwd() );
			} else {
				// Oracle Connection with no TNS Name.
				$conn = new PDO( 'oci:dbname=' . $this->getDB_host() . ':' . $this->getDB_port() . '/' . $this->getDB_name(),
					$this->getDB_user(), $this->getDB_pwd() );
			}
			// Oracle Connection.
			// Lets make sure that PDO will throw exceptions.
			$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			// Since working with Oracle is a bit different, we have to set the explicit date format
			$pstmt = $conn->prepare( "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'" );
			$pstmt->execute();
			// Now we set the connection attribute.
			$this->setConnection( $conn );
		}
		catch ( PDOException $ex ) {
			$errorMessage["ex_db_connection_error"]      = $ex;
			$errorMessage["message_db_connection_error"] = "Cannot connect to database";
			$this->setErrorMessage( $errorMessage );
			throw new Exception( $ex, 1111 );
		}
	}

	/**
	 * Check if there is an active transaction on the driver, if there is not an active transaction
	 * it creates a new one.
	 * @since 22-Jan-2015
	 */
	public function beginTransaction() {
		$this->conn = $this->getConnection();
		if ( ! $this->conn->inTransaction() ) {
			$this->conn->beginTransaction();
		}
	}

	/**
	 * Commit the current transaction
	 * @since 22-Jan-2015
	 */
	public function commit() {
		$this->conn = $this->getConnection();
		$this->conn->commit();
	}

	/**
	 * Rollback the current transaction
	 * @since 22-Jan-2015
	 */
	public function rollBack() {
		$this->conn = $this->getConnection();
		$this->conn->rollBack();
	}

	/**
	 * Insert a Record on the database. If $safeTransactions is enable then it will work With Safety Transaction.
	 *
	 * @param      $sql
	 * @param null $data
	 *
	 * @since 15-Jul-2014
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function insert_record( $sql, $data = null ) {
		$this->conn = $this->getConnection();

		if ( $this->getSafeTransactions() ) {
			try {
				$this->beginTransaction();
				$this->dt = $this->conn->prepare( $sql );
				$this->dt->execute( $data );
				$this->commit();

			}
			catch ( PDOException $ex ) {
				$errorMessage["ex_insert_error"] = $ex->getMessage();
				$this->setErrorMessage( $errorMessage );
				$this->dt->closeCursor();
				$this->rollBack();
				throw new Exception( "There was a problem inserting the record." );

				return false;
			}
		} else if ( ! $this->getSafeTransactions() ) {
			try {
				$this->dt = $this->conn->prepare( $sql );
				$this->dt->execute( $data );
			}
			catch ( PDOException $ex ) {
				$errorMessage["ex_insert_error"] = $ex->getMessage();
				$this->setErrorMessage( $errorMessage );
				$this->dt->closeCursor();
				throw new Exception( "There was a problem inserting the record." );

				return false;
			}
		}

		return true;
	}

	public function query_all_data( $sql, $data = null ) {
		$this->conn = $this->getConnection();
		try {
			$this->dt = $this->conn->prepare( $sql );
			$this->dt->execute( $data );
		}
		catch ( PDOException $ex ) {
			// var_dump($ex);
			//echo $ex->xdebug_message;
			if ( $ex->getCode() == "HY093" ) {
				$errorMessage["message_error"] = $ex->getMessage();
			}
			$errorMessage["ex_query_error"] = $ex->getMessage();
			$this->setErrorMessage( $errorMessage );
			$this->dt->closeCursor();
			throw new Exception( $ex->getMessage() );

			return false;
		}
		$this->data = $this->dt->fetchAll();
		$dataObject = array();
		// This will create an array of objects.
		foreach ( $this->data as $row ) {
			//array_push($dataObject,(Object)$row);
			// This is faster tha array_push because where are not
			// doing nothing with the value return by array_push.
			$dataObject[] = (object) $row;
		}

		return $dataObject;
	}

	private function _create_binding_types( $data_types = null ) {
		$types = array();

		if ( $data_types == null ) {
			$errorMessage["ex_param_type_null"] = '_create_binding_types(), Can\'t create null array';
			$this->setErrorMessage( $errorMessage );

			return false;
		}
		$types_count = sizeof( $data_types );
		for ( $i = 0; $i < $types_count; $i ++ ) {
			switch ( $data_types[$i] ) {
				case 'int':
					$types[] = PDO::PARAM_INT;
					break;
				case 'boolean':
					$types[] = PDO::PARAM_BOOL;
					break;
				case 'string':
					$types[] = PDO::PARAM_STR;
					break;
				default:
					$types[] = PDO::PARAM_NULL;
					break;
			}
		}

		return $types;
	}

	public function query_all_data_exact( $sql, $data = array(), $data_types = array() ) {
		$this->conn       = $this->getConnection();
		$binding_types    = array();
		$value_count      = sizeof( $data );
		$data_types_count = sizeof( $data );

		if ( $value_count != $data_types_count ) {
			$errorMessage["ex_param_type_error"] = 'The number of values is not the same as the'
				. ' Data Types. Check that you are sending the'
				. ' correct number of values and types.';
			$this->setErrorMessage( $errorMessage );

			return false;
		} else {
			$binding_types = $this->_create_binding_types( $data_types );
			if ( ! $binding_types ) {
				return false;
			}
			// Data is ready to be bind
		}
		try {
			$this->dt    = $this->conn->prepare( $sql );
			$array_count = sizeof( ( $binding_types ) );
			for ( $i = 0; $i < $array_count; $i ++ ) {
				$this->dt->bindParam( 1 + $i, $data[$i], $binding_types[$i] );
			}
			$this->dt->execute();
		}
		catch ( PDOException $ex ) {
			// var_dump($ex);
			//echo $ex->xdebug_message;
			if ( $ex->getCode() == "HY093" ) {
				$errorMessage["message_error"] = $ex->getMessage();
			}
			$errorMessage["ex_query_error"] = $ex->getMessage();
			$this->setErrorMessage( $errorMessage );
			$this->dt->closeCursor();

			return false;
		}
		$this->data = $this->dt->fetchAll();
		$dataObject = array();
		// This will create an array of objects.
		foreach ( $this->data as $row ) {
			//array_push($dataObject,(Object)$row);
			$dataObject[] = (object) $row;
			// This is faster tha array_push because where are not
			// doing nothing with the value return by array_push.
		}

		return $dataObject;
	}

	/**
	 * Return an associative array with the errors.
	 *
	 * @return mixed
	 */
	public function errorInfo() {
		return $this->errorMessage;
	}

	/**
	 * Gets the value of errorMessage.
	 *
	 * @return mixed
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}

	/**
	 * Sets the value of errorMessage.
	 *
	 * @param mixed $errorMessage the errorMessage
	 *
	 * @return self
	 */
	public function setErrorMessage( $errorMessage ) {
		$this->errorMessage[] = $errorMessage;

		return $this;
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
	 * Sets the value of connection.
	 *
	 * @param mixed $connection the connection
	 *
	 * @return self
	 */
	public function setConnection( $connection ) {
		$this->connection = $connection;

		return $this;
	}


	/**
	 * Gets the value of driverOptions.
	 *
	 * @return mixed
	 */
	public function getDriverOptions() {
		return $this->driverOptions;
	}

	/**
	 * Gets the value of useTNSName    .
	 *
	 * @return mixed
	 */
	public function getUseTNSName() {
		return $this->useTNSName;
	}

	/**
	 * Sets the value of useTNSName    .
	 *
	 * @param mixed $useTNSName the useTNSName
	 *
	 * @return self
	 */
	public function setUseTNSName( $useTNSName ) {
		$this->useTNSName = $useTNSName;

		return $this;
	}

	/**
	 * @param null $safeTransactions
	 */
	public function setSafeTransactions( $safeTransactions ) {
		$this->safeTransactions = $safeTransactions;
	}

	/**
	 * @return null
	 */
	public function getSafeTransactions() {
		return $this->safeTransactions;
	}

}

?>
