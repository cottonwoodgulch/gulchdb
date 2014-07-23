document.write ('<h3>' + document.title + '</h3>');

function ShortLink (long_url, target)
{
	text = long_url;
	url = long_url;
	
	// remove http:// -- for ease
	clip = text.indexOf ('//');
	if (clip >= 0) text = text.substring (clip + 2, text.length);
	else url = 'http://' + url;
	
	// set aside server
	clip = text.indexOf ('/');
	if (clip < 0) clip = text.length;
	server = text.substring (0, clip);
	text = text.substring (clip + 1, text.length);

	// reduce to page and get variables
	while (text.indexOf ('/') >= 0)
	{
		clip = text.indexOf ('/');
		text = text.substring (clip + 1, text.length);
	}

	// remove get variables
	clip = text.indexOf ('?');
	if (clip >= 0) text = text.substring (0, clip);
	
	// recombine
	display = server;
	if (text.length > 0) display = display + '/.../' + text;
	
	document.write ('<a target="' + target + '" href="' + url + '" title="' + url + '">' + display + '</a>');
}