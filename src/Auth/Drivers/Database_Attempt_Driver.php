<?php namespace TT\Auth\Drivers;


use TT\Auth\Drivers\DriverInterface;
use TT\Libraries\Database\Database as Auth_Driver_DB;



class Database_Attempt_Driver implements DriverInterface
{


  private $db;


  function __construct()
  {
    $this->db = new Auth_Driver_DB();
  }


  public function getAttemptsCountOrFail($guard)
  {
      return $this->db->table('attempts')->where('ip',$this->ip())->where('guard',$guard)->first();
  }

  public function addAttempt($guard)
  {
    if($this->getAttemptsCountOrFail($guard))
    {
      $this->db->pdo()->query("UPDATE attempts SET count = count+1 WHERE ip ='{$this->ip()}' AND guard='{$guard}'");
    }
    else
    {
      $this->db->pdo()->query("INSERT INTO attempts SET ip = '{$this->ip()}',count=1,guard='{$guard}'");
    }
  }


  public function startLockTime($guard,$lock_time)
  {
    $time = strtotime("+ {$lock_time} seconds");

    $this->db->pdo()->query("UPDATE attempts SET expiredate = '{$time}' WHERE ip ='{$this->ip()}' AND guard='{$guard}'");
  }


  public function deleteAttempt($guard)
  {
    $this->db->pdo()->query("DELETE FROM attempts WHERE ip ='{$this->ip()}' AND guard='{$guard}'");
  }



  public function expireDateOrFail($guard)
  {
    $result = $this->db->pdo()->query("SELECT expiredate FROM attempts WHERE ip='{$this->ip()}' AND guard='{$guard}'");
    if ($result->rowCount() > 0)
    {
      return $result->fetch()->expiredate;
    }

    return false;
  }


  public function getRemainingSecondsOrFail($guard)
  {
    if($expiredate = $this->expireDateOrFail($guard))
    {
        $remaining_seconds = $expiredate - time();

        if($remaining_seconds > 0)
        {
            return $remaining_seconds;
        }
    }

    $this->deleteAttempt($guard);

    return false;
  }


  private function ip (): String
  {
      if (!empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ))
      {
          $ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
      }
      elseif (!empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ))
      {
          $ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
      }
      else
      {
          $ip = $_SERVER[ 'REMOTE_ADDR' ];
      }

      return $ip;
  }



}
