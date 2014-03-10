<?php
// config section: -------
$infile = "SOM.csv";
$emptyVal = "[]";
$sep = "  ";
// -----------------------

// helper class
class wrapper
{
    public $name = "Hexagons";
    public $children;
}

// helper class
class hex
{
    public $hex;
    public $children;
}

class topic
{
    public $topicId;   
    public $words;   
    public $school;
    
    // reference-based wrapper for constructor
    public static function create(&$data, $key)
    {
        $data = new topic($data);   
    }
    
    public function __construct($topic)
    {
        global $topicStatement;
        
        $topicStatement->execute(array(":id" => $topic)); // execute with parameters
        $data = $topicStatement->fetch(PDO::FETCH_ASSOC);
        $topicStatement->closeCursor(); // IMPORTANT! Without closing the refcursor you won't free up resources or the pointer to be reused for the next execution.
        
        $this->words = explode(" ", $data['TopicLabel']);
        $this->topicId = $topic;
        $this->school = $data['OrganisationDepartment']; // FIXME
    }
}

require_once("config.inc.php");

$db = new PDO("mysql:host=$dbhost;dbname=meng_rcuk;", $dbuser, $dbpass);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// disable prepared statement emulation
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// prepare the statement once, execute multiple times. It's much faster that way cos you only have to send the new values to the server.
$topicStatement = $db->prepare("SELECT TopicLabel, OrganisationDepartment FROM vw_hw_topics_100 WHERE TopicID = :id;");

// read file
$file = file_get_contents('SOM.csv');

// strip lines
$file = str_replace("\n", ",", $file);

// explode on commas
$fileData = explode(",", $file);

$counter = 0;

// step through the array, and poke it into shape
array_walk($fileData, function(&$value, $key)
{
    global $counter, $emptyVal, $sep;
    
    if($value == $emptyVal)
    {
        // empty cell
        $value = array();
    }
    else
    {
        // non-empty cell
        $value = explode($sep, $value);
    }
    
    // create the topics in-place.
    array_walk($value, 'topic::create');
    
    $hex = new hex();
    $hex->hex = $counter++;
    $hex->children = $value;
    
    $value = $hex;
});

// wrap it as requested
$wrapper = new wrapper();
$wrapper->children = $fileData;

header('Content-Type: application/json');

echo "var HexJSON = \n";
echo json_encode($wrapper);
echo ";";
// end.
die();