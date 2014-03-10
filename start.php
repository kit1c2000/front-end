<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>SVG</title>
<style>
		body { font: 0.7em sans-serif; margin: 0px; }

		.arc path { stroke: #fff; }
		.axis path,
		.axis line { fill: none; stroke: #000; shape-rendering: crispEdges; }
		.browser text { text-anchor: end; }
	</style>
</head>

<body>
<?php
require_once("config.inc.php");
	
	// Connect to server and select databse.
	$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
	
	$queryToUse="
	SELECT * FROM meng_rcuk.vw_hw_topicmap_grants_100 GROUP BY TopicID limit 0, 20;
	";
	
	$query = $db->prepare($queryToUse);
	$query->execute();

	$result = $query->fetchAll();
	
	if (count($result) > 0)
	foreach($result as $row)
		{
			echo "<a href='show.php?topicID=".$row['TopicID']."'>".$row['TopicID']."</a><br/><br/>";
		}
?>
</body>

</html> 