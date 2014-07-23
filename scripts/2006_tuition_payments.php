<?php

require_once ("../library.inc.php");
require_once ("../csv.inc.php");

set_time_limit (6000);

csv_start ("2006 Tuition Payments");

csv ("Trekker");
csv ("Group");
csv ("Date");
csv ("Type");
csv ("Amount");
csv ("Check");

csv_newline ();

$sql = "SELECT c.`contact_id`, r.`year`, g.`short_name`, p.`date`, pt.`payment_type`, p.`amount`, p.`check_number` FROM `contacts` AS c
		JOIN `roster_memberships` AS m ON m.`contact_id` = c.`contact_id`
		JOIN `rosters` AS r ON r.`roster_id` = m.`roster_id`
		JOIN `groups` AS g ON g.`group_id` = r.`group_id`
		JOIN `tuitions` AS t ON t.`roster_id` = r.`roster_id`
		JOIN `payments` AS p ON p.`tuition_id` = t.`tuition_id` AND p.`contact_id` = c.`contact_id`
		JOIN `payment_types` AS pt ON pt.`payment_type_id` = p.`payment_type_id`
		WHERE r.`year` = '2006'
		ORDER BY c.`primary_name` ASC, c.`first_name` ASC, p.`date` ASC";

$payments = mysql_query ($sql) or exit_sql_error ($sql);

while ($p = mysql_fetch_assoc ($payments))
{
	csv (Name ($p["contact_id"],"%L, %n"));
	csv ("{$p["year"]} {$p["short_name"]}");
	csv ($p["date"]);
	csv ($p["payment_type"]);
	csv ($p["amount"]);
	csv ($p["check_number"]);
	csv_newline ();
}

csv_end ();

?>