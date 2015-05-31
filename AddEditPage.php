<?php
	ini_set("error_reporting",E_ALL & ~E_NOTICE);
	ini_set("display_errors",1);

	session_start();
	$_SESSION['username'];
	$_SESSION['isadmin'];

	if($_REQUEST["submitter"]=="Log Out" || $_SESSION['isadmin']=='N' || $_SESSION['username']==NULL)
	{
	session_destroy();
	header('location:LoginPage.php');
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . "/flourish/toinclude.php";
	$connstr="ISOMTEACHING5\INFOSYS280";
	$mydb=new fDatabase("mssql","finance","mytest","",$connstr,61495);
	
	$formmode=1;
	$onloaddo="";
	$inputid="";
	$inputname="";
	$inputpassword="";
	$inputadmin="";
	$brokercat="";
	$address="";
	$phone="";
	$email="";

	if ($_REQUEST["submitter"]=="Save")
		{
		if ($_REQUEST["inputid"]=="(System Specified)")
			{
			$queryres=$mydb->query("Select * from person");
			if ($queryres->countReturnedRows()==0)
				$inputid=1;
			else
				{
				$queryres=$mydb->query("Select Max(Personid) as mx from person");
				$resultrow=$queryres->fetchRow();
				$inputid=$resultrow['mx']+1;
				}
			$mydb->execute("Insert Into person ".
				"(Personid, Personname, isadmin, password)".
				"Values (%i,%s,%s,%s)",
				$inputid,
				$_REQUEST["inputname"],
				$_REQUEST["inputadmin"],
				$_REQUEST["inputpassword"]);
			$onloaddo="alert('Saved new account ".$inputid."');";
			$mydb->execute("Insert Into broker ".
				"(brokerid, address, brokercat, phone, email)".
				"Values (%i,%s,%i,%s,%s)",
				$inputid,
				$_REQUEST["address"],
				$_REQUEST["brokercat"],
				$_REQUEST["phone"],
				$_REQUEST["email"]);
			$onloaddo="alert('Saved new account ".$inputid."');";
			}
		else
			{
			$mydb->execute("Update person ".
				"Set Personname=%s, ".
				"isadmin=%s, ".
				"password=%s ".
				"where Personid=%i",
				$_REQUEST["inputname"],
				$_REQUEST["inputadmin"],
				$_REQUEST["inputpassword"],
				$_REQUEST["inputid"]);
			$onloaddo="alert('Saved existing account ".$_REQUEST["inputid"]."');";
			$mydb->execute("Update broker ".
				"Set address=%s, ".
				"brokercat=%i, ".
				"phone=%s, ".
				"email=%s ".
				"where brokerid=%i",
				$_REQUEST["address"],
				$_REQUEST["brokercat"],
				$_REQUEST["phone"],
				$_REQUEST["email"],
				$_REQUEST["inputid"]);
			$onloaddo="alert('Saved existing account ".$_REQUEST["inputid"]."');";
			}
		}
	else if ($_REQUEST["submitter"]=="Search")
		{
		if ($_REQUEST["searchid"]!=null && $_REQUEST["searchid"]!="")
			{
			$queryres=$mydb->query("Select ".
			"person.Personid, ".
			"person.Personname, ".
			"person.isadmin, ".
			"person.password, ".
			"broker.address, ".
			"broker.brokercat, ".
			"broker.phone, ".
			"broker.email ".
			"from person full outer join ".
			"broker on person.Personid=broker.brokerid ".
			"where person.Personid=%i",
			$_REQUEST["searchid"]);
			if ($queryres->countReturnedRows()==0)
				$onloaddo="alert('ID ".$_REQUEST['searchid']." not found!');";
			else
				{
				$resultrow=$queryres->fetchRow();
				$inputid=$resultrow['Personid'];
				$inputname=$resultrow['Personname'];
				$inputpassword=$resultrow['password'];
				$isadmin=$resultrow['isadmin'];
				$address=$resultrow['address'];
				$brokercat=$resultrow['brokercat'];
				$phone=$resultrow['phone'];
				$email=$resultrow['email'];
				$formmode=3;
				}
			}
		else if ($_REQUEST["searchname"]!=null && $_REQUEST["searchname"]!="")
			{
			$queryres=$mydb->query("Select ".
			"person.Personid, ".
			"person.Personname, ".
			"person.isadmin, ".
			"person.password, ".
			"broker.address, ".
			"broker.brokercat, ".
			"broker.phone, ".
			"broker.email ".
			"from person full outer join ".
			"broker on person.Personid=broker.brokerid ".
			"where Personname like %s ",
			"%".$_REQUEST["searchname"]."%");
			if ($queryres->countReturnedRows()==0)
				$onloaddo="alert('Name ".$_REQUEST['searchname']." not found!');";
			else if ($queryres->countReturnedRows()==1)
				{
				$resultrow=$queryres->fetchRow();
				$inputid=$resultrow['Personid'];
				$inputname=$resultrow['Personname'];
				$inputpassword=$resultrow['password'];
				$isadmin=$resultrow['isadmin'];
				$address=$resultrow['address'];
				$brokercat=$resultrow['brokercat'];
				$phone=$resultrow['phone'];
				$email=$resultrow['email'];
				$formmode=3;
				}
			else
				{
				$onloaddo="addeditpage.searchid.focus();";
				$onloaddo=$onloaddo."addeditpage.searchid.select();";
				$formmode=2;
				}
			}
		}
		
		$catres=$mydb->query("Select brokercode, codedesc, limit from brokercat");
?>

<html>
<form name="addeditpage" method="post" action="AddEditPage.php">
	<head>
	<title>Add or Edit Accounts</title>
	
		<link rel="stylesheet" type="text/css" href="homepage.css">
		
		<script>
		function errorinfield(whichcontrol, errormsg) {
			alert(errormsg);
			whichcontrol.focus();
			whichcontrol.select();
			return false;
			}
			
		function validateinteger(whichcontrol, controlname) {
			var whatvalue=parseInt(whichcontrol.value);
			if (whichcontrol.value.length==0)
				return true;
			else if (isNaN(whatvalue))
				return errorinfield(whichcontrol,controlname+" must be an integer.");
			else if (whatvalue!=whichcontrol.value)
				return errorinfield(whichcontrol,controlname+" can not contain non-integer elements.");
			else
				return true;
			}
			
		function checksave() {
			if (addeditpage.inputadmin.item(0).checked==false && addeditpage.inputadmin.item(1).checked==false) 
				{
				alert('Please make sure the admin field is filled in');
				return false;
				}
			else if (addeditpage.inputadmin.item(0).checked==true) {
				var everythingfilled=true;
				var errormessage="";
				if (addeditpage.inputname.value.length==0){
					everythingfilled=false;
					errormessage+="\nName";
					document.getElementById("nameerror").innerHTML=' *';
					}
				else
					document.getElementById("nameerror")
					.innerHTML='';
				if (addeditpage.inputpassword.value.length==0){
					everythingfilled=false;
					errormessage+="\nPassword";
					document.getElementById("passworderror").innerHTML=' *';
					}
				else
					document.getElementById("passworderror")
					.innerHTML='';
				if (checkPassword()==false)
					alert('Make sure the form is filled out correctly before continuing');								
				if (!everythingfilled){				
					alert('You need to fill out the following fields:'+errormessage);
					return false;
					}
				else
					return confirm("Are you sure you wish to save?");
					} 			
			else if (addeditpage.inputadmin.item(1).checked==true) {
				var everythingfilled=true;
				var errormessage="";
				if (addeditpage.inputname.value.length==0){
					everythingfilled=false;
					errormessage+="\nName";
					document.getElementById("nameerror").innerHTML=' *';
					}
				else
					document.getElementById("nameerror")
					.innerHTML='';
				if (addeditpage.inputpassword.value.length==0){
					everythingfilled=false;
					errormessage+="\nPassword";
					document.getElementById("passworderror").innerHTML=' *';
					}
				else
					document.getElementById("passworderror")
					.innerHTML='';
				if (addeditpage.brokercat.value=='0'){
					everythingfilled=false;
					errormessage+="\nBroker Category";
					document.getElementById("brokercaterror").innerHTML='*';
					}
				else
					document.getElementById("brokercaterror").innerHTML='';
				if (addeditpage.address.value.length==0){
					everythingfilled=false;
					errormessage+="\nAddress";
					document.getElementById("addresserror").innerHTML=' *';
					}
				else
					document.getElementById("addresserror")
					.innerHTML='';
				if (addeditpage.phone.value.length==0){
					everythingfilled=false;
					errormessage+="\nPhone";
					document.getElementById("phoneerror").innerHTML=' *';
					}
				else
					document.getElementById("phoneerror")
					.innerHTML='';
				if (addeditpage.email.value.length==0){
					everythingfilled=false;
					errormessage+="\nEmail";
					document.getElementById("emailerror").innerHTML=' *';
					}
				else
					document.getElementById("emailerror")
					.innerHTML='';
				if (checkPassword()==false || checkAddress()==false || checkPhone()==false || checkEmail()==false)
					alert('Make sure the form is filled out correctly before continuing');
				if (!everythingfilled){				
					alert('You need to fill out the following fields:'+errormessage);
					return false;
					}
				else
					return confirm("Are you sure you wish to save?");
					} 
			}
			
		function NewAccount() {
			document.getElementById("inputid").innerHTML="(System Specified)";
			addeditpage.inputid.disabled=false;
			addeditpage.inputid.value="(System Specified)";
			addeditpage.inputname.disabled=false;
			addeditpage.inputname.focus();
			addeditpage.inputname.select();
			addeditpage.inputpassword.disabled=false;
			addeditpage.inputadmin.item(0).disabled=false;
			addeditpage.inputadmin.item(1).disabled=false;
			addeditpage.brokercat.disabled=false;
			addeditpage.address.disabled=false;
			addeditpage.phone.disabled=false;
			addeditpage.email.disabled=false;
			addeditpage.submitter.item(2).disabled=false;
			addeditpage.cancelbutt.disabled=false;
			addeditpage.createaccount.disabled=true;
			addeditpage.submitter.item(1).disabled=true;
			addeditpage.searchid.disabled=true;
			addeditpage.searchname.disabled=true;
			}
			
		function loadfunction() {
			<?php echo $onloaddo; ?>
			var vis_var = "<?php echo $isadmin; ?>";
			if (vis_var == "N")
				document.getElementById('broker').style.visibility='visible';
			else
				document.getElementById('broker').style.visibility='hidden';
		}
		
		function NameSelect() {
			var myhidden=document.createElement("input");
			myhidden.type="hidden";
			myhidden.name="submitter";
			myhidden.value="Search";
			addeditpage.appendChild(myhidden);
			addeditpage.submit();
		}
		
		function cancelbuttclick() {
			if (confirm("Really cancel?")) {
				document.getElementById("inputid").innerHTML="(System Specified)";
				addeditpage.createaccount.disabled=false;
				addeditpage.createaccount.focus();
				addeditpage.searchid.disabled=false;
				addeditpage.searchname.disabled=false;
				addeditpage.submitter.item(1).disabled=false;
				addeditpage.inputid.value="";
				addeditpage.inputname.disabled=true;
				addeditpage.inputpassword.disabled=true;
				addeditpage.inputadmin.item(0).checked=false;
				addeditpage.inputadmin.item(0).disabled=true;
				addeditpage.inputadmin.item(1).checked=false;
				addeditpage.inputadmin.item(1).disabled=true;
				addeditpage.brokercat.value=0;
				addeditpage.brokercat.disabled=true;
				addeditpage.address.disabled=true;
				addeditpage.phone.disabled=true;
				addeditpage.email.disabled=true;
				addeditpage.submitter.item(2).disabled=true;
				addeditpage.cancelbutt.disabled=true;
				}
		}
		
		function visible() {
			if (addeditpage.inputadmin.item(1).checked)
				document.getElementById('broker').style.visibility='visible'; 
			else
				document.getElementById('broker').style.visibility='hidden';
		}
		
		function checkPassword() {
			var password = document.getElementById('password');
			var val = password.value
			if(val.length < 8)
			{
			document.getElementById("passwordvalid").innerHTML=' Your password must be atleast 8 characters';
			password.focus;
			return false;
			}
			else
				document.getElementById("passwordvalid").innerHTML='';
		}
				
		function checkAddress() {
			var address = document.getElementById('address');
			var val = address.value
			if((val.length < 20) || (val.length > 100))
			{
			document.getElementById("addresserror").innerHTML=' Your address must be 20 to 100 characters';
			address.focus;
			return false;
			}
			else
				document.getElementById("addresserror").innerHTML='';
		}
		
		function checkPhone() {
			var phone = document.getElementById('phone');
			var filter = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/; 
			if (!filter.test(phone.value)) 
				{
				document.getElementById("phoneerror").innerHTML=' Your phone number must be in the form xxx-xxx-xxxx';
				phone.focus;
				return false;
				}
			else
				document.getElementById("phoneerror").innerHTML='';
		}
				
		function checkEmail() {
			var email = document.getElementById('email');
			var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			if (!filter.test(email.value)) 
				{
				document.getElementById("emailerror").innerHTML=' Please provide a valid email address';
				email.focus;
				return false;
				}
			else
				document.getElementById("emailerror").innerHTML='';
		}
		</script>
		
	</head>
	
	<body onload="loadfunction();" >
	
	<!--Header-->
	
	<div id="header">
	<img src="LogoGreenSmall.png" width="100px" alt="Commodity Brokerage" class="logo">
	<img src="CB Header.png" width="300px" alt="Commodity Brokerage" class="logo">
	<input type="submit" name="submitter" value="Log Out" class="logout" />
	</div>
	
	<!--Sidebar-->
	
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
	
	<!--Search-->
	
	<div id="content">
	
		<div id="searchsection">
			<table>
			
			<tr>
				<td colspan="3"><input type="button" name="createaccount" value="Create New Account" class="create" onclick="NewAccount()" <?php if ($formmode>1) echo 'disabled'; ?> /></td>
			</tr>
			
			<tr>
				<td colspan="3"><b>Search by</b></td>
			</tr>
			
			<tr>
				<td><p>ID &nbsp; </p></td>
				<td>
				<input type="text" name="searchid" <?php if ($formmode>1) echo 'disabled'; ?> value="" onblur="validateinteger(addeditpage.searchid,'The ID')" />
				</td>
			</tr>
			
			<tr>
				<td><p>Name &nbsp; </p></td> 
				<td>
				<?php if ($formmode!=2) {?>
				<input type="text" name="searchname" value="" <?php if ($formmode>1) echo 'disabled'; ?> />
				<?php
				}
				else
				{
				?>
				<select name="searchid" class="select" onchange="NameSelect();" >
				<?php
				foreach ($queryres as $row)
				{
				?>
				<option value="<?php echo $row['Personid'];?>"><?php echo $row['Personname']; ?></option>
				<?php
				}
				?>
				</select>
				<?php
				}
				?>
				</td>
				<td id="wildcardtext" class="error"><?php if ($formmode==2) echo 'Multiple matches found. Which name are you looking for?'; ?></td>
			</tr>
			
			<tr>
			<td colspan="3"><input type="submit" name="submitter" value="Search" class="search" <?php if ($formmode>1) echo 'disabled'; ?> /></td>
			</tr>
			
			</table>
		</div>
		
		<!--Input-->
		
		<div id="personsection">
			<div id="buttonspace">
			<input type="submit" name="submitter" value="Save" class="saveorcancel" id="save" onclick="return checksave();" <?php if ($formmode<3) echo 'disabled'; ?> />
			<input type="button" name="cancelbutt" value="Cancel" class="saveorcancel" onclick="return cancelbuttclick();" <?php if ($formmode<3) echo 'disabled'; ?> />
			</div>
			<table>
				<tr>
					<td><b>ID &nbsp; </b></td>
					<td ><span id="inputid"><?php echo $inputid; ?></span><input type="hidden" name="inputid" value="<?php echo $inputid; ?>" 
					<?php if ($formmode<3) echo 'disabled'; ?> /></td>
				</tr>
				
				<tr>
					<td><p>Name &nbsp; </p></td> 
					<td><input type="text" name="inputname" value="<?php echo $inputname; ?>" 
					<?php if ($formmode<3) echo 'disabled'; ?> />
					<span class="error" id="nameerror" ></span></td>
				</tr>
				
				<tr>
					<td><p>Password &nbsp; </p></td> 
					<td><input type="password" name="inputpassword" value="<?php echo $inputpassword; ?>" id="password" onblur="checkPassword()"
					<?php if ($formmode<3) echo 'disabled'; ?> />
					<span class="error" id="passworderror"></span><span class="error" id="passwordvalid"></span></td>
				</tr>
				
				<tr>
					<td><p>Admin or Broker &nbsp; </p></td> 
					<td><input type="radio" name="inputadmin" value="Y" onclick="visible();" <?php if ($formmode<3) echo 'disabled'; ?> 
					<?php if ($isadmin=='Y') echo 'checked'; ?> /><p> &nbsp; This person is an admin</p>
					<span class="error" id="adminerror"></span></td>
				</tr>
				
				<tr>
					<td></td> 
					<td><input type="radio" name="inputadmin" value="N" onclick="visible();" <?php if ($formmode<3) echo 'disabled'; ?> 
					<?php if ($isadmin=='N') echo 'checked'; ?>/><p> &nbsp; This person is a broker</p></td>
				</tr>
			</table>
		</div>
		
	<!--Broker-->
	
	<div id="brokersection">
	<table id="broker">
	
	<tr>
	<td><p>Broker Category &nbsp; </p></td>
	<td>
	<select name="brokercat" class="select" <?php if ($formmode<3) echo 'disabled'; ?> >
		<option value="0" Selected></option>
		<?php 
		foreach($catres as $row) {
		?>
		<option value="<?php echo $row['brokercode']; ?>" <?php if ($brokercat==$row["brokercode"]) echo 'selected'; ?> >
		<?php echo $row["codedesc"]; ?> - <?php echo $row["limit"]; ?>
		</option>
		<?php
		}
		?>
	</select>
	</td>
	<td><span class="error" id="brokercaterror"></span></td>
	</tr>
	
	<tr>
	<td><p>Address &nbsp; </p></td>
	<td><textarea name="address" rows="3" cols="20" class="textarea" id="address" onblur="checkAddress()" <?php if ($formmode<3) echo 'disabled'; ?> ><?php echo $address; ?></textarea></td>
	<td><span class="error" id="addresserror"></span></td>
	</tr>
	
	<tr>
	<td><p>Phone Number &nbsp; </p></td>
	<td><input type="text" name="phone" value="<?php echo $phone; ?>" id="phone" onblur="checkPhone()" <?php if ($formmode<3) echo 'disabled'; ?> /></td>
	<td><span class="error" id="phoneerror"></span></td>
	</tr>
	
	<tr>
	<td><p>Email &nbsp; </p></td>
	<td><input type="text" name="email" value="<?php echo $email; ?>" id="email" onblur="checkEmail()" <?php if ($formmode<3) echo 'disabled'; ?> /></td>
	<td><span class="error" id="emailerror"></span></td>
	</tr>
	</table>
	</div>

	</div>
		
	</div>
	
	<!--Footer-->
	
	<div id="footer">
	<center><p>For queries, please contact the <a href="mailto:aper543.auckland.ac.nz">webmaster</a>.</p>
	</div>
	
	</body>
</form>
</html>