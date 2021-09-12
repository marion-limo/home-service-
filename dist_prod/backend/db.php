<?php
/************************************************************
*	DBClass
*	(c) put your name here, 2018
*	Author: put your name here
*
*	DBClass Description
*
*	Implements a PHP Data Object for Database Manipulation
*
*
*	Documentation
*	========================================================================
*	
*
*	Constructor Parameter
*	@server_info: 
*		Array;  [hostname, dbname, username, password, database]
*	
*	Class properties
*	-	_pdo: db connetion
*	-	_errors: captures errors
*	
*	Class methods
*	+Get:-> Retrieves Record(s) from Database
*		Params:
*			@table -> String; e.g 'blog_post'. Name of table to retrieve record(s) from.
*			@filter -> Optional Array object, format: [field => value] or [field1 => value, field2 => value].
*					Table and Filter(s) for the record to be retrieved.
*
*		Returns: Array object; [data => records, error => errors]. //records is all rows got
*
*
*			
*	+Save: Adds a Record to Database
*		Params:
*			@table -> String; e.g 'blog_post'. Name of table to save record to.
*			@record -> Array object, format: [fields => values]. Data to be added.
*			@
*
*		Returns: Array object; [data => record_id, error => errors]. //record_id is unique record id 
*
*
*	+Update:-> Updates a Record in Database
*		Params:
*			@table -> String; e.g 'blog_post'. Name of table to Update record.
*			@filter -> Array object, forma: [field => values]. Specific details about record to be Updated
*			@record -> Array object, format: [field => values]. New Data about record to be Updated.
*
*		Returns: Array object; [data => rows_updated, error => errors]. //rows_updated is total number of rows updated
*
*
*	+Delete:-> Deletes a Record form Database
*		Params:
*			@table -> String; e.g 'blog_post'. Name of table to Delete record from.
*			@filter -> Array object, format: [field => values]. Specific details about record to be Deleted.
*
*		Returns: Array object; [data=>rows_deleted, error => errors] //rows_deleted is number of rows deleted
*
*
*
*	NB: ALL DATA SHOULD BE SANITIZED FOR SECURITY!
*
*
*
*
**************************************************************************/

	class DBClass
	{


		private $_pdo;

		public $_errors = null;

		public function Get($table, $filter=null)
		{

			$error = null;
			$data = null;

			if (gettype($table) == 'string' and (gettype($filter) == 'array' or $filter == null))
			{
				$sql = "SELECT * FROM `{$table}`";
				$params = array();

				if ($filter != null)
				{

					$sql .= " WHERE";
					$indx = count($filter);
					foreach ($filter as $key => $value)
					{
						$sql .= " `{$key}` = ?"; 
						array_push($params, $value);
						if($indx > 1)
							$sql .= " AND";
						$indx -= 1; 
					}
				}

				if($this->_pdo)
				{

					$query = $this->_pdo->prepare($sql);
					$exec = $query->execute($params);
					if($exec)
					{			
						$data = $query->fetchAll(PDO::FETCH_ASSOC);

						#TODO: close connection
					} else 
						$error = $query->errorInfo();

				}
				elseif($this->_errors)
				{ 
					$error = $this->_errors;
				}

			}
			else
				$error = 'Invalid param(s) supplied';	

			return ['data' => $data, 'error' => $error];
			
		}


		public function Save($table, $record)
		{
			$error = null;
			$data = null;

			if (gettype($table) == 'string' and gettype($record) == 'array')
			{
				$sql = "INSERT INTO `{$table}` (";
				$values = array();
				$col = count($record);

				$sql_end = " VALUES (";
				foreach ($record as $field => $value)
				{
					if($col <= 1)
					{
						$sql_end .= " ?";
						$sql_end .= " )";
						$sql .= " {$field}";
						$sql .= " )";
					}
					else
					{
						$sql_end .= " ?,";
						$sql .= " {$field},"; 
					}
					$col -= 1; 
				}

				$sql .= $sql_end;

				if ($this->_pdo)
				{
					$values = array_values($record);
					$query = $this->_pdo->prepare($sql);
					foreach ($values as $key => &$value)
					{
						$k = $key + 1;
						$query->bindParam($k, $value);
					}
					
					$exec = $query->execute();
					if($exec)
					{			
						$data = $this->_pdo->lastInsertId();

						#TODO: close connection
					}
					else 
						$error = $query->errorInfo();

				} 
				elseif ($this->_errors) 
				{ 
					$error = $this->_errors;
				}
			}
			else
				$error = 'Invalid params supplied';

			return array('data' => $data, 'error' => $error);
		}


		public function Update($table, $filter, $record)
		{
			$error = null;
			$data = null;

			if (gettype($table) == 'string' and (gettype($filter) == 'array' and gettype($record) == 'array'))
			{
				$sql = "UPDATE `{$table}` SET";
				$values = array();
				$col = count($record);

				foreach ($record as $field => $value)
				{
					if($col <= 1)
					{
						$sql .= " `{$field}` = ?";
					}
					else
					{
						$sql .= " `{$field}` = ?,"; 
					}
					$col -= 1; 
				}

				$col = count($filter);
				$sql_end = " WHERE";

				foreach ($filter as $key => $value)
				{
					if($col <= 1)
					{
						$sql_end .= " `{$key}` = {$value}";
					}
					else
					{
						$sql_end .= " `{$key}` = {$value},"; 
					}
					$col -= 1;
				}

				$sql .= $sql_end;

				$values = array_values($record);

				if ($this->_pdo)
				{
					$query = $this->_pdo->prepare($sql);
					foreach ($values as $key => &$value)
					{
						$k = $key + 1;
						$query->bindParam($k, $value);
					}
					
					$executed = $query->execute();
					if($executed)
					{			
						$data = $query->rowCount();

						#TODO: close connection
					} 
					else 
						$error = $query->errorInfo();

				}
				elseif ($this->_errors)
				{ 
					$error = $this->_errors;
				}
			}
			else
				$error = 'Invalid params supplied';

			return array('data'=>$data, 'error' => $error);
		}

		public function Delete($table, $filter)
		{
			$error = null;
			$data = null;

			if (gettype($table) == 'string' and gettype($filter) == 'array')
			{
				$sql = "DELETE FROM `{$table}` WHERE";
				$values = array();
				$col = count($filter);

				foreach ($filter as $field => $value)
				{
					if($col <= 1)
						$sql .= " `{$field}` = ?";
					
					else
						$sql .= " `{$field}` = ?,"; 

					$col -= 1; 
				}
		 
				if($this->_pdo)
				{
					$values = array_values($filter);
					$query = $this->_pdo->prepare($sql);
					foreach ($values as $key => &$value)
					{
						$k = $key + 1;
						$query->bindParam($k, $value);
					}
					
					$executed = $query->execute();
					if($executed)
					{			
						$data = $query->rowCount();

						#TODO: close connection
					}
					else 
						$error = $query->errorInfo();/**/

				} 
				elseif ($this->_errors) 
				{
					$error = $this->_errors;
				}
			}
			else
				$error = 'Invalid params supplied';

			return array('data'=>$data, 'error' => $error);
		}

		function __construct($server)
		{

			#$dbh =  "mysql:host={$server['hostname']};port={$server['port']};dbname={$server['dbname']}";
			$dbh =  "mysql:host={$server['hostname']};dbname={$server['dbname']}";
			
			try
			{
				@$this->_pdo = new PDO($dbh, $server['username'], $server['password']);
			}
			catch(PDOException $e)
			{
				$this->_errors = $e->getMessage();
				#die($this->_errors);
			}
		}
	}