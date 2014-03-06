<?php
	require_once("config.inc.php");
	
	$main = function($print_func) {
    $print_func("date\tmontlyfunding\n");
	
	global $dbhost;
	global $dbname;
	global $dbuser;
	global $dbpass;
	
	// Connect to server and select database.
	$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);

	$queryToUse="SELECT * FROM meng_rcuk.information where OrganisationName = 'heriot-watt university' AND ID = :id;";
	$query = $db->prepare($queryToUse);
	$query->bindValue(":id", $_GET['id']);
	$query->execute();
	
	$result = $query->fetchAll();
	
	if (count($result) > 0)
	foreach($result as $row)
		{
			$totalgrantvalue = $row['TotalGrantValue'];
			$startdate = $row['StartDate'];
			$enddate = $row['EndDate'];
			
			$date1 = DateTime::createFromFormat("Y-m-d H:i:s", $startdate);
			$date2 = DateTime::createFromFormat("Y-m-d H:i:s", $enddate);
			
			$y1 = $date1->format("Y");
			$m1 = $date1->format("m");
			
			$y2 = $date2->format("Y");
			$m2 = $date2->format("m");
			
			$numMonth = ($y2-$y1)*12+($m2-$m1)+1;
			$avgMonthlyFunding = $totalgrantvalue/$numMonth;
			
			$digit = strlen(round($avgMonthlyFunding, 0, PHP_ROUND_HALF_DOWN));
			$res = "1";
			for ($i = 1; $i <= $digit; $i++){
				$res = $res."0";
			}
			$_SESSION['MonthlyFunding'] = $res;

			$y = $y1;
			$m = $m1;
			
			for ($i = 1; $i <= $numMonth; $i++) {
				if (strlen($m) == 1) {
					$m = "0".$m;
				}
				$print_func($y.$m."\t".$avgMonthlyFunding."\n");
				
				$m++;
				
				if ($m > 12) {
					$y++;
					$m = 1;
				}
			}
		}
};
$f = fopen("monthlyfunding.tsv", "wb");
$main(function($output) use ($f) {
    fwrite($f, $output);
    //echo $output;
});
fclose($f);

/*
		header("Content-Disposition: attachment; filename=\"monthlyfunding.tsv\"");
		header("Content-Type: text/tab-delimited-values");
	
		echo "date\tmontlyfunding\n";
		
		
		while ($row = mysql_fetch_assoc($result))
		{
			$granttitle = $row['GrantTitle'];
			$totalgrantvalue = $row['TotalGrantValue'];
			$startdate = $row['StartDate'];
			$enddate = $row['EndDate'];
			
			$date1 = DateTime::createFromFormat("Y-m-d H:i:s", $startdate);
			$date2 = DateTime::createFromFormat("Y-m-d H:i:s", $enddate);
			
			$y1 = $date1->format("Y");
			$m1 = $date1->format("m");
			
			$y2 = $date2->format("Y");
			$m2 = $date2->format("m");
			
			$numMonth = ($y2-$y1)*12+($m2-$m1)+1;
			$avgMonthlyFunding = $totalgrantvalue/$numMonth;
			
			$y = $y1;
			$m = $m1;
			
			for ($i = 1; $i <= $numMonth; $i++) {
				printf("%s%02d\t%s\n",
				$y,
				$m,
				$avgMonthlyFunding
				);
				
				$m++;
				
				if ($m > 12) {
					$y++;
					$m = 1;
				}
			}
            
   
		}
		*/
