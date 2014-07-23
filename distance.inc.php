<?php// the code in this file is converted from ASP routines written by Mike Shaffer,// originally found at guysfromrolla.com/webtech/code/2points.asp.html// obligatory copyright notice follows -- thanks for doing the hard work Mike!//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::://:::                                                                         ::://:::  These functions may come in handy for processes that need to  find     ::://:::  distances between two points on the planet. It is derived from         ::://:::  spherical trigonometry. Although the earth is actually an oblate       ::://:::  spheroid, these methods are generally accurate for non-navigational    ::://:::  purposes (e.g. e-commerce calculations for store-finders, etc.)        ::://:::  Do NOT use them to plot your next trip around the world. :)            ::://:::                                                                         ::://:::                                                                         ::://:::  blah blah blah blah blah blah blah blah blah blah blah blah blah blah  ::://:::  blah blah blah blah blah blah blah blah blah blah blah blah blah blah  ::://:::  blah blah      Copyright *c* 2000, Mike Shaffer.       blah blah blah  ::://:::  blah blah        ALL RIGHTS RESERVED WORLDWIDE         blah blah blah  ::://:::  blah blah   Specific permission to use this routine    blah blah blah  ::://:::  blah blah   in any way is granted by Mike Shaffer      blah blah blah  ::://:::  blah blah   provided that these comments are included  blah blah blah  ::://:::  blah blah   and that copyright ownership is retained.  blah blah blah  ::://:::  blah blah blah blah blah blah blah blah blah blah blah blah blah blah  ::://:::  blah blah blah blah blah blah blah blah blah blah blah blah blah blah  ::://:::                                                                         ::://:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::require_once ("library.inc.php");// this could take a little while -- let's up the PHP time limitset_time_limit (6000);// toggle these for more or less output -- both are false by default$debugging = false;$output = false;// calculate the distance between two points in statute milesfunction Distance ($lat1, $long1, $lat2, $long2){	// this math is cribbed from 4 Guys From Rolla and is not verified	// http://www.4guysfromrolla.com/webtech/code/2points.asp.html	$x = (sin (deg2rad ($lat1)) * sin (deg2rad ($lat2)) + cos (deg2rad ($lat1)) * cos (deg2rad ($lat2)) * cos (abs ((deg2rad( $long2) - deg2rad ($long1)))));	$x = atan ((sqrt (1 - pow ($x, 2))) / $x);	return (1.852 * 60.0 * (($x / pi ()) * 180.0)) / 1.609344;	// statute miles: 1.609344	// nautical miles: 1.0	// kilometers: 1.852}// convert decimal degrees to degrees, minutes, secondsfunction DecimalToDMS ($dec){	$dms = array ();	$dms["degrees"] = intval ($dec);	$temp = ($dms["degrees"] - $dec) * 60;	$dms["minutes"] = intval ($temp);	$temp = ($dms["minutes"] - $temp) * 60;	$dms["seconds"] = intval ($temp);	return $dms;}// convert degrees, minutes, seconds to decimal degreesfunction DMSToDecimal ($dms){	return $dms["degrees"] + ($dms["minutes"] / 60.0) + ($dms["seconds"] / 3600.0);}// convert statute (normal) miles to nautical miles (1 mile/minute)function StatuteToNautical ($stat){	return $stat * 1.15;}// compute the bounding rectangle, given a center and a distancefunction DistanceRectangle ($lat, $long, $distance){	global $debugging;	$distance = StatuteToNautical ($distance) / 60.0;	$rect = array ();	$rect["east"] = $long + $distance;	$rect["north"] = $lat + $distance;	$rect["west"] = $long - $distance;	$rect["south"] = $lat - $distance;	if ($debugging) echo "<table><tr><td></td><td>{$rect["north"]}.$long</td><td></td></tr>\n<tr><td>$lat,{$rect["west"]}</td><td>$lat,$long</td><td>$lat,{$rect["east"]}</td></tr>\n<tr><td></td><td>{$rect["south"]},$long</td><td></td></tr></table>";	return $rect;}// generate list of zip codes within distance miles of the center of zipfunction DistanceZipCodes ($zip, $distance){	global $debugging;	$sql = "SELECT * FROM `zip_codes`			WHERE `zip` = '$zip' LIMIT 1";	$result = mysql_query ($sql) or exit ("<code>$sql;</code><br>" . mysql_error());	$center = mysql_fetch_assoc ($result);	mysql_free_result ($result);	if ($debugging) echo "<p>Centered at: {$center["zip"]}, {$center["city"]}, {$center["state"]} ({$center["latitude"]},{$center["longitude"]})</p>";	$rect = DistanceRectangle ($center["latitude"], $center["longitude"], $distance);	$sql = "SELECT * FROM `zip_codes`			WHERE `longitude` >= {$rect["west"]} AND				  `longitude` <= {$rect["east"]} AND				  `latitude` >= {$rect["south"]} AND				  `latitude` <= {$rect["north"]}			GROUP BY `zip`			ORDER BY `zip` ASC, `state` ASC, `city` ASC";	$result = mysql_query ($sql) or exit ("<code>$sql;</code><br>" . mysql_error());	if ($debugging) echo "<p>" . mysql_num_rows ($result) . " possible matches.</p>";	$zips = array();	$i = 0;	while ($row = mysql_fetch_assoc ($result))	{		if (Distance ($center["latitude"], $center["longitude"], $row["latitude"], $row["longitude"]) <= $distance)		{			$zips[$i++] = $row;			if ($debugging) echo "<p>" . Distance ($center["latitude"], $center["longitude"], $row["latitude"], $row["longitude"]) . " from {$center["zip"]} to {$row["zip"]}, it will be included.</p>";		}		else if ($debugging)		{			echo "<p>" . Distance ($center["latitude"], $center["longitude"], $row["latitude"], $row["longitude"]) . " from {$center["zip"]} to {$row["zip"]}, it will not be included.</p>";		}	}	mysql_free_result ($result);	if ($debugging) echo "<p>" . sizeof ($zips) . " actual matches.</p>";	return $zips;}// some debugging info, so you can get a sense of how to use this allif ($debugging || $output){	echo "<html><head><title>Debugging distance.inc.php</title><link rel=\"stylesheet\" href=\"stylesheet.css\"></head><body>";	$sql = "SELECT * FROM `zip_codes` WHERE `zip` = '19144' LIMIT 1";	$result = mysql_query ($sql) or exit ("<code>$sql;</code><br>" . mysql_error());	$center = mysql_fetch_assoc ($result);	mysql_free_result ($result);	$zips = DistanceZipCodes ($center["zip"], 10);	for ($i = 0; $i < sizeof ($zips); $i++)	{		$sql = "SELECT * FROM contacts AS c JOIN address_associations AS aa ON aa.contact_id = c.contact_id JOIN addresses AS a ON a.address_id = aa.address_id JOIN zip_codes AS z ON z.zip = a.postal_code WHERE a.postal_code = '{$zips[$i]["zip"]}' ORDER BY a.address_id, c.primary_name ASC, c.first_name ASC";		$result = mysql_query ($sql) or exit ("<code>$sql;</code><br>" . mysql_error());		if (mysql_num_rows ($result) > 0) echo "\n<h3>{$zips[$i]["zip"]}</h3><dl>";		$prev_address = NULL;		$prev_row = NULL;		$names = "";		$distance = 0;		while ($a = mysql_fetch_assoc ($result))		{			if ($prev_address != $a["address_id"])			{				echo "\n<dt>" . $names . " (" . round ($distance, 1) . " miles)";				echo "<dd>";				DisplayAddress ($prev_row);				$names = Name ($a["contact_id"], "%n %L");				$prev_row = $a;			}			else			{				$prev_address = $a["address_id"];				$distance = Distance ($center["latitude"], $center["longitude"], $a["latitude"], $a["longitude"]);				$names = dbAddToList ($names, Name ($a["contact_id"], "%n %L"), ", ");			}		}		if (mysql_num_rows ($result) > 0) echo "\n</dl>";	}	echo "\n</body></html>";}?>