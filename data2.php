<?php
require_once("config.inc.php");

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$TopicID = is_integer($_GET['topic']) ? $_GET['topic'] : 0;

$db->exec("CREATE TABLE IF NOT EXISTS tmp_topic_month_data (`Percentage` decimal(27,4), `TopicId` int(11), `HoldingDepartmentName` varchar(40), `date` datetime) ENGINE=InnoDB;");
$db->exec("SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;");
$db->beginTransaction();
$db->exec("DELETE FROM tmp_topic_month_data;");
$db->exec(<<<EOF
INSERT INTO tmp_topic_month_data
SELECT scpt.`(COUNT(i.GrantRefNumber)/gpt.GrantCount*100)`, scpt.TopicId, scpt.HoldingDepartmentName, dates.date1 FROM vw_schoolscontrpertopic scpt
JOIN (SELECT m1 AS date1 FROM (
SELECT 
((  SELECT MIN(StartDate) 
    FROM vw_schoolscontrpertopic) 
    - INTERVAL DAYOFMONTH((SELECT MAX(EndDate) FROM vw_schoolscontrpertopic))-1 DAY) 
+INTERVAL m MONTH AS m1
FROM
(
SELECT @ROWNUM:=@ROWNUM+1 AS m FROM
(SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t1, (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t2,
(SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t3,
(SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t4,
(SELECT @ROWNUM:=-1) t0
) d1
) d2 
WHERE m1<=(SELECT MAX(EndDate) FROM vw_schoolscontrpertopic)
ORDER BY m1) dates
WHERE dates.date1 BETWEEN scpt.StartDate AND scpt.EndDate
ORDER BY scpt.TopicId, dates.date1;
EOF
);
$getQueryQuery = $db->prepare(<<<EOF
SELECT 
    CONCAT('SELECT date, ##DEPTS## FROM (SELECT DISTINCT date FROM tmp_topic_month_data) d ', group_concat(queryPart SEPARATOR ' '), ' ORDER BY date;') AS query 
FROM 
(
	SELECT 
        CONCAT('LEFT JOIN (SELECT date AS d', @rownum:=@rownum + 1, ', Percentage AS \'', dept.HoldingDepartmentName, '\' FROM tmp_topic_month_data where HoldingDepartmentName = \'', dept.HoldingDepartmentName, '\' and TopicId = {$TopicID}) t', @rownum, ' ON d.date = t', @rownum, '.d', @rownum) as queryPart
    FROM 
	(
		SELECT DISTINCT 
			HoldingDepartmentName
		FROM 
			tmp_topic_month_data
	) dept
    JOIN (SELECT @rownum:=0) r
) s;
EOF
);
$getQueryQuery->execute();
$getQuery = $getQueryQuery->fetchColumn();
$deptQuery = $db->prepare(<<<EOF
SELECT 
    group_concat(HoldingDepartmentName
        SEPARATOR ', ')
FROM
    (SELECT DISTINCT
        CONCAT('COALESCE(`', HoldingDepartmentName, '`, 0) AS `', HoldingDepartmentName, '`') as HoldingDepartmentName
    FROM
        tmp_topic_month_data) d;
EOF
);
$deptQuery->execute();
$depts = $deptQuery->fetchColumn();
$getQuery = str_replace("##DEPTS##", $depts, $getQuery);
$query = $db->prepare($getQuery);
$query->execute();

header('Content-Type: text/tab-separated-values');
header('Content-Disposition: attachment; filename="data.tsv"');

$columnNames = array();
$columnCount = $query->columnCount();
for ($i = 0; $i < $columnCount; $i++)
{
	$columnMeta = $query->getColumnMeta($i);
	$columnNames[] = $columnMeta['name'];
}

$data = $query->fetchAll(PDO::FETCH_ASSOC);

echo implode("\t", $columnNames) . "\r\n";
foreach ($data as $row)
{
	echo implode("\t", $row) . "\r\n";
}

$db->rollBack();