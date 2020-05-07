```$config = [
  'dsn'=>'mysql',
  'host'=>'localhost',
  'dbname'=>'wp34',
  'username' =>'root',
  'password' =>''
];
$pdo = new \Samitrimal\Pdo\Db($config);
//insert
$pdo->Execute("insert into wp_users(user_login,user_pass) VALUES(:user_login,:user_pass)",array(':user_login'=>'samitrimal',':user_pass'=>'ddskdslkdsklds'));
print $pdo->lastInsertId();
update
$statement = $pdo->Execute('select * from wp_users')->fetchAll();
$pdo->dump($statement);```