<?php
require_once("site.config.php");
define("NO_SESSION",true);

$import_csv_file = "sf_accts.csv";
define("IMPORT_SOURCE","sf"); // should keep this the same

define("NUM_FIELDS", 13);
define("TEST_MODE", true);

set_time_limit(3600); // 1hr

function clean_import($str) {
	$bad_ansi = array(
		chr(0),
		chr(1),
		chr(2),
		chr(3),
		chr(4),
		chr(5),
		chr(6),
		chr(7),
		chr(8),
		chr(9),
		chr(11),
		chr(12),
		chr(14),
		chr(15),
		chr(16),
		chr(17),
		chr(18),
		chr(19)
	);
	return str_replace($bad_ansi,"_",$str);
}

//##########################################

if(!file_exists($import_csv_file))
	die("File '$import_csv_file' does not exist!");

if(!($fp = fopen($import_csv_file, "rt"))) {
	die("ERROR: Could not read CSV import file.");
}

// [JAS]: Read in characters to limit or to the first CR/LF.
while($line = fgets($fp, 2048)) {

	$line = trim($line); // remove whitespace on ends
	
//	echo $line . "<br>";

	$line = substr($line,1,strlen($line)-2); // strip outer quotes
	$fields = split('","', $line);
	
	if(!is_array($fields) || count($fields) != NUM_FIELDS) {
		echo "Skipping a line that doesn't have enough fields ($line).<br>";
		continue;
	}
	
	// [JAS]: Are we just displaying the data, or actually doing something with it?
	if(TEST_MODE) {
		echo "Name: " . $fields[0] . "<BR>";
		echo "Acct #: " . $fields[1] . "<BR>";
		echo "Addy 1: " . $fields[2] . "<BR>";
		echo "Addy 2: " . $fields[3] . "<BR>";
		echo "Addy 3: " . $fields[4] . "<BR>";
		echo "City: " . $fields[5] . "<BR>";
		echo "State: " . $fields[6] . "<BR>";
		echo "Zip: " . $fields[7] . "<BR>";
		echo "Country: " . $fields[8] . "<BR>";
		echo "Phone: " . $fields[9] . "<BR>";
		echo "Fax: " . $fields[10] . "<BR>";
		echo "Website: " . $fields[11] . "<BR>";
		echo "Created Date: " . $fields[12] . "<BR>";
		echo "<HR>";
	}
	else {
		// [JAS]: [TODO] Make sure the import id hasn't already been imported.
		$sql = sprintf("SELECT c.id,c.name,c.import_id FROM company c WHERE c.import_id = BINARY %s AND c.import_source = %s",
			$cerberus_db->escape($fields[1]),
			$cerberus_db->escape(IMPORT_SOURCE)
		);
		$count_res = $cerberus_db->query($sql);

		if($cerberus_db->num_rows($count_res) > 0) {
			$dupe_row = $cerberus_db->grab_first_row($count_res);
			echo $fields[0] . " (" . $fields[1] . ") is a dupe of " . $dupe_row["name"] . " (" .$dupe_row["import_id"] . ")<br>";
			flush();
			continue; // dupe import
		}
		
		// [JAS]: Combine the addy 1-3 fields.
		$street_address = "";
		if(!empty($fields[2])) $street_address = $fields[2];
		if(!empty($fields[3])) $street_address .= "\r\n" . $fields[3];
		if(!empty($fields[4])) $street_address .= "\r\n" . $fields[4];
		
		// [JAS]: Determine country id
		$country_name = $fields[8];
		$country_id = 0;
		
		if($country_name == "USA" || $country_name == "US")
			$country_name = "United States";
		
		if(!empty($country_name)) {
			$sql = sprintf("SELECT c.country_id FROM country c WHERE c.country_name = %s",
					$cerberus_db->escape($country_name)
				);
			$country_res = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($country_res)) {
				$country_row = $cerberus_db->grab_first_row($country_res);
				$country_id = $country_row["country_id"];
			}
		}
		
		$created_date = gmmktime();
		if(!empty($fields[12])) {
			$created_date = strtotime($fields[12]);
		}
			
		$sql = "INSERT INTO company (name,import_source,import_id,company_mailing_address,company_mailing_city,company_mailing_state,".
				"company_mailing_zip,company_mailing_country_id,company_phone,company_fax,company_website,created_date) ".
			sprintf(
					"VALUES(%s,%s,%s,%s,%s,%s,%s,%d,%s,%s,%s,%d)",
					$cerberus_db->escape(stripslashes($fields[0])),
					$cerberus_db->escape(IMPORT_SOURCE),
					$cerberus_db->escape($fields[1]),
					$cerberus_db->escape(stripslashes($street_address)),
					$cerberus_db->escape(stripslashes($fields[5])),
					$cerberus_db->escape(stripslashes($fields[6])),
					$cerberus_db->escape(stripslashes($fields[7])),
					$country_id,
					$cerberus_db->escape(stripslashes($fields[9])),
					$cerberus_db->escape(stripslashes($fields[10])),
					$cerberus_db->escape(stripslashes($fields[11])),
					$created_date
				);

		$sql = clean_import($sql);
		$cerberus_db->query($sql);
	}
}


?>