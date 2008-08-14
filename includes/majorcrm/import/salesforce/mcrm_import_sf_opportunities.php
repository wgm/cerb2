<?php
require_once("site.config.php");
define("NO_SESSION",true);

$import_csv_file = "sf_opportunities.csv";
define("IMPORT_SOURCE","sf"); // should keep this the same

define("NUM_FIELDS", 8);
define("DEFAULT_CLOSE_DATE",gmmktime(0,0,0,12,31,2005));
define("TEST_MODE", true);

set_time_limit(3600); // 1hr

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
		echo "Opp #: " . $fields[0] . "<BR>";
		echo "Acct #: " . $fields[1] . "<BR>";
		echo "Name: " . $fields[2] . "<BR>";
		echo "Amount: " . $fields[3] . "<BR>";
		echo "Close Date: " . $fields[4] . "<BR>";
		echo "Stage: " . $fields[5] . "<BR>";
		echo "Probability: " . $fields[6] . "<BR>";
		echo "Created Date: " . $fields[7] . "<BR>";
		echo "<HR>";
	}
	else {
		// [JAS]: [TODO] Make sure the import id hasn't already been imported.
		$sql = sprintf("SELECT o.opportunity_id,o.opportunity_name,o.import_id FROM opportunity o WHERE o.import_id = BINARY %s AND o.import_source = %s",
			$cerberus_db->escape($fields[0]),
			$cerberus_db->escape(IMPORT_SOURCE)
		);
		$count_res = $cerberus_db->query($sql);
		if($cerberus_db->num_rows($count_res) > 0) {
			$dupe_row = $cerberus_db->grab_first_row($count_res);
			echo $fields[2] . " (" . $fields[0] . ") is a dupe of " . $dupe_row["opportunity_name"] . " (" .$dupe_row["import_id"] . ")<br>";
			continue; // dupe import
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
		
		// [JAS]: [TODO] This should handle other currencies later as well
		$amount = 0.00;
		if(!empty($fields[3]))
			$amount = str_replace(array("$",","),"",$fields[3]);
		
		$date = DEFAULT_CLOSE_DATE;
		if(!empty($fields[4])) {
			$date = strtotime($fields[4]);
		}

		$created_date = gmmktime();
		if(!empty($fields[7])) {
			$created_date = strtotime($fields[7]);
		}
		
		$stage = "Prospecting";
		if(!empty($fields[5]))
			$stage = $fields[5];
		
		$sql = "INSERT INTO opportunity (opportunity_name,import_source,import_id,company_id,amount,".
				"close_date,probability,stage,created_date) ".
			sprintf(
					"VALUES(%s,%s,%s,%d,%f,%d,%d,%s,%d)",
					$cerberus_db->escape(stripslashes($fields[2])),
					$cerberus_db->escape(IMPORT_SOURCE),
					$cerberus_db->escape($fields[0]),
					$company_id,
					$amount,
					$date,
					$fields[6],
					$cerberus_db->escape(stripslashes($stage)),
					$created_date
				);
		$cerberus_db->query($sql);
	}
}


?>