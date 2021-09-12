<?php

/**
*	Backend Functions
*	RH:- request handler
*
*/
	include 'pdf.php';

	function RH($request, $DB)
	{
		$response = ['data'=>null,'error'=>null];

		//
		if ($request->a == 'auth')
		{
			$response = authCMD($DB,$request);
		}
		elseif ($request->a == 'query')
		{
			$response = dbCMD($DB,$request);
		}
		elseif ($request->a == 'print')
		{
			$response = printCMD($DB,$request);
		}
		else
		{
			$response['error'] = "Error: Invalid Request Type!";
		}
		/**/
		
		return $response;
	}

	function printCMD($db,$rq)
	{
		$r = ['data'=>null, 'error'=>null];
		switch ($rq->target)
		{
			case 'services':
			{				
				// first get services form db
				$filter = obj2array($rq->filter);
				$services = $db->Get($rq->table,$filter);
				$r= count($services['data']) ? printServices($services['data']) : $services;
			}break;
			case 'orders':
			{				
				// first get orders form db
				$filter = obj2array($rq->filter);
				$orders = $db->Get($rq->table,$filter);
				$r = count($orders['data']) ? printOrders($orders['data']) : $orders;
			}break;
			case 'providers':
			{				
				// first get orders form db
				$filter = obj2array($rq->filter);
				$providers = $db->Get($rq->table,$filter);
				$r = count($providers['data']) ? printSProviders($providers['data']) : $providers;
			}break;
			
			default:
				$r['error'] = 'Invalid Print request!';
				break;
		}

		return $r; 
	}

	function authCMD($db,$q)
	{
		$r = ['data'=>null, 'error'=>null];
		$table = $q->table;

		switch ($q->target) {
			case 'login':{
				$filter = obj2array($q->filter);
				$cmd = $db->Get($table, $filter);

				if ( count($cmd['data']) == 1 )
				{
					$user = $cmd['data'][0];
					if(loginUser($user))
					{
						$r['data'] = ['auth_token'=>getAuthToken()];
					}
				}
				elseif($cmd['error'])
				{
				
					$r['error'] = $cmd['error'];
				}
				elseif ( count($cmd['data']) != 1 )
				{
					$r['error'] = 'Invalid User Name and/or Password!';					
				}
			}break;

			case 'reg':{
				
				$d = obj2array($q->data);
				$t = $q->table;

				# checking if account does not already exists
				$acc = $db->Get($t, ['fullname'=>$d['fullname'], 'username'=>$d['username']]);
				if(count($acc['data']) == 0)
				{
					$r = $db->save($t,$d);
				}
				elseif(count($acc['data']) != 0)
				{
					// send duplicate error
					$cname = $d['fullname'];
					$r['error'] = "Could not create account. Account with name '{$cname}' already exists!";
				}
				elseif($acc['error']) // send the db error
				{
					$r['error'] = $acc['error'];
				}
			}break;

			case 'get':{
				$r['data'] = ['auth_token'=>null];
				$r['data']['auth_token'] = getAuthToken();
			}break;
			
			default:
				$r['error'] = 'Invalid Auth request!';
				break;
		}

		return $r;
	}


	function obj2array ( $object, $out = [] )
	{
	    foreach ( (array) $object as $index => $node )
	        $out[$index] = ( is_object ( $node ) ) ? $this->obj2array ( $node ) : $node;

	    return $out;
	}

	function dbCMD($db, $q)
	{
		$r = ['data'=>null, 'error'=>null];
		$table = $q->table;

		switch ($q->target) {
			case 'get':{
				$filter = obj2array($q->filter);
				$r = $db->Get($table, $filter);
			}break;

			case 'set':{
				$record = obj2array($q->data);
				$r = $db->Save($table, $record);
			}break;

			case 'update':{
				$record = obj2array($q->data);
				$filter = obj2array($q->filter);

				$r = $db->Update($table, $filter, $record);
			}break;

			case 'delete':{
				$filter = obj2array($q->filter);

				$r = $db->Delete($table, $filter);
			}break;
			
			default:
				$r['error'] = 'Invalid DB Query request!';
				break;
		}

		return $r;
	}
?>
