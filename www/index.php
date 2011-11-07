<?php


?>
<html>
<head>
<title>Myllybot backlogist</title>
<script src="http://code.jquery.com/jquery-1.7.min.js"></script>
<script>
var last_data = 0;

function fetch_new_datas() {
jQuery.post('ajax/ajax_get_lines.php', { t:last_data }, function(data) {
		

	});

}

setInterval("fetch_new_datas()", 5000)

</script>
</head>
<body>
<div id="content"></div>
</body>
</html>

<?php



?>
