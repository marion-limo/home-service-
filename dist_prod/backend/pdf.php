<?php
	require('fpdf/fpdf.php');

	class PDF extends FPDF
	{
		public $pdf_title = '';
		// Page header
		public function Header()
		{
		    // Logo
		    $this->Image('../assets/logo.png',0,0,50);
		    // Arial bold 15
		    $this->SetFont('Courier','B',22);
		    // Move to the right
		    $this->Cell(80);
		    // Business Title
		    $this->Cell(30,10,'Home Service System',0,1,'C');
		    $this->SetFont('Courier','',12);
		    // Move to the right
		    $this->Cell(80);
		    // Content Title
		    $this->Cell(30,10,'Admin: marionjepchumba@gmail.com',0,1,'C');
		    //line break
		    $this->Ln(5);
		    // Move to the right
		    $this->Cell(80);
		    // Content Title
		    $this->SetFont('Courier','U',15);
		    $this->Cell(30,10,$this->pdf_title,0,1,'C');
		}

		// write table
		public function drawTable($title,$fields)
		{
			//Colors, line width and bold font
		    $this->SetFillColor(255,0,0);
		    $this->SetTextColor(25,108,96);
		    $this->SetDrawColor(56,80,103);
		    $this->SetLineWidth(.3);
		    $this->SetFont('Times','B',11);
	    	$this->Cell(0,10,$title,1,0,'C');
	    	$this->Ln();

	    	foreach ($fields as $field)
		    {//echo var_dump($field);
		    	// Table head Colors, line width and bold font
			    $this->SetFillColor(255,0,0);
			    $this->SetTextColor(0,0,0);
			    $this->SetDrawColor(56,80,103);
			    $this->SetLineWidth(.3);
			    $this->SetFont('Times','B',11);
		    	$this->Cell(30,10,$field['col_name'],1,0,'R');

		    	// Table data Colors, line width and bold font
			    $this->SetFillColor(255,0,0);
			    $this->SetTextColor(0,0,0);
			    $this->SetLineWidth(.3);
			    $this->SetFont('Times','',11);

			    $this->Cell(0,10,$field['col_value'],1);

			    $this->Ln();
		    }
		}

		// Page footer
		public function Footer()
		{
		    // Position at 1.5 cm from bottom
		    $this->SetY(-15);
		    // Arial italic 8
		    $this->SetFont('Courier','I',8);
		    // Page number
		    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		}

	}

	// Instanciation of inherited class

	function printOrders($orders)
	{
		$pdf = new PDF();
		$pdf->pdf_title = 'Order Summary';
		$pdf->AliasNbPages();
		
		$n = 0;
		foreach ($orders as $order)
		{
			$pdf->AddPage();
			$order_id = $order['id'];
			$a =[ // A. Order Details
				[ 'col_name'=>'Order ID', 'col_value'=>$order['id'] ],
				[ 'col_name'=>'Status', 'col_value'=>$order['status'] ],
				[ 'col_name'=>'Sent On', 'col_value'=>$order['add_date'] ],
				[ 'col_name'=>'Modified On', 'col_value'=>$order['last_modified']],
			];

			$product = json_decode($order['product']);
			$provider = json_decode($order['provider']);
			$provider=$provider->provider;

			$b = [ // B. Product Details
				[ 'col_name'=>'Service ID', 'col_value'=>$product->id ],
				[ 'col_name'=>'Label', 'col_value'=>$product->label ],
				[ 'col_name'=>'Description', 'col_value'=>$product->description ],
				[ 'col_name'=>'Service Fee', 'col_value'=>"KSHS. {$product->service_fee}/="],
				[ 'col_name'=>'Provider', 'col_value'=>"{$provider->fullname}, $provider->contact"],
			];

			$client = json_decode($order['client']);

			$c = [ // C. Client Details
				[ 'col_name'=>'ID', 'col_value'=>'#' ],
				[ 'col_name'=>'Full Name', 'col_value'=>$client->fullname ],
				[ 'col_name'=>'Contact', 'col_value'=>$client->contact ],
				[ 'col_name'=>'Address', 'col_value'=>$client->address],
			];

			$pdf->drawTable('A. Order Details',$a);
			$pdf->drawTable('B. Product Details',$b);
			$pdf->drawTable('C. Client Details',$c);

			$pdf->SetFont('Times','',12);
			$pdf->Cell(0,10,'Contact the Service Provider for more information.',0,1);
			$pdf->Cell(0,10,'This document is system generated, valid and subject to modifications.',0,1);
			
			
			$pdf->SetFont('Courier','',12);
			date_default_timezone_set("Africa/Nairobi");
			$date = Date('d l F, Y H:i:s e');

			$pdf->Cell(0,10,"Generated: {$date} time.",0,1);
			$n++;
		}

		$d = Date('y-m-d_H-i-s');
		$filename = $n == 1 ? "order_{$order_id}_{$d}.pdf" : "orders_{$d}.pdf";
		$file = "../assets/files/orders/{$filename}";

		$pdf->Output('F',$file);
		
		if(file_exists($file))
			return ['data'=>$file, 'error'=>null];
		else return ['data'=>null, 'error'=> 'Error occurred while generating order(s)'];
	}

	function printServices($services)
	{

		$pdf = new PDF();
		$pdf->pdf_title = 'Service Information';

		$n = 0;
		foreach ($services as $service)
		{
			$service_id = $service['id'];
			$pdf->AliasNbPages();
			$pdf->AddPage();

			$a =[ // Service Details
				[ 'col_name'=>'Service ID', 'col_value'=>$service['id'] ],
				[ 'col_name'=>'Label', 'col_value'=>$service['label'] ],
				[ 'col_name'=>'Description', 'col_value'=>$service['description'] ],
				[ 'col_name'=>'Service Fee', 'col_value'=>"KSHS. {$service['service_fee']}/=" ],
				[ 'col_name'=>'Added On', 'col_value'=>$service['added_on'] ],
				[ 'col_name'=>'Modified On', 'col_value'=>$service['modified_on']],
			];

			$pdf->drawTable("{$service['label']}",$a);

			$pdf->SetFont('Times','',12);
			$pdf->Cell(0,10,'Contact the Admin for more information.',0,1);
			$pdf->Cell(0,10,'This document is system generated, valid and subject to modifications.',0,1);
			
			
			$pdf->SetFont('Courier','',12);
			date_default_timezone_set("Africa/Nairobi");
			$date = Date('d l F, Y H:i:s e');

			$pdf->Cell(0,10,"Generated: {$date} time.",0,1);
			$n++;
		}

		$d = Date('y-m-d_H-i-s');
		$filename = $n == 1 ? "service_{$service_id}_{$d}.pdf" : "serices_{$d}.pdf";
		$file = "../assets/files/services/{$filename}";

		$pdf->Output('F',$file);
		
		if(file_exists($file))
			return ['data'=>$file, 'error'=>null];
		else return ['data'=>null, 'error'=> 'Error occurred while generating service(s)'];
	}

	function printSProviders($providers)
	{

		$pdf = new PDF();
		$pdf->pdf_title = 'Service Provider Information';

		$n = 0;
		@$host = defined(SERVER_NAME) ? SERVER_NAME : 'homeservice.localhost';
		foreach ($providers as $provider)
		{
			$provider_id = $provider['id'];
			$pdf->AliasNbPages();
			$pdf->AddPage();

			$user = json_decode($provider['provider']);
			$id = $provider['service_id'];

			$link = "See Service details at: https://{$host}/#/services/{$id}";

			$a =[ // Service Details
				[ 'col_name'=>'Provider ID', 'col_value'=>$provider['id'] ],
				[ 'col_name'=>'User ID', 'col_value'=>$provider['user_id'] ],
				[ 'col_name'=>'Full Name', 'col_value'=>$user->fullname ],
				[ 'col_name'=>'Contact', 'col_value'=>$user->contact ],
				[ 'col_name'=>'Location', 'col_value'=>$user->address ],
				[ 'col_name'=>'Service ID', 'col_value'=> "{$id}, {$link}" ],
				[ 'col_name'=>'Rate', 'col_value'=>"KSHS. {$provider['cost']}/=" ],
				[ 'col_name'=>'Added On', 'col_value'=>$provider['add_date'] ],
				[ 'col_name'=>'Last Modified', 'col_value'=>$provider['last_modified']],
			];

			$pdf->drawTable("{$user->fullname}'s Details",$a);

			$pdf->SetFont('Times','',12);
			$pdf->Cell(0,10,'Contact the Admin for more information.',0,1);
			$pdf->Cell(0,10,'This document is system generated, valid and subject to modifications.',0,1);
			
			
			$pdf->SetFont('Courier','',12);
			date_default_timezone_set("Africa/Nairobi");
			$date = Date('d l F, Y H:i:s e');

			$pdf->Cell(0,10,"Generated: {$date} time.",0,1);
			$n++;
		}

		$d = Date('y-m-d_H-i-s');
		$filename = $n == 1 ? "provider_{$provider_id}_{$d}.pdf" : "providers_{$d}.pdf";
		$file = "../assets/files/providers/{$filename}";

		$pdf->Output('F',$file);
		
		if(file_exists($file))
			return ['data'=>$file, 'error'=>null];
		else return ['data'=>null, 'error'=> 'Error occurred while generating Service Provider(s)'];
	}