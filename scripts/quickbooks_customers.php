<?php

// this is a little bit of a hack
// this script is designed to export lists of "customers" in a CSV file that QuickBooks is ready to read
// the lists of people are selected in chunks, and discrete, consecutive chunks can be chosen by passing
// different start and end values to the script as GET parameters
	// 0. All donors
	// 1. All enrolled 2008 Trekkers
	// 2. All 2007 trekkers not yet enrolled for 2008
	// 3. All donors who have given jointly, as one joint customer "Mrs. Courtney and Mr. Jeff Zemsky"

require_once ("../library.inc.php");
require_once ("../csv.inc.php");

$start = (isset ($_GET["start"]) ? $_GET["start"] : 0);
$end = (isset ($_GET["end"]) ? $_GET["end"] : 3);

set_time_limit (6000);

csv_start ("quickbooks");

csv ("JOB OR CUSTOMER NAME"); // Name ("%L %D, %F %m)
csv ("SALUTATION");
csv ("FIRST NAME");
csv ("MIDDLE INITIAL"); // extract from middle name
csv ("LAST NAME");
csv ("COMPANY NAME"); // only for business type contacts
csv ("PHONE"); // highest ranking phone number
csv ("EMAIL"); // highest ranking email address
csv ("CONTACT"); // whole salutation "Mrs. Courtney and Mr. Jeffrey Zemsky"
csv ("BILLING ADDRESS 1"); // address needs to include contact as first line
csv ("BILLING ADDRESS 2"); // construct address line by line from database (no separate city, state, zip or country)
csv ("BILLING ADDRESS 3");
csv ("BILLING ADDRESS 4");
csv ("BILLING ADDRESS 5");
csv ("NOTE"); // trek years
csv ("CUSTOMER TYPE"); // "Donor" for donors, all others blank
csv ("ACCOUNT NUMBER"); // volatile -- meant for credit card number, currently used for contact ID

csv_newline ();

