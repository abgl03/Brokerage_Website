<?php
	ini_set("error_reporting",E_ALL & ~E_NOTICE);
	ini_set("display_errors",1);

	include_once $_SERVER['DOCUMENT_ROOT'] . "/flourish/toinclude.php";
	$connstr="ISOMTEACHING5\INFOSYS280";
	$mydb=new fDatabase("mssql","finance","mytest","",$connstr,61495);
	
		$inputtedusername = $_REQUEST['username'];
		$inputtedpassword = $_REQUEST['password'];
		
		if($_REQUEST["submitter"]=="Log In")
		{
			if(strlen($inputtedusername)==0 || strlen($inputtedpassword)==0)
			{
			$errornoinput = "alert('No username or password inputted.');";
			}
			
			else
			{
				$result=$mydb->query("SELECT Personname,password,isadmin FROM person where Personname = '$inputtedusername'");
				if ($result->countReturnedRows()==0)
				{
				$errorusername = "alert('Username does not exist. Please try again.');";
				}
				
				else
				{
				$resultrow = $result ->fetchRow();
				$personname = $resultrow['Personname'];
				$password = $resultrow['password'];
				$isadmin = $resultrow['isadmin'];
				
					if ($inputtedusername==$personname &&  $inputtedpassword==$password) 
					{
					session_start();
					$_SESSION['username'] = $inputtedusername;
					$_SESSION['isadmin'] = $isadmin;
					header('location:HomePage.php');
					}
					
					else
					{
					$errorpassword = "alert('Wrong password. Please try again.');";
					}
				}
			}
		}
					
?>

<html>
	
	<form name="loginpage" method="post" action="LoginPage.php">
	
	<head>
	<title>Log In</title>
	
		<link rel="stylesheet" type="text/css" href="LoginPage.css">
	
		<script>
		
		function doalert()
		{
		<?php echo $errornoinput; ?>
		<?php echo $errorusername; ?>
		<?php echo $errorpassword; ?>
		}
		
		</script>
		
	</head>
	
	<body onload="doalert()">
	
		<div id="box">
		
		<center><img src="LogoGreenSmall.png" width="100px" alt="Commodity Brokerage">
		
		<div id="smallbox">
		
		<br />
		
		<div class="input">
		<input type="text" name="username" size="20" value="" placeholder="Username" />
		</div>
		<div class="input">
		<input type="password" name="password" size="20" value="" placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;" />
		</div>
		<div class="input">
		<input type="submit" name="submitter" value="Log In"/>
		</div>
		</div>
		
		</div>
		
	</body>
	
	</form>
	
</html>