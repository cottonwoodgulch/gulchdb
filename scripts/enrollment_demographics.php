<?php

require_once ("../library.inc.php");
require_once ("../csv.inc.php");

$now = getdate();
$year = (isset ($_GET["year"]) ? $_GET["year"] : $now["year"]);
csv_start ($year . "_enrollment_demographics");

// select all trekkers from roster year
$sql = "SELECT * FROM contacts AS c
		JOIN roster_memberships AS m ON m.contact_id = c.contact_id
		JOIN rosters AS r ON r.roster_id = m.roster_id AND r.year = '$year'
		JOIN roles AS l ON l.role_id = m.role_id AND l.is_staff = '0'
		JOIN groups AS g ON g.group_id = r.group_id AND g.excluded = '0'
		JOIN address_associations AS aa ON aa.contact_id = c.contact_id
		JOIN addresses AS a ON a.address_id = aa.address_id
		ORDER BY c.primary_name ASC, c.first_name ASC";
$trekkers = mysql_query ($sql) or exit (mysql_error());

// Returning vs. New
// -- source of new Trekkers (needs to be pulled from inquiries)
csv ("Please refer to the Inquiries spreadsheet to determine the sources of new Trekkers");
csv_newline ();

// -- returning are what % of prev. yr's enrollment
$returning_trekkers = 0;
$new_trekkers = 0;
$previous_year_enrollment = 0;

while ($trekker = mysql_fetch_assoc ($trekkers))
{
	$sql = "SELECT * FROM roster_memberships AS m JOIN rosters AS r ON r.roster_id = m.roster_id WHERE m.contact_id = {$trekker["contact_id"]} AND r.year = '" . ($year - 1) . "'";
	$previous_year = mysql_query ($sql) or exit (mysql_error());
	if (mysql_num_rows ($previous_year) > 0)
	{
		$returning_trekkers++;
	}
	else
	{
		$new_trekkers++;
	}
	mysql_free_result ($previous_year);
}
mysql_data_seek ($trekkers, 0);

$sql = "SELECT COUNT(*) AS enrolled FROM contacts AS c
		JOIN roster_memberships AS m ON m.contact_id = c.contact_id
		JOIN rosters AS r ON r.roster_id = m.roster_id AND r.year = '" . ($year - 1) . "'
		JOIN roles AS l ON l.role_id = m.role_id AND l.is_staff = '0'
		JOIN groups AS g ON g.group_id = r.group_id AND g.excluded = '0'
		GROUP BY r.year";
$result = mysql_query ($sql) or exit (mysql_error());
$prev_year = mysql_fetch_assoc ($result);
$previous_year_enrollment = $prev_year["enrolled"];
mysql_free_result ($result);

csv_newline ();
csv ("Year");
csv ("Enrollment");
csv ("New");
csv ("Returning");
csv_newline ();
csv ($year - 1);
csv ($previous_year_enrollment);
csv_newline ();
csv ($year);
csv ($returning_trekkers + $new_trekkers);
csv ($new_trekkers);
csv ($returning_trekkers);
csv_newline();

// Age Distribution
// figure out which groups we're working with
$sql = "SELECT g.short_name FROM contacts AS c
		JOIN roster_memberships AS m ON m.contact_id = c.contact_id
		JOIN rosters AS r ON r.roster_id = m.roster_id AND r.year = '$year'
		JOIN roles AS l ON l.role_id = m.role_id AND l.is_staff = '0'
		JOIN groups AS g ON g.group_id = r.group_id AND g.excluded = '0'
		GROUP BY g.short_name
		ORDER BY g.short_name ASC";
$groups = mysql_query ($sql) or exit (mysql_error());

// create a basic, empty array of ages
$demographics_ages = array ("Child" => array(), 9 => array(), 10 => array(), 11 => array(), 12 => array(), 13 => array(), 14 => array(), 15 => array(), 16 => array(), 17 => array(), 18 => array(), 19 => array(), "Adult" => array(), "Unknown" => array());

// start outputting column headers
csv_newline ();
csv ("Age");
csv ("Trekkers");

// output the headers for each group column
while ($group = mysql_fetch_assoc ($groups))
{
	while (list ($index, $array) = each ($demographics_ages))
	{
		$demographics_ages[$index]["Trekkers"] = 0;
		$demographics_ages[$index][$group["short_name"]] = 0;
	}
	reset ($demographics_ages);
	csv ($group["short_name"]);
}
csv_newline ();

// tabulate the ages for the trekkers
while ($trekker = mysql_fetch_assoc ($trekkers))
{
	$byear = substr ($trekker["birth_date"], 0, 4);
	$bmonth = substr ($trekker["birth_date"], 5, 2);
	$bday = substr ($trekker["birth_date"], 7, 2);

	$age = $year - $byear + ($bmonth > 6 ? -1 : 0);

	if ($age > 100)
	{
		$age = "Unknown";
	}
	else if ($age > 19)
	{
		$age = "Adult";
	}
	else if ($age < 9)
	{
		$age = "Child";
	}

	$demographics_ages[$age]["Trekkers"]++;
	$demographics_ages[$age][$trekker["short_name"]]++;
}
mysql_data_seek ($trekkers, 0);

// output the table of ages
while (list ($age, $tallies) = each ($demographics_ages))
{
	csv ($age);
	while (list($group, $tally) = each ($tallies))
	{
		csv ($tally);
	}
	csv_newline ();
}


// Geographic Distribution

// based on the Library of Congress definitions
// http://memory.loc.gov/ammem/gmdhtml/rrhtml/regdef.html
$regions = array ("New England" => array ("CT", "ME", "MA", "NH", "RI", "VT"),
				  "Mid-Atlantic" => array ("DE", "DC", "MD", "NJ", "NY", "PA"),
				  "Southeast" => array ("AL", "GA", "KY", "LA", "MS", "NC", "SC", "TN", "VA", "WV"),
				  "Midwest" => array ("IL", "IA", "IN", "KS", "MI", "MN", "MO", "NE", "ND", "OH", "SD", "WI"),
				  "Southwest" => array ("AR", "AZ", "CA", "CO", "NV", "NM", "OK", "TX"),
				  "Northwest" => array ("ID", "OR", "MT", "WA", "WY"),
				  "Hawaii and Alaska" => array ("AK", "HI"));
$demographics_regions = array("New England" => 0,
							  "Mid-Atlantic" => 0,
							  "Southeast" => 0,
							  "Midwest" => 0,
							  "Southwest" => 0,
							  "Northwest" => 0,
							  "Hawaii and Alaska" => 0);

while ($trekker = mysql_fetch_assoc ($trekkers))
{
	while (list ($region, $states) = each ($regions))
	{
		for ($i = 0; $i < sizeof ($states); $i++)
		{
			if ($trekker["state"] == $states[$i])
			{
				$demographics_regions[$region]++;
			}
		}
	}
	reset ($regions);
}
mysql_data_seek ($trekkers, 0);

csv_newline ();
csv ("Region");
csv ("Trekkers");
csv_newline ();
while (list ($region, $tally) = each ($demographics_regions))
{
	csv ($region);
	csv ($tally);
	csv_newline();
}

csv_end ();

?>