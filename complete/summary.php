<?php
	require_once ('../library.inc.php');

	$cid = (isset($_GET['cid']) ? $_GET['cid'] : exit ("<strong>Unspecified Contact:</strong> A contact must be specified to load this form."));

	$contact_query = "SELECT *
					  FROM contacts
					  WHERE contact_id = $cid
					  LIMIT 1";
	$contact_result = mysql_query ($contact_query) or exit (mysql_error());
	$contact = mysql_fetch_assoc ($contact_result);

	if ($rbac->check('view_phone', $_SESSION['user']) || $rbac->check('view_contact_information', $_SESSION['user'])) {
		$phones_query = "SELECT p.*, pt.*, c.*
						 FROM phones AS p
						 JOIN phone_associations AS pa ON pa.phone_id = p.phone_id
						 JOIN contacts AS c ON c.contact_id = pa.contact_id
						 JOIN phone_types AS pt ON pt.phone_type_id = p.phone_type_id
						 WHERE c.contact_id = $cid
						 ORDER BY pt.rank ASC";
		$phones = mysql_query ($phones_query, $GLOBALS['db']['link']) or exit (mysql_error());
	}

	if ($rbac->check('view_email', $_SESSION['user']) || $rbac->check('view_contact_information', $_SESSION['user'])) {
		$emails_query = "SELECT e.*, et.*, c.*
						 FROM emails AS e
						 JOIN email_associations AS ea ON ea.email_id = e.email_id
						 JOIN contacts AS c ON c.contact_id = ea.contact_id
						 JOIN email_types AS et ON et.email_type_id = e.email_type_id
						 WHERE c.contact_id = $cid
						 ORDER BY et.rank ASC";
		$emails = mysql_query ($emails_query, $GLOBALS['db']['link']) or exit (mysql_error());
	}

	if ($rbac->check('view_url', $_SESSION['user']) || $rbac->check('view_contact_information', $_SESSION['user'])) {
		$urls_query = "SELECT u.*, ut.*, c.*
					   FROM urls AS u
					   JOIN url_associations AS ua ON ua.url_id = u.url_id
					   JOIN contacts AS c ON c.contact_id = ua.contact_id
					   JOIN url_types AS ut ON ut.url_type_id = u.url_type_id
					   WHERE c.contact_id = $cid
					   ORDER BY ut.rank ASC";
		$urls = mysql_query ($urls_query, $GLOBALS['db']['link']) or exit (mysql_error());
	}

	if ($rbac->check('view_address', $_SESSION['user']) || $rbac->check('view_contact_information', $_SESSION['user'])) {
		$addresses_query = "SELECT a.*, at.*, c.*
							FROM addresses AS a
							JOIN address_associations AS aa ON a.address_id = aa.address_id
							JOIN contacts AS c ON c.contact_id = aa.contact_id
							JOIN address_types AS at ON at.address_type_id = a.address_type_id
							WHERE c.contact_id = $cid
							ORDER BY at.rank ASC";
		$addresses = mysql_query ($addresses_query, $GLOBALS['db']['link']) or exit (mysql_error());
	}

	if ($rbac->check('view_relationship', $_SESSION['user']) || $rbac->check('view_contact_information', $_SESSION['user'])) {
		$relationships_query = "SELECT *
								FROM relationships AS r
								JOIN contacts AS c ON c.contact_id = r.contact_id
								LEFT JOIN titles AS t ON t.title_id = c.title_id
								LEFT JOIN degrees AS d ON d.degree_id = c.degree_id
								JOIN relationship_types AS rt ON rt.relationship_type_id = r.relationship_type_id
								WHERE r.relative_id = $cid
								ORDER BY rt.rank ASC, c.primary_name ASC, c.first_name ASC";
		$relationships = mysql_query ($relationships_query, $GLOBALS['db']['link']) or exit (mysql_error());
	}

	if ($rbac->check('view_roster', $_SESSION['user']) || $rbac->check('view_contact_information', $_SESSION['user'])) {
		$groups_query = "SELECT r.*, g.*, l.*, c.*
						 FROM rosters AS r
						 JOIN roster_memberships AS m ON r.roster_id = m.roster_id
						 JOIN contacts AS c ON c.contact_id = m.contact_id
						 JOIN groups AS g ON g.group_id = r.group_id
						 LEFT JOIN roles AS l ON l.role_id = m.role_id
						 WHERE m.contact_id = $cid
						 ORDER BY r.year DESC";
		$groups = mysql_query ($groups_query, $GLOBALS['db']['link']) or exit (mysql_error());
	}

	if ($rbac->check('view_note', $_SESSION['user'])) {
		$notes_query = "SELECT * FROM notes WHERE contact_id = $cid ORDER BY modified ASC";
		$notes = mysql_query ($notes_query, $GLOBALS['db']['link']) or exit (mysql_error());
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Summary</title>
		<link rel="stylesheet" href="../stylesheet.css">
	</head>

	<body>
		<script src="../library.inc.js"></script>

		<dl>
			<?php if (mysql_num_rows($phones) > 0) { ?>
<dt><a href="phones.php?cid=<?php echo $cid; ?>">Phones</a></dt>
				<dd><table>
					<?php
						while ($phone = mysql_fetch_assoc ($phones))
						{
							$shared_query = "SELECT *
											 FROM phone_associations AS pa
											 JOIN contacts AS c ON c.contact_id = pa.contact_id
											 LEFT JOIN titles AS t ON t.title_id = c.title_id
											 LEFT JOIN degrees AS d ON d.degree_id = c.degree_id
											 WHERE pa.contact_id <> $cid AND pa.phone_id = " . $phone['phone_id'] . "
											 ORDER BY c.primary_name ASC, c.first_name ASC";
							$shared = mysql_query ($shared_query, $GLOBALS['db']['link']) or exit (mysql_error());
							echo "<tr><th>{$phone["phone_type"]}</th><td>";
							DisplayPhone ($phone);
							echo "</td>";
							if (mysql_num_rows ($shared) > 0)
							{
								echo '<td class="shared">Shared with ';
								while ($other = mysql_fetch_assoc ($shared))
								{
									echo '<a href="contacts.php?cid=' . $other['contact_id'] . '&detail=summary.php" target="content">' . Name($other['contact_id'], '%n %L %D') . '</a><br>';
								}
								echo '</td>';
							}
							echo "</tr>\n";
						} ?>
				</table></dd>
			<?php }; if (mysql_num_rows($emails) > 0) { ?>
				<dt><a href="emails.php?cid=<?php echo $cid; ?>">Emails</a></dt>
				<dd><table>
					<?php
						while ($email = mysql_fetch_assoc ($emails))
						{
							$shared_query = "SELECT *
											 FROM email_associations AS ea
											 JOIN contacts AS c ON c.contact_id = ea.contact_id
											 LEFT JOIN titles AS t ON t.title_id = c.title_id
											 LEFT JOIN degrees AS d ON d.degree_id = c.degree_id
											 WHERE ea.contact_id <> $cid AND ea.email_id = " . $email['email_id'] . "
											 ORDER BY c.primary_name ASC, c.first_name ASC";
							$shared = mysql_query ($shared_query, $GLOBALS['db']['link']) or exit (mysql_error());
							echo "<tr><th>{$email["email_type"]}</th><td>";
							DisplayEmail ($email);
							echo "</td>";
							if (mysql_num_rows ($shared) > 0)
							{
								echo '<td class="shared">Shared with ';
								while ($other = mysql_fetch_assoc ($shared))
								{
									echo '<a href="contacts.php?cid=' . $other['contact_id'] . '&detail=summary.php" target="content">' . Name($other['contact_id'], '%n %L %D') . '</a><br>';
								}
								echo '</td>';
							}
							echo "</tr>\n";
						} ?>
				</table></dd>
			<?php }; if (mysql_num_rows($urls) > 0) { ?>
				<dt><a href="urls.php?cid=<?php echo $cid; ?>">URLs</a></dt>
				<dd><table>
					<?php
						while ($url = mysql_fetch_assoc ($urls))
						{
							$shared_query = "SELECT *
											 FROM url_associations AS ua
											 JOIN contacts AS c ON c.contact_id = ua.contact_id
											 LEFT JOIN titles AS t ON t.title_id = c.title_id
											 LEFT JOIN degrees AS d ON d.degree_id = c.degree_id
											 WHERE ua.contact_id <> $cid AND ua.url_id = " . $url['url_id'] . "
											 ORDER BY c.primary_name ASC, c.first_name ASC";
							$shared = mysql_query ($shared_query, $GLOBALS['db']['link']) or exit (mysql_error());
							echo "<tr><th>{$url["url_type"]}</th><td>";
							DisplayURL ($url);
							echo "</td>";
							if (mysql_num_rows ($shared) > 0)
							{
								echo '<td class="shared">Shared with ';
								while ($other = mysql_fetch_assoc ($shared))
								{
									echo '<a href="contacts.php?cid=' . $other['contact_id'] . '&detail=summary.php" target="content">' . Name($other['contact_id'], '%n %L %D') . '</a><br>';
								}
								echo '</td>';
							}
							echo "</tr>\n";
						} ?>
				</table></dd>
			<?php }; if (mysql_num_rows($addresses) > 0) { ?>
				<dt><a href="addresses.php?cid=<?php echo $cid; ?>">Addresses</a></dt>
				<dd><table>
					<?php
						while ($address = mysql_fetch_assoc ($addresses))
						{
							$shared_query = "SELECT *
											 FROM address_associations AS aa
											 JOIN contacts AS c ON c.contact_id = aa.contact_id
											 LEFT JOIN titles AS t ON t.title_id = c.title_id
											 LEFT JOIN degrees AS d ON d.degree_id = c.degree_id
											 WHERE aa.contact_id <> $cid AND aa.address_id = " . $address['address_id'] . "
											 ORDER BY c.primary_name ASC, c.first_name ASC";
							$shared = mysql_query ($shared_query, $GLOBALS['db']['link']) or exit (mysql_error());
							echo "<tr><th>{$address["address_type"]}</th><td>";
							DisplayAddress ($address);
							echo "</td><td>";
							$google = $address["street_address_1"] . ", " . (strlen ($address["street_address_2"]) > 0 ? $address["street_address_2"] . ", " : "") . $address["city"] . ", " . $address["state"] . ", " . $address["postal_code"] . ($address["country"] != "United States" ? ", " . $address["country"] : "");
							//echo "<a target=\"_blank\" href=\"http://local.google.com/maps?q=$google&spn=2.189473,5.928223&iwloc=A&hl=en\">Map this address</a><br>";
							echo "<form name=\"google\" target=\"_new\" method=\"get\" action=\"http://local.google.com/maps\"><input name=\"q\" value=\"$google\" type=\"hidden\"><input name=\"spn\" value=\"2.189473,5.928223\" type=\"hidden\"><input name=\"iwloc\" value=\"A\" type=\"hidden\"><input name=\"hl\" value=\"en\" type=\"hidden\"><input type=\"submit\" value=\"Map\"></form>";

							if ($address["country"] == "United States")
							{
								$usps = "<form name=\"usps\" target=\"_new\" action=\"http://zip4.usps.com/zip4/zcl_0_results.jsp\" method=\"post\"><input name=\"firmname\" value=\"\" type=\"hidden\"><input name=\"address2\" maxlength=\"50\" type=\"hidden\" value=\"{$address["street_address_1"]}\"><input name=\"address1\" maxlength=\"50\" type=\"hidden\" value=\"{$address["street_address_2"]}\"><input name=\"city\" maxlength=\"50\" type=\"hidden\" value=\"{$address["city"]}\"><input name=\"state\" maxlength=\"2\" type=\"hidden\" value=\"{$address["state"]}\"><input name=\"urbanization\" value=\"\" type=\"hidden\"><input name=\"zip5\" maxlength=\"10\" type=\"hidden\" value=\"{$address["postal_code"]}\"><input name=\"submit\" value=\"Validate\" type=\"submit\"></form>";
								echo "$usps";
							}
							echo '<td class="shared">';
							if (mysql_num_rows ($shared) > 0)
							{
								echo 'Shared with ';
								while ($other = mysql_fetch_assoc ($shared))
								{
									echo '<a href="contacts.php?cid=' . $other['contact_id'] . '&detail=summary.php" target="content">' . Name($other['contact_id'], '%n %L %D') . '</a><br>';
								}
							}
							echo "</td></tr>\n";
						} ?>
				</table></dd>
			<?php }; if (mysql_num_rows($relationships) > 0) { ?>
				<dt><a href="relationships.php?cid=<?php echo $cid; ?>">Relationships</a></dt>
				<dd><table>
					<?php
						while ($relationship = mysql_fetch_assoc ($relationships))
						{
							echo '<tr><td>' . ucfirst ((strlen ($contact["gender"]) ? $relationship[$contact["gender"]] : $relationship["relationship_type"])) . ' of <a href="contacts.php?cid=' . $relationship['contact_id'] . '&detail=summary.php" target="content">' . Name($relationship['contact_id'], '%n %L %D', $relationship) . '</a></td></tr>';
						} ?>
				</table></dd>
			<?php }; if (mysql_num_rows($groups) > 0) { ?>
				<dt><a href="group_memberships.php?cid=<?php echo $cid; ?>">Groups</a></dt>
				<dd><table>
					<?php
						while ($group = mysql_fetch_assoc ($groups))
						{
							echo '<tr>';
							DisplayGroup ($group);
							echo '</tr>';
						} ?>
				</table></dd>
			<?php } if (mysql_num_rows ($notes) > 0) {?>
				<dt><a href="notes.php?cid=<?php echo $cid; ?>">Notes</a></dt>
				<dd><table>
					<?php
						while ($note = mysql_fetch_assoc ($notes))
						{
							echo '<tr><td class="note">' . $note['note'] . '</td><td class="shared">' . $note['modified'] . '</pre></td></tr>';
						}
					?>
				</table></dd>
			<?php } ?>
		</dl>
	</body>
</html>