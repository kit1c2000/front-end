<?php
/** README:
 *
 * Output formats: use ?format= and then one of the following:
 *    debug  - uses PHP's print_r to show the data values held
 *    tsv    - output in tab-separated values format
 *    tsvfm  - output in tsv, but show nicely in the browser
 *    json   - JSON format
 *    jsonfm - JSON format shown in the browser
 *
 * The query to execute can be specified by the &query= parameter.
 *
 * To add new queries to this list, add a variable with the name you want to use 
 * containing the query, then add the variable name to $queryVariables.
 *
 * You should be always querying as ?query=foo&format=bar  (parameter order not significant, but both present)
 */

require_once("config.inc.php");

// QUERY LIST HERE!
$queryVariables = array("schoolscontribpertopic", "debug");

$schoolscontribpertopic = "SELECT scpt.*, dates.date1 FROM vw_schoolscontrpertopic scpt JOIN (SELECT  m1 AS date1 FROM (SELECT ((SELECT MIN(StartDate) FROM vw_schoolscontrpertopic) - INTERVAL DAYOFMONTH((SELECT  MAX(EndDate) FROM vw_schoolscontrpertopic)) - 1 DAY) + INTERVAL m MONTH AS m1 FROM (SELECT  @ROWNUM:=@ROWNUM + 1 AS m FROM (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t1, (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t2, (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t3, (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) t4, (SELECT @ROWNUM:=- 1) t0) d1) d2 WHERE m1 <= (SELECT MAX(EndDate) FROM vw_schoolscontrpertopic) ORDER BY m1) dates WHERE dates.date1 BETWEEN scpt.StartDate AND scpt.EndDate ORDER BY scpt.TopicId, dates.date1;";
$debug = "SELECT 'sekrit' AS secretSitePassword FROM dual;"; // little easter egg... since http://is.gd/9HluJs got reverted :(
// END OF QUERY LIST!

// default to sane value
$queryToUse = "debug";
if(isset($_GET['query']))
{
	// sanitise the requested variable
	if(in_array($_GET['query'], $queryVariables ))
	{
		// the query is from the list of accepted values, so it's fine.
		$queryToUse = $_GET['query'];
	}
}

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
$query = $db->prepare($$queryToUse);
$query->execute();

// default to sane value
$dataFormat = "debug";
if(isset($_GET['format']))
{
	if(in_array($_GET['format'], array("json", "jsonfm", "tsv", "tsvfm", "debug")))
	{
		$dataFormat = $_GET['format'];
	}
}

if($dataFormat == "tsv")
{
	header('Content-Type: text/tab-separated-values');
	header('Content-Disposition: attachment; filename="data.tsv"');
} 
elseif($dataFormat == "json")
{
	header('Content-Type: application/json');
	header('Content-Disposition: attachment; filename="data.json"');
}
else
{
	header('Content-Type: text/plain');
}

// get the columns in the dataset
$columnNames = array();
$columnCount = $query->columnCount();
for ($i = 0; $i < $columnCount; $i++)
{
	$columnMeta = $query->getColumnMeta($i);
	$columnNames[] = $columnMeta['name'];
}

// PROCESS DATA INTO OUTPUT FORMAT

if($dataFormat == "tsv" || $dataFormat == "tsvfm")
{
	$data = $query->fetchAll(PDO::FETCH_ASSOC);
	
	echo implode("\t", $columnNames) . "\r\n";

	foreach ($data as $row)
	{
		echo implode("\t", $row) . "\r\n";
	}
}

if($dataFormat == "json" || $dataFormat == "jsonfm")
{
	$data = $query->fetchAll(PDO::FETCH_OBJ);

	echo json_encode($data);
}

if($dataFormat == "debug")
{
	$data = $query->fetchAll(PDO::FETCH_ASSOC);

	print_r($data);
}
