<html>
<head>
<title>Test XML + File Post form for Cerberus 2</title>
</head>
<body>
<?php
if(!isset($_POST["filename"])) {
	if(empty($PHP_SELF)) {
		$PHP_SELF = $_SERVER["PHP_SELF"];
	}
?>
To use this form you have to post twice so we can get the form set up properly with the testpost.file location.
Upload this file: <a href="./testpost.file">testpost.file</a>
<form action="<?php echo $PHP_SELF; ?>" method="POST">
Your parser location: <input type="text" name="parser" value="http://www.domain.com/cerberus-gui/parser.php"><br />
Your email address: <input type="text" name="from" value="your@emailaddress.com"><br />
Queue email address: <input type="text" name="to" value="cerberus@address.com"><br />
Filename from above: <input type="file" name="filename"><br />
<input type="submit" value="To step 2!"><br />
</form>
<?php
} else {
?>
<form action="<?php echo $_POST["parser"]; ?>" method="POST" enctype="multipart/form-data">
XML To be Sent:<textarea name="xml" cols="80" rows="30">
<email>
  <file>
    <tempname>
      <?php echo $_POST["filename"]; ?>
    </tempname>
  </file>
  <headers>
    <from>
      <?php echo $_POST["from"]; ?>
    </from>
    <subject>
      This is a test!
    </subject>
    <to>
      <?php echo $_POST["to"]; ?>
    </to>
  </headers>
</email>
</textarea><br />
Select the same file again: <input type="file" name="<?php echo $_POST["filename"]; ?>" value="<?php echo $_POST["filename"]; ?>"><br />
<input type="submit" value="Test it!"><br />
</form>
<?php } ?>
</body>
</html>
