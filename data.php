<?php
require_once("config.inc.php");

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
$query = $db->prepare("SELECT scpt.*, dates.date1 FROM vw_schoolscontrpertopic scpt JOIN (SELECT  m1 AS date1 FROM (SELECT ((SELECT MIN(StartDate) FROM vw_schoolscontrpertopic) - INTERVAL DAYOFMONTH((SELECT  MAX(EndDate) FROM vw_schoolscontrpertopic)) - 1 DAY) + INTERVAL m MONTH AS m1 FROM (SELECT  @ROWNUM:=@ROWNUM + 1 AS m FROM (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t1, (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t2, (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t3, (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t4, (SELECT @ROWNUM:=- 1) t0) d1) d2 WHERE m1 <= (SELECT MAX(EndDate) FROM vw_schoolscontrpertopic) ORDER BY m1) dates WHERE dates.date1 BETWEEN scpt.StartDate AND scpt.EndDate ORDER BY scpt.TopicId, dates.date1;");
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
