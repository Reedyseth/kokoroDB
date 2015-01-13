<?php
/**
 * IkokoroDB will define the methods that are needed on kokoroDB. This will help
 * to control the methods for every connections type.
 * @author Israel Barragan C.
 * @version 140113
 * @since 13-Jan-2015
 * @name IkokoroDB.php
 *
 */
interface IkokoroDB {
	public function create_connection();
	public function insert_record( $sql, $data );
	//public function delete_record();
	public function query_all_data( $sql, $data );
	public function query_all_data_exact( $sql, $data , $data_types );
	public function errorInfo();
	public function getErrorMessage();
	public function setErrorMessage( $errorMessage );
	public function getConnection();
	public function setConnection( $connection );
}
