<?php 

/*
    The class, DBConnection, creates the connection to the database specified in the .env file. 
    The .env file is parsed for the necessary variables in the constructor.

    The class, Article, allows for the connection of an Article object to be saved to the database using 
    the save method.
*/

class DBConnection 
{
    public function __construct()
    {
        $env = file_get_contents(".env");
        $lines = explode("\n",$env);
        $r = array();
    
        $iterateSecondLoop = false;
        $breakLoop = false;
    
        $keyValuePairsArr = array();
    
        $b = 0;
        foreach($lines as $line){
            $r = array();
            for($i=0; (($i<strlen($line)) && ($line[$i] != "=") && ($iterateSecondLoop == false) && ($breakLoop == false)); $i++){
                $r[$i] = $line[$i];
    
                if($line[$i + 1] == "="){
                    $iterateSecondLoop = true;
                }
            }
            $rString = implode("", $r);
            $rString = trim($rString);
    
            $t = array();
            for($x=$i; ($x<strlen($line) && ($iterateSecondLoop == true)) && ($breakLoop == false); $x++){
                $t[$x] = $line[$x];
            }
    
            $tRemovedChars = array();
            $a = 0;
            foreach($t as $tChar){
                if(($tChar != "=") && ($tChar != " ") && ($tChar != "\n")){
                    $tRemovedChars[$a] = $tChar;
                }
                ++$a;
            }
    
            $iterateSecondLoop = false;
            $tRemovedCharsString = implode("", $tRemovedChars);
            
            $keyValuePairsArr[$b] = [
                "name" => $rString,
                "value" => $tRemovedCharsString,
            ];
    
            ++$b;
        }
    
        $charset = 'utf8mb4';
    
        foreach($keyValuePairsArr as $keyValuePair){
            if($keyValuePair["name"] == "DB_CONNECTION"){
                $connection = trim($keyValuePair["value"]);
            } else if($keyValuePair["name"] == "DB_HOST"){
                $host = trim($keyValuePair["value"]);
            } else if($keyValuePair["name"] == "DB_DATABASE"){
                $db = trim($keyValuePair["value"]);
            } else if($keyValuePair["name"] == "DB_USERNAME"){
                $user = trim($keyValuePair["value"]);
            } else if($keyValuePair["name"] == "DB_PASSWORD"){
                $pass = trim($keyValuePair["value"]);
            }
        }
    
        $dsn = "$connection:host=$host;dbname=$db;charset=$charset";
    
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        $this->pdo = $pdo;
    }
}

class Article extends DBConnection
{
    public $title;
    public $content;
    public $coverImagePath;
    public $columns = ['title', 'content', 'cover_image_path'];

    public function save(){
        foreach($this as $key=>$value){
            if($key == "title"){
                $this->title = $value; 
            } else if($key == "content"){
                $this->content = $value;
            } else if($key == "cover_image_path"){
                $this->coverImagePath = $value;
            } 
        }

        $sql = "INSERT INTO articles (title, content, cover_image_path) VALUES (?,?,?)";
        $dbConnection = new DBConnection;
        $stmt= $dbConnection->pdo->prepare($sql);
        $stmt->execute([$this->title, $this->content, $this->coverImagePath]);
    }

}

$article = new Article;
$article->title = "Title";
$article->content = "Content";
$article->cover_image_path = "None";
$article->save();

$dbConnection = new DBConnection;

$stmt = $dbConnection->pdo->query('SELECT * FROM articles');
while($row = $stmt->fetch())
{
    echo $row['title'] . "\n";
}

die();