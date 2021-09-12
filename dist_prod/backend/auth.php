<?php
/************************************************************
*	Authentication Functions 
*	(c) put your name her, 2018
*	Author: put your name here
*
*	
*	
*/

function destroySession()
{
	// Initialize the session.
	// If you are using session_name("something"), don't forget it now!
	session_name(HSA_SESSION_NAME);
	session_start();

	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) 
	{
	    $params = session_get_cookie_params();
	    setcookie(session_name(), '', time() - 42000,
	        $params["path"], $params["domain"],
	        $params["secure"], $params["httponly"]
	    );
	}

	// Finally, destroy the session.
	session_destroy();

}

function isLoggedIn()
{
	if(isset($_SESSION['username']) and 
		!empty($_SESSION['username']) and 
		$_SESSION['username'] != null)
		return true;
	return false;
}

function loginUser($user)
{
	setSession($user);

	if (isLoggedIn())
		return true;

	return false;
}


function getAuthToken()
{
	return array
	(
		'uname' => $_SESSION['username'],
		'uid' => $_SESSION['user_id'],
		'role'=>$_SESSION['user_role'],
		'l'=>$_SESSION['isloggedin']
	);
}

function setSession($user = ['username'=>null,'id'=>null, 'fullname'=>null,'role'=>'provider'])
{
	$_SESSION['username'] = $user['username'];
	$_SESSION['user_id'] = $user['id'];
	$_SESSION['user_role'] = $user['role'];
	$_SESSION['fullname'] = $user['fullname'];
	
	$_SESSION['isloggedin'] = isloggedin();
}

function initSession()
{
	// to initialize session:- set default auth_token only once!
	if (version_compare(PHP_VERSION, '5.4.0', '>='))// For php 5.4.0 or newer
	{
		if (session_status() == PHP_SESSION_NONE) 
		{
			session_name(HSA_SESSION_NAME);
			session_start(array('cookie_lifetime'=> 5400)); //send persistent cookie that lasts for 1hr 30min (5400sec)
			if (!isset($_SESSION['user_id']))
			{
				setSession();
			}
		}
	}
	else//if(version_compare(PHP_VERSION, '5.4.0', '<'))// For older php
	{
		if (session_id() == '')
		{
			session_name(HSA_SESSION_NAME);
			session_start(array('cookie_lifetime'=> 5400)); //send persistent cookie that lasts for 1hr 30min (5400sec)
			if (!isset($_SESSION['user_id']))
			{
				setSession();
			}
		}
	}
			
}

function logoutUser(){
	destroySession();
	if(!isset($_SESSION['username']))
		return true;
	return false;
}

