<?php

function make_seed() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}
srand(make_seed());

$dir = opendir ('graphics/cow');
while (false !== ($file = readdir ($dir)))
{
	if ($file[0] != '.') $list[] = $file;
}
rsort ($list);

$recent = (isset($HTTP_GET_VARS['recent']) ? $HTTP_GET_VARS['recent'] : -1);
do {
	$cow = rand() % sizeof ($list);
} while ($cow == $recent);

$file = $list[$cow];

?>

<html>
<head>
	<link rel="stylesheet" href="stylesheet.css">
</head>
<body>
<h1>Gulch DB</h1>
<h3>Cottonwood Gulch Foundation</h3>
		<table><tr class="stripe"><th>Help</th><td>For assistance with this database, please contact Seth Battis (<a href="mailto:&quot;Seth%20Battis&quot; &lt;seth@cottonwoodgulch.org&gt;">seth@cottonwoodgulch.org</a>)</td></tr>
		<tr><th>Generic Mailing List</th><td><form action ="scripts/mailing_list.php"><input type="submit" value="Export CSV"></form><br>Generic mailing list sorted in country and then zip code order, with contacts grouped by address. This excludes the recurring business contacts group of individuals, all business contacts, deceased contacts and school group participants.</tr>
		<tr class="stripe"><th rowspan="2">Tuition Reports</th><td><form method="get" action="scripts/tuition_report.php">Monthly <select name="month">
		<?php
		$now = getdate();
		$year = $now[year];
		$month = $now[mon];

		for ($y = $year; $y >= 2003; $y--)
		{
			if ($y != $year)
			{
				$month = 12;
			}

			if ($y != 2003)
			{
				$mm = 0;
			}
			else
			{
				$mm = 9;
			}

			for ($m = $month; $m > $mm; $m--)
			{
				switch ($m)
				{
					case 12: $mn = 'December'; break;
					case 11: $mn = 'November'; break;
					case 10: $mn = 'October'; break;
					case 9: $mn = 'September'; break;
					case 8: $mn = 'August'; break;
					case 7: $mn = 'July'; break;
					case 6: $mn = 'June'; break;
					case 5: $mn = 'May'; break;
					case 4: $mn = 'April'; break;
					case 3: $mn = 'March'; break;
					case 2: $mn = 'February'; break;
					case 1: $mn = 'January';
				}
				$mf = '0';
				if ($m > 9)
				{
					$mf = '';
				}
				echo "<option value=\"$y-$mf$m\">$mn $y (FY " . ($m < 10 ? $y-1 . "-$y" : $y . '-' . ($y + 1)) . ")</option>\r";
			}
		}
		?>
		</select>
		<input type="submit" value="Export CSV">
		</form></td></tr><tr class="stripe"><td>
		<form method="get" action="scripts/tuition_ytd.php">
		Year-to-date <select name="year">
		<?php
			$now = getdate();
			$year = $now['year'];

			for ($y = ($month > 9 ? $year + 1 : $year); $y >= 2004; $y--)
			{
				echo "<option value=\"$y\">$y Rosters (FY " . ($y - 1) . "-$y)</option>\r";
			}
		?>
		</select>
		<input type="submit" value="Export CSV"></form></td></tr>
		<tr><th>Enrollment Demographics</th><td>
		<form method="get" action="scripts/enrollment_demographics.php">
		Rosters <select name="year">
		<?php
			$now = getdate();
			$year = $now['year'];

			for ($y = $year; $y >= 2004; $y--)
			{
				echo "<option value=\"$y\">$y</option>\r";
			}
		?>
		</select>
		<input type="submit" value="Export CSV"></form></td></tr>
		<tr class="stripe"><th rowspan="2">Contributor List</th><td>
		<form method="get" action="scripts/contributors.php">
		Fiscal Year <select name="year">
		<?php
			$now = getdate();
			$year = $now['year'];

			for ($y = $year; $y >= 2003; $y--)
			{
				echo "<option value=\"$y\">$y-" . ($y + 1) . "</option>\r";
			}
		?>
		</select>
		<input type="submit" value="Export CSV"></form></td></tr>
		<tr class="stripe"><td>
		<form method="get" action="scripts/contributors-quickbooks.php">
		Fiscal Year (named for QuickBooks) <select name="year">
		<?php
			$now = getdate();
			$year = $now['year'];

			for ($y = $year; $y >= 2003; $y--)
			{
				echo "<option value=\"$y\">$y-" . ($y + 1) . "</option>\r";
			}
		?>
		</select>
		<input type="submit" value="Export CSV"></form></td></tr>
		<tr>
			<th rowspan="5">QuickBooks</th>
			<td><form method="post" action="scripts/quickbooks_customers.php?start=0&end=1">
				Export all donors and 2008 Trekkers <input type="submit" value="Export CSV">
			</form></td>
		</tr>
		<tr>
			<td><form method="post" action="scripts/quickbooks_customers.php?start=2&end=2">
				Export 2007 Trekkers not yet enrolled for 2008 <input type="submit" value="Export CSV">
				</form></td>
		</tr>
		<tr>
			<td><form method="post" action="scripts/quickbooks_customers.php?start=3&end=3">
				Export joint donors as single customers <input type="submit" value="Export CSV">
				</form></td>
		</tr>
		<tr>
			<td><form method="post" action="scripts/quickbooks_customers.php">
			Export <b>all of the above</b> as customers for Quickbooks (may take a minute or two) <input type="submit" value="Export CSV">
			</form></td>
		</tr>
		<tr>
			<td><form method="post" action="scripts/2006_tuition_payments.php">
				2006 tuition payments as a readable spreadsheet <input type="submit" value="Export CSV">
				</form></td>
		</tr>
		<tr class="stripe"><th>Cow</th><td><a href="home.php?recent=<?php echo $cow; ?>"><img src="graphics/cow/<?php echo $file; ?>"></a></td></tr>
		</table>
	</body>
</html>