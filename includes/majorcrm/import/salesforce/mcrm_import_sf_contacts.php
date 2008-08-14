<?php
require_once("site.config.php");
define("NO_SESSION",true);

$import_csv_file = "sf_contacts.csv";
define("IMPORT_SOURCE","sf"); // should keep this the same

define("NUM_FIELDS", 18);
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

$company_hash = array();

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
		echo "Contact #: " . $fields[0] . "<BR>";
		echo "Acct #: " . $fields[1] . "<BR>";
		echo "Sal: " . $fields[2] . "<BR>";
		echo "First: " . $fields[3] . "<BR>";
		echo "Last: " . $fields[4] . "<BR>";
		echo "Addy 1: " . $fields[5] . "<BR>";
		echo "Addy 2: " . $fields[6] . "<BR>";
		echo "Addy 3: " . $fields[7] . "<BR>";
		echo "City: " . $fields[8] . "<BR>";
		echo "State: " . $fields[9] . "<BR>";
		echo "Zip: " . $fields[10] . "<BR>";
		echo "Country: " . $fields[11] . "<BR>";
		echo "Phone: " . $fields[12] . "<BR>";
		echo "Mobile: " . $fields[13] . "<BR>";
		echo "Home: " . $fields[14] . "<BR>";
		echo "Fax: " . $fields[15] . "<BR>";
		echo "Email: " . $fields[16] . "<BR>";
		echo "Created Date: " . $fields[17] . "<BR>";
		echo "<HR>";
	}
	else {
		// [JAS]: [TODO] Make sure the import id hasn't already been imported.
		$sql = sprintf("SELECT pu.public_user_id,pu.name_first,pu.name_last,pu.import_id FROM public_gui_users pu WHERE pu.import_id = BINARY %s AND pu.import_source = %s",
				$cerberus_db->escape($fields[0]),
				$cerberus_db->escape(IMPORT_SOURCE)
			);
		$count_res = $cerberus_db->query($sql);

		if($cerberus_db->num_rows($count_res) > 0) {
			$dupe_row = $cerberus_db->grab_first_row($count_res);
			echo $fields[3] . " " . $fields[4] . " (" . $fields[0] . ") is a dupe of " . $dupe_row["name_first"] . " " . $dupe_row["name_last"] . " (" .$dupe_row["import_id"] . ")<br>";
			flush();
			continue; // dupe import
		}
		
		// [JAS]: Combine the addy 1-3 fields.
		$street_address = "";
		if(!empty($fields[5])) $street_address = $fields[5];
		if(!empty($fields[6])) $street_address .= "\r\n" . $fields[6];
		if(!empty($fields[7])) $street_address .= "\r\n" . $fields[7];
		
		// [JAS]: Determine country id
		$country_name = $fields[11];
		$country_id = 0;
		
		if($country_name == "USA" || $country_name == "US" || $country_name == "United States")
			$country_id = 250;
		if($country_name == "UK" || $country_name == "United Kingdom")
			$country_id = 249;
		
		if(!empty($country_name) && empty($country_id)) {
			$sql = sprintf("SELECT c.country_id FROM country c WHERE c.country_name = %s",
					$cerberus_db->escape($country_name)
				);
			$country_res = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($country_res)) {
				$country_row = $cerberus_db->grab_first_row($country_res);
				$country_id = $country_row["country_id"];
			}
		}
		
		// [JAS]: Find the local company ID for the remote account ID
		$company_id = 0;
		if(!empty($fields[1])) {
			if(!isset($company_hash[$fields[1]])) {
				$sql = sprintf("SELECT c.id FROM company c WHERE c.import_source = %s AND c.import_id = BINARY %s",
						$cerberus_db->escape(IMPORT_SOURCE),
						$cerberus_db->escape($fields[1])
					);
				$cid_res = $cerberus_db->query($sql);
				if($cerberus_db->num_rows($cid_res)) {
					$cid_row = $cerberus_db->grab_first_row($cid_res);
					$company_id = $cid_row["id"];
					$company_hash[$fields[1]] = $company_id;
				}
			}
			else {
				$company_id = $company_hash[$fields[1]];
			}
		}
		
		$created_date = gmmktime();
		if(!empty($fields[17])) {
			$created_date = strtotime($fields[17]);
		}
		
		$sql = "INSERT INTO public_gui_users (name_first,name_last,import_source,import_id,company_id,mailing_address,".
				"mailing_city,mailing_state,mailing_zip,mailing_country_id,phone_work,phone_home,phone_mobile,phone_fax,created_date) ".
			sprintf(
					"VALUES(%s,%s,%s,%s,%d,%s,%s,%s,%s,%d,%s,%s,%s,%s,%d)",
					$cerberus_db->escape(stripslashes($fields[3])),
					$cerberus_db->escape(stripslashes($fields[4])),
					$cerberus_db->escape(IMPORT_SOURCE),
					$cerberus_db->escape($fields[0]),
					$company_id,
					$cerberus_db->escape(stripslashes($street_address)),
					$cerberus_db->escape(stripslashes($fields[8])),
					$cerberus_db->escape(stripslashes($fields[9])),
					$cerberus_db->escape(stripslashes($fields[10])),
					$country_id,
					$cerberus_db->escape(stripslashes($fields[12])),
					$cerberus_db->escape(stripslashes($fields[14])),
					$cerberus_db->escape(stripslashes($fields[13])),
					$cerberus_db->escape(stripslashes($fields[15])),
					$created_date
				);
		$sql = clean_import($sql);
		$cerberus_db->query($sql);

		$contact_id = $cerberus_db->insert_id();
		
//		echo "QUERY [new: $contact_id)]: " . $sql . "<HR>";

		// [JAS]: Contact e-mail (add/assign to contact if available)
		if(!empty($fields[16])) {
			$sql = sprintf("SELECT a.address_id, a.public_user_id FROM address a WHERE a.address_address = %s",
					$cerberus_db->escape($fields[16])
				);
			$addy_res = $cerberus_db->query($sql);
			
			// The address exists in the DB already
			if($cerberus_db->num_rows($addy_res)) {
				$addy_row = $cerberus_db->grab_first_row($addy_res);
				if($addy_row["public_user_id"] == 0) { // unassigned
					$sql = sprintf("UPDATE address SET public_user_id = %d WHERE address_id = %d AND public_user_id = 0",
							$contact_id,
							$addy_row["address_id"]
						);
					$cerberus_db->query($sql);
				}
				else { // assigned, do nothing
//					echo "Address " . $addy_row["address_id"] . " is already assigned.<br>";
				}
			}
			else { // the address is available for insert
				$sql = sprintf("INSERT INTO address (address_address, public_user_id) ".
							"VALUES (%s,%d)",
								$cerberus_db->escape($fields[16]),
								$contact_id
						);
				$cerberus_db->query($sql);
			}
		}
		
	} // end else
}


?>