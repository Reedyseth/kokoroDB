<?php

/**
 *
 *
 * @author  Israel Barragan
 * @version 140521
 * @since   11-Apr-2014
 * @name arkbase.php
 */
class Arkbase {
	private $connection = null;
	private $db_user = "root";
	private $db_pwd = "1234";
	private $db_host = "localhost";
	private $db_name = "tutorials";
	private $db_port = "";
	private $conn = null;
	private $dt = null;
	private $data = null;
	private $errorMessage = array();


	function __construct() {
		$this->create_connection();
	}

	function create_connection() {
		try {
			$conn = new PDO( "mysql:host=" . $this->db_host .
				';dbname=' . $this->db_name, $this->db_user, $this->db_pwd );
			// Lets make sure that PDO will throw exceptions.
			$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			// Now we set the connection attribute.
			$this->setConnection( $conn );
		}
		catch ( PDOException $ex ) {
			$errorMessage["ex_db_connection_error"]      = $ex;
			$errorMessage["message_db_connection_error"] = "Cannot connect to database";
			$this->setErrorMessage( $errorMessage );
			exit;
		}
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
}

?>
