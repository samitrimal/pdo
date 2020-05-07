<?php  declare(strict_types=1);

namespace Samitrimal\Pdo;

class Db extends \PDO
{

    public function __construct(array $config)
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8',$config['host'],$config['dbname']);
        try
        {
            parent::__construct($dsn,$config['username'],$config['password']);
            $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        return $this;
        }
        catch(\PDOException $e)
        {
            throw new \Exception($e->getMessage());
        }

    }
    public function Execute(string $sql,array $params=array())
    { 
        $statement = parent::prepare($sql);
        if(count($params)>0)
        {
            $statement->execute($params);  
        }
        else
        {
            $statement->execute();
        }
        return $statement;
    }

    public function Insert(string $table,array $data):int
    {
       
        $columns =$this->GetColumnString($data);
        $values = $this->GetInsertValueString($data);
        $sql =sprintf("INSERT INTO %s (%s) VALUES (%s)",$table,$columns,$values); 
        $this->Execute($sql,$data);
        $id = intval($this->lastInsertId());
        
        return $id;
    }
    public function Update(string $tableName,array $data,array $where=[])
    {
        $sql = "Update %s SET %s WHERE %s";
        $whereString = "1";
        $dataString = $this->GetSqlString($this->PrepareKeyValue($data));
        if(count($where)>0)
        {
            $whereKpv =$this->PrepareKeyValue($where);
            $data = array_merge($data,$where);
             $whereString.= " AND ".$this->GetSqlString($whereKpv);
        }
        $sql =sprintf($sql,$tableName,$dataString,$whereString); 
        
       return $this->Execute($sql,$data);
    }
    public function Delete(string $tableName,array $where = null)
    {
        
        $sql = "DELETE FROM %s WHERE %s";
        $whereString = "1";
        
        if(count($where)>0)
        {
            $whereKpv =$this->PrepareKeyValue($where);
            $whereString.= " AND ".$this->GetSqlString($whereKpv);
        }
        $sql =sprintf($sql,$tableName,$whereString); 
        
       return $this->Execute($sql,$where);
    }

    public function FetchClass(string $sql,string $class,array $params=[])
    {
        $entity = new $class();
        $result = $this->Execute($sql,$params);
        $result->setFetchMode(\PDO::FETCH_INTO, $entity);
        $result =  $result->fetch();
        if($result == false) {$result = $entity; }
        return $result;
    }
    
    public function FetchObject(string $sql,array $params=[])
    {
        $result = $this->Execute($sql,$params);
        return $result->fetch(\PDO::FETCH_OBJ);
    }
    public function FetchAllObject(string $sql, array $params =[])
    {   
        $result = $this->Execute($sql,$params);
        return $result->fetchAll(\PDO::FETCH_OBJ);
    }

    public function FetchAllClass(string $sql,$class,array $params=[])
    {
        $result = $this->Execute($sql,$params);
        return $result->fetchAll(\PDO::FETCH_CLASS,$class);
        
    }
    private  function GetSqlString(array $data,$separator=",")
    {
        return implode($separator,$data);
    }
    private function PrepareKeyValue(array $data){
        $pair = [];
       $keys = $this->QuoteKeys($data);
       $values = $this->PrepareValues($data);
        $combinedData = array_combine($keys,$values);
        foreach($combinedData as $key=>$value)
        {
            $pair[] = $key."=".$value;
        }
       return $pair;
    }
    private function GetColumnString(array $data)
    {
        $columns = $this->QuoteKeys($data);
        return $this->GetSqlString($columns);
    }
    private function GetInsertValueString(array $data)
    {
        $values = $this->PrepareValues($data);
        return $this->GetSqlString($values);
    }
    private function PrepareValues(array $data)
    {
        $values = array_keys($data);
        return array_map(function($val){
            return ":".$val;
        },$values);

    }
    public function QuoteKeys(array $data) : array
    {
        $columns = array_keys($data);
        return array_map(function($val){
            return "`".$val."`";
        },$columns);	
        
        
    }

    
    public function DropAllTables(){
        $stmt = $this->Execute("show tables");
        $tables =  $stmt->fetchAll();
        $this->Execute("SET FOREIGN_KEY_CHECKS = 0");
        foreach($tables as $table){
          //  var_dump($table);die;
            $this->Execute("drop table ". $table[0]);
        }
        $this->db->Execute("SET FOREIGN_KEY_CHECKS = 1");
      }
      public function TruncateAllTables(){
          $stmt = $this->Execute("show tables");
          $tables =  $stmt->fetchAll();
          $this->Execute("SET FOREIGN_KEY_CHECKS = 0");
          foreach($tables as $table){
              $this->TruncateTable($table);
          }
          $this->Execute("SET FOREIGN_KEY_CHECKS = 1");
      }
      public function TruncateTable($table){
        $this->Exec("SET FOREIGN_KEY_CHECKS = 0");
        $this->Exec("TRUNCATE TABLE ". $table);
        $this->Exec("SET FOREIGN_KEY_CHECKS = 1");
    }
    
}