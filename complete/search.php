<?php	require_once('../library.inc.php');	require_once ("../distance.inc.php");	$name_pattern = (isset ($_POST['name_pattern']) ? $_POST['name_pattern'] : NULL);	$location_pattern = (isset ($_POST['location_pattern']) ? $_POST['location_pattern'] : NULL);	$zip = (isset ($_POST["zip"]) ? $_POST["zip"] : NULL);	$distance = (isset ($_POST["distance"]) ? $_POST["distance"] : NULL);	$sql = (isset ($_POST['sql']) ? SQLFormat (stripslashes ($_POST['sql'])) : NULL);	mysql_select_db($GLOBALS['db']['db'], $GLOBALS['db']['link']);	$name_exist = (strlen($name_pattern) > 0);	$location_exist = (strlen($location_pattern) > 0);	$zip_exist = (strlen ($zip) > 0) || (strlen ($distance) > 0);	$sql_exist = (strlen($sql) > 0);	if ($name_exist)	{		$name_contacts = NameSearch ($name_pattern);		$row_name_contacts = mysql_fetch_assoc($name_contacts);		$totalRows_name_contacts = mysql_num_rows($name_contacts);	}		if($location_exist)	{		$query_location_contacts =			"SELECT * FROM contacts AS c				JOIN address_associations AS aa ON c.contact_id = aa.contact_id				JOIN addresses AS a ON aa.address_id = a.address_id				WHERE					address_type_id <> '7' AND					(						city LIKE '%$location_pattern%' OR						state LIKE '%$location_pattern%' OR						(							postal_code LIKE '$location_pattern%' AND							country = 'United States'						) OR						street_address_1 LIKE '%$location_pattern%'					)				ORDER BY					state ASC,					city ASC,					primary_name ASC,					first_name ASC";		$location_contacts = mysql_query($query_location_contacts, $GLOBALS['db']['link']) or exit ("<code>$sql</code><br>" . mysql_error());		$row_location_contacts = mysql_fetch_assoc($location_contacts);		$totalRows_location_contacts = mysql_num_rows($location_contacts);	}		if ($zip_exist && $rbac->check('search_zip_code', $_SESSION['user']))	{		if ((strlen ($zip) == 5) && (strlen ($distance) > 0))		{			$sql =				"SELECT * FROM zip_codes					WHERE zip = '$zip'					LIMIT 1";			$result = mysql_query ($sql) or exit ("<code>$sql</code><br>" . mysql_error());			$center = mysql_fetch_assoc ($result);			mysql_free_result ($result);				$zips = DistanceZipCodes ($zip, $distance);			$sql = "SELECT * FROM `contacts` AS c				JOIN `address_associations` AS aa ON c.contact_id = aa.contact_id				JOIN addresses AS a ON a.address_id = aa.address_id				JOIN zip_codes AS z ON z.zip = a.postal_code				WHERE					address_type_id <> '7' AND					(						postal_code = '{$zips[0]["zip"]}'";			for ($i = 1; $i < sizeof ($zips); $i++)			{				$sql = dbAddToList ($sql, "postal_code = '{$zips[$i]["zip"]}'", " OR ");			}			$sql .= ")				ORDER BY					primary_name ASC,					first_name ASC";			$zip_result = mysql_query ($sql) or exit ("<code>$sql</code><br>" . mysql_error());			$row_zip = mysql_fetch_assoc ($zip_result);			$totalRows_zip = mysql_num_rows ($zip_result);		}		else		{			$zip_error = "You need to enter a valid 5-digit zip code and a distance.";			$totalRows_zip = 0;		}	}if ($sql_exist) {		$sql_result = mysql_query ($sql, $GLOBALS['db']['link']) or ($sql_error = mysql_error());		if (! isset($sql_error)) {			$row_sql = mysql_fetch_assoc ($sql_result);			$totalRows_sql = mysql_num_rows ($sql_result);		}		else		{			$row_sql = NULL;			$totalRows_sql = 0;		}	}?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>Find Individuals</title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" href="../stylesheet.css"><base target="content"></head><body><script src="../library.inc.js"></script><?php/* no need any more -- 2010-10-25 SDBif ($GLOBALS["db"]["host"] != "localhost"){	echo "<p class=\"warning\">You are using a remote copy of the database at {$GLOBALS["db"]["host"]}.</p>\n";}*/?><p class="warning">Database server: <?= $GLOBALS["db"]["host"] ?></p><form name="find_contact_by_name" action="search.php" method="post" target="search"><table><tr>	<th>find by name</th></tr><tr>	<td><input type="text" name="name_pattern" value="<?php if ($name_exist) echo $name_pattern; ?>"><input type="submit" name="button" value="Find"></td></tr></table></form><?php if ($name_exist) {if ($totalRows_name_contacts == 1){	echo "<script><!--\nparent.parent.content.location.replace('contacts.php?cid=" . $row_name_contacts["contact_id"] . "&detail=summary.php');\n//--></script>\n";}if ($totalRows_name_contacts > 0) {echo "<form method=\"post\" action=\"../scripts/csv_from_list.php\">\n";echo "<input type=\"hidden\" name=\"filename\" value=\"{$name_pattern}\">\n";echo "<ol>\n";	do {		echo '<li><a href="contacts.php?cid=' . $row_name_contacts['contact_id'] . '&detail=summary.php">' . Name ($row_name_contacts["contact_id"], "%n %L %D") . '</a>';		echo "<input type=\"hidden\" name=\"item{$row_name_contacts["contact_id"]}\" value=\"{$row_name_contacts["contact_id"]}\">\n";	} while ($row_name_contacts = mysql_fetch_assoc ($name_contacts));	echo '</ol>';	echo "<input type=\"submit\" name=\"button\" value=\"Export this list as CSV\">\n";	echo "</form>";}else{echo '<p>No matches found (try searching by initial).</p>';}}?><form name="find_contact_by_location" action="search.php" method="post" target="search"><table><tr><th>find by street, city, state or zip</th></tr><tr><td><input type="text" name="location_pattern" value="<?php if ($location_exist) echo $location_pattern; ?>"><input type="submit" name="button" value="Find"></td></tr></table></form><?php if ($location_exist) {if ($totalRows_location_contacts == 1){	echo "<script><!--\nparent.parent.content.location.replace('contacts.php?cid=" . $row_location_contacts["contact_id"] . "&detail=summary.php');\n//--></script>\n";}if ($totalRows_location_contacts > 0) {echo "<form method=\"post\" action=\"../scripts/csv_from_list.php\">\n";echo "<input type=\"hidden\" name=\"filename\" value=\"{$location_pattern}\">\n";echo "<ol>\n";	do {		echo '<li><a href="contacts.php?cid=' . $row_location_contacts['contact_id'] . '&detail=summary.php">' . Name ($row_location_contacts["contact_id"], "%n %L %D") . ' (' . $row_location_contacts['city'] . ', ' . $row_location_contacts['state'] . ' ' . $row_location_contacts['postal_code'] . ')</a>';		echo "<input type=\"hidden\" name=\"item{$row_location_contacts["contact_id"]}\" value=\"{$row_location_contacts["contact_id"]}\">\n";	} while ($row_location_contacts = mysql_fetch_assoc ($location_contacts));	echo '</ol>';	echo "<input type=\"submit\" name=\"button\" value=\"Export this list as CSV\">\n";	echo "</form>";}else{echo '<p>No matches found.</p>';}}?><?php if ($rbac->check('search_zip_code', $_SESSION['user'])): ?><form name="find_zip" action="search.php" method="post" target="search"><table><tr><th>near a zip code</th></tr><tr><td><input type="text" name="zip" maxlength="5" value="<?php if ($zip) echo $zip; ?>"> <input type="submit" name="button" value="Find"><br>Distance in miles <input type="text" name="distance" size="10" value="<?php echo ($distance ? $distance : 25); ?>"></td></tr></table></form><?phpif ($zip_exist){	if ($totalRows_zip == 1)	{		echo "<script><!--\nparent.parent.content.location.replace('contacts.php?cid=" . $row_zip["contact_id"] . "&detail=summary.php');\n//--></script>\n";	}	if ($totalRows_zip > 0)	{		echo "<form method=\"post\" action=\"../scripts/csv_from_list.php\">\n";		echo "<input type=\"hidden\" name=\"filename\" value=\"{$distance}_miles_from_{$zip}\">\n";		echo "<ol>\n";		do {			echo "<li><a href=\"contacts.php?cid={$row_zip["contact_id"]}&detail=summary.php\">" . Name ($row_zip["contact_id"], "%L, %n") . " ({$row_zip["city"]}, {$row_zip["state"]} " . round (Distance ($center["latitude"], $center["longitude"], $row_zip["latitude"], $row_zip["longitude"]), 0) . " mi.)</a>\n";			echo "<input type=\"hidden\" name=\"item{$row_zip["contact_id"]}\" value=\"{$row_zip["contact_id"]}\">\n";		} while ($row_zip = mysql_fetch_assoc ($zip_result));		echo "</ol>\n";		echo "<input type=\"submit\" name=\"button\" value=\"Export this list as CSV\">\n";		echo "</form>";	}	else if (isset ($zip_error))	{		echo "<p>$zip_error</p>";	}	else	{		echo "<p>No matches found.</p>";	}}?><?php endif; ?><form name="find_sql" action="search.php" method="post" target="search"><table><tr><th>i'm a sql bad ass</th></tr><tr><td><textarea name="sql" cols="20" rows="5" wrap="virtual"><?php if ($sql_exist) echo $sql; ?></textarea><br><input type="submit" name="button" value="Find"></td></tr></table></form><?php if ($sql_exist) {if ($totalRows_sql == 1){	echo "<script><!--\nparent.parent.content.location.replace('contacts.php?cid=" . $row_sql["contact_id"] . "&detail=summary.php');\n//</script>\n";}if ($totalRows_sql > 0) {echo "<form method=\"post\" action=\"../scripts/csv_from_list.php\">\n";echo "<input type=\"hidden\" name=\"filename\" value=\"sql\">\n";$one_line_sql = str_replace (array ("\n", "\t"), array (" ", ""), $sql);echo "<input type=\"hidden\" name=\"comment\" value=\"$one_line_sql\">\n";echo "<ol>\n";	do {		echo '<li><a href="contacts.php?cid=' . $row_sql['contact_id'] . '&detail=summary.php">' . (strlen($row_sql['nickname']) > 0 ? Name($row_sql['contact_id'], '%L, %N %M') : Name ($row_sql['contact_id'], '%L, %F %M')) . (isset($row_sql['info']) ? ' (' . $row_sql['info'] . ')' : '') . '</a>';		echo "<input type=\"hidden\" name=\"item{$row_sql["contact_id"]}\" value=\"{$row_sql["contact_id"]}\">\n";	} while ($row_sql = mysql_fetch_assoc ($sql_result));	echo '</ol>';	echo "<input type=\"submit\" name=\"button\" value=\"Export this list as CSV\">\n";	echo "</form>";}else{if (isset ($sql_error)) echo "<p>$sql_error</p>";else echo '<p>No matches found.</p>';} ?><pre><?php echo $sql; ?></pre><?php }?><br><form name="create_contact" action="contacts.php?rec=create" method="post" target="content"><input type="submit" name="button" value="Add a New Contact"></form></body></html><?phpif ($name_exist) mysql_free_result($name_contacts);if ($location_exist) mysql_free_result($location_contacts);if ($sql_exist) mysql_free_result ($sql_result);?>