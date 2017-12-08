<?php namespace TT\Libraries\Database;

/**
 * @package    TT
 * @author  Samir Rustamov <rustemovv96@gmail.com>
 * @link https://github.com/SamirRustamov/TT
 * @subpackage    Library
 * @category    Database
 */


use PDO;

use PDOException;

abstract class Connection
{

    protected static $general = [];

    protected static $config  = [];

    protected static $connect;

    protected $connection_group = 'default';



    function __construct()
    {
      $this->reconnect();
    }



    public function pdo ()
    {
        return self::$connect;
    }



    protected  function reconnect()
    {
      if (!isset(self::$general[$this->connection_group]))
      {
          $base_dir = dirname(__DIR__,2);

          if(file_exists($base_dir.'/configs/database.php'))
          {
              $config_data = require_once $base_dir.'/configs/database.php';
          }
          else
          {
              throw new \Exception("Config file [".$base_dir.'/configs/database.php'."] not found");
          }

          static::$config[$this->connection_group] = $config_data[$this->connection_group];

          $config    = static::$config[$this->connection_group];
          try
          {
              $dsn = "host={$config[ 'hostname' ]};dbname={$config[ 'dbname' ]};charset={$config[ 'charset' ]}";
              static::$connect = new PDO("mysql:{$dsn}" ,$config[ 'username' ] ,$config[ 'password' ]);
              static::$connect->setAttribute (PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_OBJ );
              static::$connect->setAttribute (PDO::ATTR_ERRMODE ,PDO::ERRMODE_EXCEPTION );
              static::$connect->query ( "SET CHARACTER SET  " . $config[ 'charset' ] );
              static::$connect->query ( "SET NAMES " . $config[ 'charset' ] );
              static::$general[$this->connection_group] = self::$connect;
          }
          catch (PDOException $e)
          {
             throw new \Exception($e->getMessage());
          }
      }
      else
      {
        static::$connect = self::$general[$this->connection_group];
      }
    }




    public function connect ($connection_group = 'default')
    {
         $this->connection_group = $connection_group;
         $this->reconnect();
         return $this;
    }


    /**
    * Database connection close;
    */
    public function close ()
    {
        if(isset(self::$general[$this->connection_group]))
        {
            unset(self::$general[$this->connection_group]);
            $this->connection_group = 'default';
            $this->reconnect();
        }

    }



}
