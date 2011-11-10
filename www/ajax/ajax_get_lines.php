<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../conf_inc.php';

$t = isset($_POST['t'])?($_POST['t']+0):0;

$ret_arr = array('lines' => 0, 'time' => microtime(true));

$link = mysql_connect($cfg['database_host'], $cfg['database_user'], $cfg['database_password']);
if (!$link) {
	$ret_arr['error'] = 'link';
	die(json_encode($ret_arr));
}
if (!mysql_select_db($cfg['database_name'])) {
	$ret_arr['error'] = 'db';
	die(json_encode($ret_arr));
}

$q = "SELECT * FROM log WHERE Timestamp > $t ORDER BY Timestamp DESC LIMIT 20";
$res = mysql_query($q);


//get data in reverse order
for ($i = mysql_num_rows($res) - 1; $i >= 0; $i--) {
	if (!mysql_data_seek($res, $i)) {
		continue;
	}

	if (!($row = mysql_fetch_assoc($res))) {
		continue;
	}

	$ret_arr['data'][] = htmlentities( 
		'@'.
		date('H:i:s', $row['Timestamp']).
		' ('.
		$row['nick'].
		') '.
		$row['msg']);
}
$ret_arr['lines'] = count($ret_arr['data']);
echo json_encode($ret_arr);
?>
