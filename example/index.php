<?php declare(strict_types=1);
include __DIR__."/../vendor/autoload.php";
function dump($data,$label)
{
    echo PHP_EOL." ".$label.':'.PHP_EOL;
    var_dump($data);
}
$config = [
    "host"=>"localhost",
    "dbname"=>"test",
    "username" =>"root",
    "password" =>"",
];

class Category
{
    public int $Id;
    public string $Title;
    public string $Url;
    public string $Status;
    public string $CreatedAt;
    public string $UpdatedAt;
}
$pdo = new Samitrimal\Pdo\Db($config);

$sql = "CREATE TABLE IF NOT EXISTS `category` (
    `Id` INT NOT NULL AUTO_INCREMENT ,
    `Title` TINYTEXT NOT NULL ,
    `Url` TINYTEXT NOT NULL ,
    `Status` tinyint(1) NOT NULL ,
    `CreatedAt` DATETIME NOT NULL,
    `UpdatedAt` DATETIME NOT NULL, PRIMARY KEY (`Id`));";
$pdo->Execute($sql);
$pdo->Execute("TRUNCATE TABLE `category`");
$data = [
    "Title"=>"Books",
    "Url"=>"books",
    "Status"=>1,
    "CreatedAt"=>date("Y-m-d H:i:s"),
    "UpdatedAt"=>date("Y-m-d H:i:s"),
];

for($i=0;$i<50;$i++)
{
  $data["Title"]   = $data["Title"]." ::: #".$i;
  $id =$pdo->Insert("category",$data);
 
}
echo "Insert Rows".PHP_EOL;

$data =["Title"=>"Books Updated"];
$updateRes = $pdo->Update("category",$data,["Id"=>1]);
dump($updateRes,"Update");
$sql="SELECT * from category where 1 and Id=:Id";
$item = $pdo->FetchObject($sql,["Id"=>1]);
dump($item,"Fetch Object With Params");
$item = $pdo->FetchClass($sql,Category::class,["Id"=>1]);
dump($item,"Fetch Class With Params");

$sql="SELECT * from category where 1 and Id=2";
$item = $pdo->FetchObject($sql);
dump($item,"Fetch Object Without Params");
$item = $pdo->FetchClass($sql,Category::class,);
dump($item,"Fetch Class Without Params");

$sql="SELECT * from category where 1 limit 2";
$item = $pdo->FetchAllObject($sql);
dump($item,"Fetch All Object Without Params");
$item = $pdo->FetchAllClass($sql,Category::class,);
dump($item,"Fetch All Class Without Params");

$sql="SELECT * from category where 1 and Id >:Id limit 3";
$item = $pdo->FetchAllObject($sql,["Id"=>2]);
dump($item,"Fetch All Object With Params");
$item = $pdo->FetchAllClass($sql,Category::class,["Id"=>2]);
dump($item,"Fetch Class All With Params");

$pdo->Delete("category",["Id" => 5]);
echo "Deleted ID 5".PHP_EOL;
