<?php

require_once "library.inc.php";

$roster_id = NULL;
$log = 0;

while (list ($key, $value) = each ($_POST))
{
	switch ($key)
	{
		default:
			$$key = ($value ? $value : 0);
	}
}

if ($roster_id)
{
	$sql = "UPDATE `rosters` SET `log` = '$log' WHERE `roster_id` = '$roster_id'";
	$result = mysql_query ($sql) or exit ($mysql_error());
}

$sql = "SELECT * FROM `rosters` AS r JOIN `groups` AS g ON g.`group_id` = r.`group_id` WHERE r.`year` > '0' AND g.`excluded` = '0' ORDER BY r.`year` ASC, g.`group` ASC";
$groups = mysql_query ($sql) or exit (mysql_error());

?>

<html>

<body>

<?php

while ($g = mysql_fetch_assoc ($groups))
{
	echo "<p><a name=\"" . $g["roster_id"] . "\"></a><form name=\"" . $g["roster_id"] . "\" method=\"post\" action=\"logs.php#" . $g["roster_id"] . "\">\n";
	echo "<input name=\"roster_id\" type=\"hidden\" value=\"" . $g["roster_id"] . "\">\n";
	echo "<input name=\"log\" type=\"hidden\" value=\"" . ($g["log"] ? "0" : "1") . "\">\n";
	echo "<input name=\"button\" type=\"submit\" value=\"" . ($g["log"] ? "YES" : "NO") . " [toggle]\">\n";
	echo $g["year"] . " " . $g["group"];
	echo "</form></p>";
}

?>

</body>

</html>