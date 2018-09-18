<?php
include('./config.inc.php');

class DataBase
{
  private static $instance = null;
  
  private $dbHost = DBHOST;
  private $dbName = DBNAME;
  private $user = DBUSER;
  private $pass = DBPASS;
  
  private $sQuery; 
  private $pdo; 
  private $bConnected = false;
  
  public function __construct()
  {
    $this->Connect($this->dbHost, $this->dbName, $this->user, $this->pass);
  }
  
  public function Connect($dbHost, $dbName, $user, $pass)
  {
    global $settings;
    $dsn = 'mysql:dbname='.$dbName.';host='.$dbHost;
    try 
    {
      $this->pdo = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		
		  $this->bConnected = true;
    }
    catch (PDOException $e)
    {
      echo $this->ExceptionLog($e->getMessage());
      die();
    }
  }
  
  public function CloseConnection()
  {
    $this->pdo = null;
  }
  
  public function Init($query)
  {
    if(!$this->bConnected) { $this->Connect();}
    try
    {
      $this->sQuery = $this->pdo->prepare($query); 
      $this->success = $this->sQuery->execute();
    }
    catch (PDOExepction $e)
    {
      $this->ExceptionLog($e->getMessage(), $query);
    }
  }
  
  public function query($query, $fetchmode = PDO::FETCH_ASSOC)
  {
    $query = trim($query);
    $this->Init($query);
    $rawStatement = explode(" ", $query);
    
    $statement = strtolower($rawStatement[0]);
    if ($statement === 'select' || $statement === 'show') {
      return $this->sQuery->fetchAll($fetchmode);
    }
    elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
      return $this->sQuery->rowCount();	
    }	
    else {
      return NULL;
    }
  }
  
  public function lastInsertId() {
    return $this->pdo->lastInsertId();
  }
  
  private function ExceptionLog($message , $sql = "")
	{
		$exception  = 'Unhandled Exception. <br />';
		$exception .= $message;
		$exception .= "<br /> You can find the error back in the log.";
		
    if(!empty($sql)) {
			$message .= "\r\nRaw SQL : "  . $sql;
		}
    
		throw new Exception($message);
		#return $exception;
	}			
}