<?php
header('Content-Type: text/html; charset=utf-8');


?>
<html>
<head>
<title>Myllybot backlogist</title>
<script src="http://code.jquery.com/jquery-1.7.min.js"></script>
<script>
var last_data = 0;
var timeout = 5000;

function fetch_new_datas() {
	jQuery.post('ajax/ajax_get_lines.php', { t:last_data }, function(data) {
		json = JSON.parse(data);
		if(json.error !== undefined) {
			alert(json.error);
		} 
		if(json.lines > 0) {
//			jQuery("#content").append("Yes new lines<br>");
			for(var i = 0; i < json.lines; i++) {
				jQuery("#content").append(json.data[i]+ "<br>");
			}
			last_data = json.time;
			timeout = 5000;

			var obj = document.getElementById('content');
			obj.scrollTop = obj.scrollHeight;
		} else {
			timeout = timeout*1.1;
			timeout = Math.min(600000, timeout);
//			jQuery("#content").append("no new data<br>");
		}

	});
	setTimeout("fetch_new_datas()", timeout);
}

fetch_new_datas();

</script>
<style>
#content {
	border: 1px solid black;
	height:99%;
	width: 99%;
	overflow: auto;
}
</style>
</head>
<body>
<div id="content"></div>
</body>
</html>

<?php



?>
