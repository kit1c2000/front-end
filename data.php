<?php
require_once("config.inc.php");

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
$query = $db->prepare("select * from globalgeodata where longitude > -3.3 and longitude < -3 and latitude < 56 and latitude > 55.8;");
$query->execute();
$data = $query->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/tab-separated-values');

// get the columns in the dataset
$columnNames = array();
$columnCount = $query->columnCount();
for ($i = 0; $i < $columnCount; $i++)
{
	$columnMeta = $query->getColumnMeta($i);
	$columnNames[] = $columnMeta['name'];
}

echo implode("\t", $columnNames) . "\r\n";

foreach ($data as $row)
{
	echo implode("\t", $row) . "\r\n";
}
