<?php

require_once ("../library.inc.php");
require_once ("../csv.inc.php");

set_time_limit (6000);

$filename = (isset ($_POST["filename"]) ? $_POST["filename"] : "list");
csv_start ($filename);

$comment = (isset ($_POST["comment"]) ? $_POST["comment"] : NULL);
if ($comment)
{
	csv ($comment);
	csv_newline ();
}

csv ("First");
csv ("Nickname");
csv ("Last");
csv ("Address1");
csv ("Address2");
csv ("City");
csv ("State");
csv ("Zip");
csv ("Country");
csv ("Phone");
csv ("Email");
csv_newline ();

while (list ($key, $value) = each ($_POST))
{
	switch ($key)
	{
		case "button": break;
		case "filename": break;
		case "comment": break;
		default:
			if (strpos ($key,"item") === 0)
			{
				$sql = "SELECT * FROM `contacts` WHERE `contact_id` = '$value' LIMIT 1";
				$result = mysql_query ($sql) or exit ("<code>$sql<code><br>" . mysql_error ());
				$contact = mysql_fetch_assoc ($result) or exit ("<p>ERROR: Could not find contact $value</p>");
				mysql_free_result ($result);
				csv ($contact["first_name"]);
				csv ($contact["nickname"]);
				csv ($contact["primary_name"]);

				$sql = "SELECT * FROM `contacts` AS c
						JOIN `address_associations` AS aa ON aa.`contact_id` = c.`contact_id`
						JOIN `addresses` AS a ON a.`address_id` = aa.`address_id`
						JOIN `address_types` AS at ON at.`address_type_id` = a.`address_type_id`
						WHERE c.`contact_id` = '$value'
						GROUP BY c.`contact_id`
						ORDER BY at.`rank` ASC
						LIMIT 1";
				$result = mysql_query ($sql) or exit ("<code>$sql</code><br>" . mysql_error ());
				if (mysql_num_rows ($result))
				{
					$address = mysql_fetch_assoc ($result);
					csv ($address["street_address_1"]);
					csv ($address["street_address_2"]);
					csv ($address["city"]);
					csv ($address["state"]);
					csv ($address["postal_code"]);
					if ($address["country"] != "United States")
					{
						csv ($address["country"]);
					}
					else
					{
						csv ("");
					}
				}
				else
				{
					csv ("");
					csv ("");
					csv ("");
					csv ("");
					csv ("");
					csv ("");
				}
				mysql_free_result ($result);

				$sql = "SELECT * FROM `contacts` AS c
						JOIN `phone_associations` AS pa ON pa.`contact_id` = c.`contact_id`
						JOIN `phones` AS p ON p.`phone_id` = pa.`phone_id`
						JOIN `phone_types` AS pt ON pt.`phone_type_id` = p.`phone_type_id`
						WHERE c.`contact_id` = '$value'
						GROUP BY c.`contact_id`
						ORDER BY pt.`rank` ASC
						LIMIT 1";
				$result = mysql_query ($sql) or exit ("<code>$sql</code><br>" . mysql_error ());
				if (mysql_num_rows ($result))
				{
					$phone = mysql_fetch_assoc ($result);
					csv ($phone["number"]);
				}
				else
				{
					csv ("");
				}
				mysql_free_result ($result);

				$sql = "SELECT * FROM `contacts` AS c
						JOIN `email_associations` AS ea ON ea.`contact_id` = c.`contact_id`
						JOIN `emails` AS e ON e.`email_id` = ea.`email_id`
						JOIN `email_types` AS et ON et.`email_type_id` = e.`email_type_id`
						WHERE c.`contact_id` = '$value'
						GROUP BY c.`contact_id`
						ORDER BY et.`rank` ASC
						LIMIT 1";
				$result = mysql_query ($sql) or exit ("<code>$sql</code><br>" . mysql_error ());
				if (mysql_num_rows ($result))
				{
					$email = mysql_fetch_assoc ($result);
					csv ($email["email"]);
				}
				else
				{
					csv ("");
				}
				mysql_free_result ($result);
			}
		csv_newline ();
	}
}

csv_end ();

?>