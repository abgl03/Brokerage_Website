<?php
	ini_set("error_reporting",E_ALL & ~E_NOTICE);
	ini_set("display_errors",1);

	session_start();
	$_SESSION['username'];
	$_SESSION['isadmin'];

	if($_REQUEST["submitter"]=="Log Out" || $_SESSION['isadmin']=='Y' || $_SESSION['username']==NULL)
	{
	session_destroy();
	header('location:LoginPage.php');
	}
?>

<html>
<form name="homepage" method="post" action="HomePage.php">
	<head>
	<title>Order</title>
	
		<link rel="stylesheet" type="text/css" href="HomePage.css">
		
		<script>
		
		</script>
		
	</head>
	
	<body>

	<div id="header">
	<img src="LogoGreenSmall.png" width="100px" alt="Commodity Brokerage" class="logo">
	<img src="CB Header.png" width="300px" alt="Commodity Brokerage" class="logo">
	<input type="submit" name="submitter" value="Log Out" class="logout" />
	</div>
	
	<div id="container">
	<div id="sidebar">
	<div id="littlespace">
	</div>
	<div class="sb">
	<p><a href="HomePage.php">Home</a></p>
	</div>
	<div class="sb">
	<p><a href="AddEditPage.php">Add/Edit Accounts</a></p>
	</div>
	<div class="sb">
	<p><a href="OrderPage.php">Order</a></p>
	</div>
	<div class="sb">
	<p><a href="ChartsPage.php">Charts</a></p>
	</div>
	</div>
	<div id="content">
	<div id="unfinishedsection">
	<b>Project 2</b>
	</div>
	</div>
	</div>
	
	<div id="footer">
	<center><p>For queries, please contact the <a href="mailto:aper543.auckland.ac.nz">webmaster</a>.</p>
	</div>
	
	</body>
</form>
</html>