for ($step = $start; $step <= $end; $step++)
{
	$sql = NULL;
	switch ($step)
	{
		case 0: // we're collecting donors
			$sql = "SELECT c.`contact_id`, ct.`contact_type`, t.`title`, c.`first_name`, c.`middle_name`, c.`primary_name`, deg.`degree`
					FROM `contacts` AS c
					JOIN `donation_associations` AS da ON da.`contact_id` = c.`contact_id`
					JOIN `donations` AS d ON d.`donation_id` = da.`donation_id`
					JOIN `contact_types` AS ct ON ct.`contact_type_id` = c.`contact_type_id`
					LEFT JOIN `titles` AS t ON t.`title_id` = c.`title_id`
					LEFT JOIN `degrees` AS deg ON deg.`degree_id` = c.`degree_id`
					WHERE d.`date` >= '2001-10-01'
					GROUP BY c.`contact_id`
					ORDER BY c.`primary_name` ASC, c.`first_name` ASC";
			break;
		case 1: // we're collecting 2008 Trekkers
			$sql = "SELECT c.`contact_id`, ct.`contact_type`, t.`title`, c.`first_name`, c.`middle_name`, c.`primary_name`, deg.`degree`
					FROM `contacts` AS c
					JOIN `roster_memberships` AS m ON m.`contact_id` = c.`contact_id`
					JOIN `rosters` AS r ON r.`roster_id` = m.`roster_id`
					JOIN `groups` AS g ON g.`group_id` = r.`group_id`
					JOIN `contact_types` AS ct ON ct.`contact_type_id` = c.`contact_type_id`
					LEFT JOIN `titles` AS t ON t.`title_id` = c.`title_id`
					LEFT JOIN `degrees` AS deg ON deg.`degree_id` = c.`degree_id`
					WHERE r.`year` = '2008' AND g.`excluded` = '0'
					GROUP BY c.`contact_id`
					ORDER BY c.`primary_name` ASC, c.`first_name` ASC";
			break;
		case 2: // we're collecting currently unenrolled 2007 trekkers (not a perfect query)
			$sql = "SELECT c.`contact_id`, ct.`contact_type`, t.`title`, c.`first_name`, c.`middle_name`, c.`primary_name`, deg.`degree`
					FROM `contacts` AS c
					JOIN `roster_memberships` AS m ON m.`contact_id` = c.`contact_id`
					JOIN `roles` AS l ON l.`role_id` = m.`role_id`
					JOIN `rosters` AS r ON r.`roster_id` = m.`roster_id` AND r.`year` = '2007'
					JOIN `groups` AS g ON g.`group_id` = r.`group_id`
					JOIN `contact_types` AS ct ON ct.`contact_type_id` = c.`contact_type_id`
					LEFT JOIN `titles` AS t ON t.`title_id` = c.`title_id`
					LEFT JOIN `degrees` AS deg ON deg.`degree_id` = c.`degree_id`
					WHERE g.`excluded` = '0' AND l.`is_staff` = '0'
					GROUP BY c.`contact_id`
					ORDER BY c.`primary_name` ASC, c.`first_name` ASC";
			break;
		case 3: // we're collecting all joint donors
			$sql = "SELECT c.`contact_id` , d.`donation_id` , COUNT( da.`donation_assocation_id`  )  AS  `donors`
					FROM  `donations`  AS d
					JOIN  `donation_associations`  AS da ON da.`donation_id`  = d.`donation_id`
					JOIN  `contacts`  AS c ON c.`contact_id`  = d.`donor_id`
					GROUP  BY d.`donation_id`
					HAVING  `donors`  >1
					ORDER  BY c.`primary_name` ASC, c.`first_name` ASC ";
			break;

	}

	$contacts = mysql_query ($sql) or exit_sql_error ($sql);
	$used_cid = NULL;

	while ($c = mysql_fetch_assoc ($contacts))
	{
		$skip = false;
		$donors = NULL;

		// any special instructions for this list, including whether or not to skip this person
		switch ($step)
		{
			case 2: // we're looking for not-yet-enrolled 2005 trekkers in 2006, so skip 'em if they're enrolled
				$sql = "SELECT * FROM `roster_memberships` AS m JOIN `rosters` AS r ON r.`roster_id` = m.`roster_id` WHERE m.`contact_id` = '{$c["contact_id"]}' AND r.`year` = '2006'";
				$result = mysql_query ($sql) or exit_sql_error ($sql);
				$skip = (mysql_num_rows ($result) > 0);
				mysql_free_result ($result);
				break;
			case 3: // this "customer" is a collection of donors
				$sql = "SELECT * FROM `contacts` AS c
						JOIN `donation_associations` AS da ON da.`contact_id` = c.`contact_id`
						WHERE da.`donation_id` = '{$c["donation_id"]}'
						ORDER BY c.`primary_name` ASC, c.`gender` DESC";
				$donor_result = mysql_query ($sql) or exit_sql_error ($sql);
				for ($i = 0; $i < mysql_num_rows ($donor_result); $i++)
				{
					$donors[$i] = mysql_fetch_assoc ($donor_result);
				}
				mysql_free_result ($donor_result);
				break;
		}

		if (!$skip)
		{
			$job_or_customer_name = "";
			$salutation = "";
			$first_name = "";
			$middle_initial = "";
			$last_name = "";
			$company_name = "";
			$phone = "";
			$email = "";
			$contact = "";
			$billing_address[1] = "";
			$billing_address[2] = "";
			$billing_address[3] = "";
			$billing_address[4] = "";
			$billing_address[5] = "";
			$note = "";
			$customer_type = (($step == 0) || ($step == 3)) ? "Donor" : "";
			$account_number = $c["contact_id"];

			if ($step == 3) // we need to combine some donors
			{
				// generate the combined account number and determine how many last names we're dealing with
				$account_number = "";
				for ($i = 0; $i < sizeof ($donors); $i++)
				{
					$account_number = dbAddToList ($account_number, $donors[$i]["contact_id"], "-");
				}

				for ($i = 0; $i < sizeof ($donors); $i++)
				{
					$contact = dbAddToList ($contact, Name ($donors[$i]["contact_id"], "%T %F %m"));
					$job_or_customer_name = dbAddToList ($job_or_customer_name, Name($donors[$i]["contact_id"],"%L, %F %m"), " and ");
				}
				$contact .= " {$donors[0]["primary_name"]}";
				$comma = strrpos ($contact, ",");
				if ($comma !== false)
				{
					$contact = substr ($contact, 0, $comma) . " and" . substr ($contact, $comma + 1);
				}
			}
			else
			{

				if ($c["contact_type"] == "Individual")
				{
					$job_or_customer_name = Name ($c["contact_id"], "%L %D, %F %m");
					$salutation = $c["title"];
					$first_name = $c["first_name"];
					$middle_initial = (strlen ($c["middle_name"]) ? $c["middle_name"][0] : "");
					$last_name = Name ($c["contact_id"], "%L %D");
					$contact = Name ($c["contact_id"], "%T %F %m %L %D");
				}
				else if ($c["contact_type"] == "Business")
				{
					$job_or_customer_name = $c["primary_name"];
					$company_name = $c["primary_name"];
					$contact = $c["primary_name"];
				}
			}

			if (isset ($used_cid[$account_number]))
			{
				continue;
			}
			else
			{
				$used_cid[$account_number] = true;
			}

			$sql = "SELECT a.* FROM `address_associations` AS aa
						JOIN `addresses` AS a ON a.`address_id` = aa.`address_id`
						JOIN `address_types` AS at ON at.`address_type_id` = a.`address_type_id`
						WHERE aa.`contact_id` = '{$c["contact_id"]}'
						GROUP BY a.`address_id`
						ORDER BY at.`rank` ASC
						LIMIT 1";
			$result = mysql_query ($sql) or exit_sql_error ($sql);
			$address = mysql_fetch_assoc ($result);

			if ($address)
			{
				$line = 1;
				$billing_address[$line++] = $contact;
				if (strlen ($address["street_address_1"]))
				{
					$billing_address[$line++] = $address["street_address_1"];
				}
				if (strlen ($address["street_address_2"]))
				{
					$billing_address[$line++] = $address["street_address_2"];
				}
				if (strlen ($address["city"]))
				{
					$billing_address[$line] = $address["city"];
				}
				if (strlen ($address["state"]))
				{
					$billing_address[$line] .= ", {$address["state"]}";
				}
				if (strlen ($address["postal_code"]))
				{
					$billing_address[$line] .= " {$address["postal_code"]}";
				}
				$line++;
				if ((strlen ($address["country"])) && ($address["country"] != "United States"))
				{
					$billing_address[$line++] = $address["country"];
				}
			}

			$sql = "SELECT p.* FROM `phone_associations` AS pa
						JOIN `phones` AS p ON p.`phone_id` = pa.`phone_id`
						JOIN `phone_types` AS pt ON pt.`phone_type_id` = p.`phone_type_id`
						WHERE pa.`contact_id` = '{$c["contact_id"]}'
						GROUP BY p.`phone_id`
						ORDER BY pt.`rank` ASC
						LIMIT 1";
			$result = mysql_query ($sql) or exit_sql_error ($sql);
			$_phone = mysql_fetch_assoc ($result);

			if ($_phone)
			{
				$phone = dbPhone ($_phone["number"]);
			}

			$sql = "SELECT e.* FROM `email_associations` AS ea
						JOIN `emails` AS e ON e.`email_id` = ea.`email_id`
						JOIN `email_types` AS et ON et.`email_type_id` = e.`email_type_id`
						WHERE ea.`contact_id` = '{$c["contact_id"]}'
						GROUP BY e.email_id
						ORDER BY et.rank ASC
						LIMIT 1";
			$result = mysql_query ($sql) or exit_sql_error ($sql);
			$_email = mysql_fetch_assoc ($result);

			if ($_email)
			{
				$email = $_email["email"];
			}

			$sql = "SELECT r.`year` FROM `roster_memberships` AS m
						JOIN `rosters` AS r ON r.`roster_id` = m.`roster_id`
						JOIN `groups` AS g ON g.`group_id` = r.`group_id`
						WHERE g.`excluded` <> '1' AND r.`year` > '0' AND m.`contact_id` = '{$c["contact_id"]}'
						GROUP BY r.`year`
						ORDER BY r.`year` ASC";
			$years = mysql_query ($sql) or exit_sql_error ($sql);

			if ($step != 3)
			{
				$prev_year = -1;
				$curr_year = NULL;
				$note = "";
				while ($y = mysql_fetch_assoc ($years))
				{
					$curr_year = $y["year"][2] . $y["year"][3];
					if ($curr_year == ($prev_year + 1))
					{
						if ((strlen ($note) > 2) && ($note[strlen ($note) - 1] != "-"))
						{
							$note .= "-";
						}
					}
					else
					{
						if (($prev_year) && (strlen ($note) > 2) && ($note[strlen ($note) - 1] == "-"))
						{
							$note .= "'$prev_year";
						}
						$note = dbAddToList ($note, "'$curr_year");
					}
					$prev_year = $curr_year;
				}
				if ((strlen ($note) > 2) && ($note[strlen ($note) - 1] == "-"))
				{
					$note .= "'$curr_year";
				}
			}

			csv ($job_or_customer_name);
			csv ($salutation);
			csv ($first_name);
			csv ($middle_initial);
			csv ($last_name);
			csv ($company_name);
			csv ($phone);
			csv ($email);
			csv ($contact);
			for ($i = 1; $i <= 5; $i++)
			{
				csv ($billing_address[$i]);
			}
			csv ($note);
			csv ($customer_type);
			csv ($account_number);
			csv_newline ();
		}
	}
}

csv_end ();

?>