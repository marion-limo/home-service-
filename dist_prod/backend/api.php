<?php
/************************************************************
*	Home Service Application API Endpoint
*	(c) Put your Name here, 2018
*	Author: put your name here
*
*
*
*/
	/*SERVER Details:*/

	define('HSA_HOST_NAME', 'localhost');
	define('HSA_PORT', 3360);
	define('HSA_USERNAME', 'root');//
	define('HSA_DB_NAME', 'homeserviceapp_db');//
	define('HSA_PASSWORD', '');//


	/*Settings*/

	define('HSA_SESSION_NAME', 'hsa_session'); // this is the unique session ID cookie registered on the browse

	//load Database class
	require_once 'db.php';

	//load auth functions
	include 'auth.php';

	initSession();
	include 'utils.php';
	$server = array
	(
		'hostname' => HSA_HOST_NAME,  
		'port'=>HSA_PORT,
		'dbname' => HSA_DB_NAME, 
		'username' => HSA_USERNAME, 
		'password' => HSA_PASSWORD
	);

	// logout users
	if(isset($_GET['logout']))
	{
		if(logoutUser())
			header("Location: /");
	}

	if(isset($_FILES['image']))
	{
		if(is_uploaded_file($_FILES['image']['tmp_name']))
		{
			$tmp_name = $_FILES['image']['tmp_name'];
			$filename = basename($_FILES['image']['name']);
			
			$upload_to = "../assets/service-imgs/{$filename}";
			$r['data'] = move_uploaded_file($tmp_name, $upload_to);
		}
		else $r = ['error'=>'File Upload Failed','data'=>null];
		#header("Content-Type: application/json");
		echo json_encode($r);
	}


	$DB = new DBClass($server);
	$input = file_get_contents('php://input');
	$input = json_decode($input);
	
	if(isset($input))
	{
		$response = array('data'=>null, 'error'=>null);

		if ($DB->_errors)
		{	
			$response['error'] = $DB->_errors;
		}

		elseif ($DB)
		{
			$response = RH($input, $DB);
		}
		else
		{
			$response['error'] = 'Program/Unknown Error!';
		}
		header("Content-Type: application/json");
		echo json_encode($response);
	}
?>
