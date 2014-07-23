<?php

require_once "../library.inc.php";

$form = (isset ($_POST["form"]) ? $_POST["form"] : NULL);
$name = (isset ($_POST["name"]) ? $_POST["name"] : NULL);
$year = (isset ($_POST["year"]) ? $_POST["year"] : NULL);
$roster = (isset ($_POST["roster"]) ? $_POST["roster"] : NULL);
$flag = (isset ($_GET["flag"]) ? $_GET["flag"] : NULL);

$name_str = $name;
$name_array = NULL;
$i = 0;

while (($j = strpos ($name_str, " ")) !== false)
{
	$name_array[$i++] = substr ($name_str, 0, $j);
	$name_str = substr ($name_str, $j + 1);
}
$name_array[$i] = $name_str;

$sql = "SELECT * FROM `rosters` WHERE 1 GROUP BY `year` ORDER BY `year` DESC";
$years = mysql_query ($sql) or header ("Location index.php?flag=MySQL+error+(I1)");

if (($name !== NULL) && strlen ($name) > 0)
{
	$sql = "SELECT * FROM `contacts` AS c JOIN `roster_memberships` AS m ON m.`contact_id` = c.`contact_id` JOIN `rosters` AS r ON r.`roster_id` = m.`roster_id` JOIN `groups` AS g ON g.`group_id` = r.`group_id` WHERE 1";
	for ($i = 0; $i < sizeof ($name_array); $i++)
	{
		$n = $name_array[$i];
		$sql .= " AND (`first_name` LIKE '$n%' OR `middle_name` LIKE '$n%' OR `primary_name` LIKE '$n%' OR `nickname` LIKE '$n%')";
	}
	$sql .= " ORDER BY `primary_name` ASC, `first_name` ASC, `year` ASC";
	$names = mysql_query ($sql) or header ("Location index.php?flag=MySQL+error+(I2)");
}

if ($year !== NULL)
{
	$sql = "SELECT * FROM `groups` AS g JOIN `rosters` AS r ON g.`group_id` = r.`group_id` WHERE r.`year` = '$year' ORDER BY g.`excluded` ASC, g.`group` ASC";
	$rosters = mysql_query ($sql) or header ("Location index.php?flag=MySQL+error+(I3)");
}

if (($form == "rosters") && ($roster !== NULL))
{
	$sql = "SELECT * FROM `rosters` WHERE `roster_id` = '$roster' AND `year` = '$year'";
	$ready = mysql_query ($sql);
	if (mysql_num_rows ($ready) == 1)
	{
		header ("Location: roster.php?id=$roster");
	}
	else
	{
		$roster = NULL;
	}
}

DocType();
?>

<html>

<head>
	<link rel="stylesheet" href="../stylesheet.css">
	<script src="/javascript/lib.js"></script>
</head>

<body>

<table class="menu" width="98%">
	<tr>
		<td width="100%" align="right">Groups</td>
	</tr>
</table>

<?php

if ($flag !== NULL)
{
	echo "<p class=\"error\">A $flag has occurred and you were redirected to this page. If this error persists, please contact <a href=\"mailto:webmaster@cottonwoodgulch.org\">the webmaster.</a></p>\n";
}

?>

<form name="rosters" method="post" action="index.php">
<input name="form" type="hidden" value="rosters">
<?php

if ($name !== NULL)
{
	echo "<input name=\"name\" type=\"hidden\" value=\"$name\">\n";
}

?>
<dl>
	<dt>
		<th>Find a group</th>
	<dd><table>
	<tr>
		<td class="label">Choose a year</td>
<?php

if ((isset ($rosters)) && (mysql_num_rows ($rosters) > 0))
{
	echo "\t\t<td class=\"label\">Choose a group</td>\n";
}

?>
	<tr>
		<td><select name="year" onchange="document.rosters.submit();">
<?php

while ($y = mysql_fetch_assoc ($years))
{
	$y = $y["year"];
	echo "\t\t\t<option value=\"$y\"" . ($year === $y ? " selected" : "") . ">$y</option>\n";
}
?>
		</select></td>
<?php

if ((isset ($rosters)) && (mysql_num_rows ($rosters) > 0))
{
	echo "\t\t<td><select name=\"roster\">\n";
	while ($r = mysql_fetch_assoc ($rosters))
	{
		echo "\t\t\t<option value=\"" . $r["roster_id"] . "\"" . ($roster === $r["roster_id"] ? " selected" : "") . ">" . $r["group"] . "</option>\n";
	}
}

?>
		<td><input type="submit" value="Go"></td>
	</tr></table>
</dl>
</form>

</body>