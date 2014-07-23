<?php

require_once ("../library.inc.php");

$cid = (isset ($_GET["cid"]) ? $_GET["cid"] : exit ("bad things happened"));

$family = array();
$family[0]["id"] = $cid;
$family[0]["level"] = 0;

function Children ($id, $level)
{
	$sql = "SELECT c.`contact_id` FROM `relationships` AS r  JOIN `contacts` AS c ON r.`relative_id` = c.`contact_id` JOIN `relationship_types` AS t ON t.`relationship_type_id` = r.`relationship_type_id` WHERE t.`relationship_type` = 'Child' AND r.`contact_id` = '$id' ORDER BY c.`birth_date` DESC";
	$children = mysql_query ($sql);
	while ($c = mysql_fetch_assoc ($children));
	{
		$i = sizeof ($GLOBALS["family"]);
		$GLOBALS["family"][$i]["id"] = $c["contact_id"];
		$GLOBALS["family"][$i]["level"] = $level + 1;
		Children ($c["contact_id"], $level + 1);
	}
	mysql_free_result ($children);
}

Children ($cid, 0);

?>

<html>

<head>
	<title>Family Tree for <?php echo Name ($cid, "%n %L"); ?></title>
	<link rel="stylesheet" href="../stylesheet.css">
	<script type="text/javascript" href="../library.inc.js"></script>
</head>

<body>

<table>
<?php

$done = false;
for ($i = 0; $done; $i++)
{
	echo "<tr>";
	$done = true;
	for ($j = 0; $j < sizeof ($GLOBALS["family"]); $j++)
	{
		if ($GLOBALS["family"][$j]["level"] == $i)
		{
			$done = false;
			echo "<td>" . Name ($GLOBALS["family"][$j]["id"], "%n %L") . "</td>";
		}
	}
	echo "</tr>\n";
}

?>
</table>

</body>

</html>