<?php
/* Example command to add to MyllyBOT */

/* Adding can be done in the IRC channel the bot is currently connected by typing
 * !addcmd <command> <url>
 * <command> - Name of the command to use in the IRC channel. For example <command> = youtube - can be accessed with !youtube in IRC
 * <url> - URL to PHP file which handles the command. For example http://myllyserver.com/youtube.php
 */

/* Variables come in $_REQUEST from the bot */
$var = $_REQUEST['var'];

if($var == "")
{
	echo "Type something to search for example !youtube nyan";
	exit();
}
else
{
	$var = str_replace("|/", "+", $var);
	$var = htmlentities($var);

	/* Search query based on the variable */
	$url = 'http://www.youtube.com/results?search_query=' . $var;

	/* Search for the first video in search */
	$needle = 'watch?v=';
	$lines = file($url);
	$result = "";

	foreach( $lines as $line_num => $line ) 
	{
		$start = strpos($line, $needle);
	
		if($start != 0)
		{
			$end = strpos($line, '"', $start);
			$result = substr($line, $start, $end-$start);
			break;
		}
	}

	if($result != "")
	{
		/* Result found, output to the bot to forward it to IRC */
		echo 'http://www.youtube.com/' . $result;
	}
	else
	{
		echo "Youtube: NOT FOUND.";
	}
}

?>
