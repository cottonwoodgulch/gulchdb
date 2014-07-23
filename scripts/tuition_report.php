<?php

require_once ("../library.inc.php");

$month = (isset ($_GET["month"]) ? $_GET["month"] : NULL);
$yr = substr ($month, 0, 4);
$mo = substr ($month, 5, 2);
$mo++;
if ($mo > 12)
{
	$yr++;
	$mo = 1;
}
$end = "$yr-$mo-01";

$sql = "SELECT p.`date` AS `date`, p.amount AS payment, g.short_name AS `group`, t.amount AS tuition, c.primary_name, c.first_name, pt.payment_type FROM payments AS p
			JOIN contacts AS c ON c.contact_id = p.contact_id
			JOIN tuitions AS t ON t.tuition_id = p.tuition_id
			JOIN payment_types AS pt ON pt.payment_type_id = p.payment_type_id
			JOIN rosters AS r ON t.roster_id = r.roster_id
			JOIN groups AS g ON g.group_id = r.group_id " .
		($month !== NULL ? "WHERE p.`date` >= '$month-01' and p.`date` < '$end'" : "")	
		. " ORDER BY p.date ASC, g.short_name ASC, c.primary_name ASC, c.first_name ASC";

$payments = mysql_query ($sql) or exit (mysql_error());

$sql = "SELECT * FROM payment_types ORDER BY payment_type ASC";

$types = mysql_query ($sql) or exit (mysql_error()); 

header('Content-Type: text/x-csv');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
// lem9 & loic1: IE need specific headers
//if (PMA_USR_BROWSER_AGENT == 'IE') {
	header('Content-Disposition: inline; filename="'. ($month !== NULL ? $month . "_" : '') . 'tuition_report.csv"');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');
/*} else {
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Pragma: no-cache');
}*/

$payment_types = array();
$i = 0;
echo '"Date","Group","Tuition","Name"';
while ($type = mysql_fetch_assoc ($types))
{
	if (($type["payment_type_id"] <> 7) && ($type["payment_type_id"] <> 8) && ($type["payment_type_id"] <> 10))
	{
		echo ',"' . ($type["code"] <> "" ? $type["code"] . " " : "") . $type["payment_type"] . '"';
		$payment_types[$i]["total"] = 0;
		$payment_types[$i++]["name"] = $type["payment_type"];
	}
}
echo "\r";

while ($p = mysql_fetch_assoc ($payments))
{
	echo '"' . $p["date"] . '"';
	echo ',"' . $p["group"] . '"';
	echo ',"' . $p["tuition"] . '"';
	echo ',"' . $p["primary_name"] . ', ' . $p["first_name"] . '"';
	for ($i = 0; $i < sizeof ($payment_types); $i++)
	{
		if ($payment_types[$i]["name"] == $p["payment_type"])
		{
			echo ',"' . $p["payment"] . '"';
			$payment_types[$i]["total"] += $p["payment"];
		}
		else
		{
			echo ',';
		}
	}
	echo "\r";
}

echo ',,,';
for ($i = 0; $i < sizeof ($payment_types); $i++)
{
	echo ',"'. $payment_types[$i]["total"] . '"';
}
echo "\r";
?>