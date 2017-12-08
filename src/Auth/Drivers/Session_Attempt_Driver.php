<?php namespace TT\Auth\Drivers;


use TT\Auth\Drivers\DriverInterface;
use TT\Libraries\Session\Session as Auth_Driver_Session;



class Session_Attempt_Driver implements DriverInterface
{

  private $session;


  function __construct()
  {
    $this->session = new Auth_Driver_Session();
  }


  public function getAttemptsCountOrFail($guard)
  {
    if($count = $this->session->get("AUTH_ATTEMP_COUNT_{$guard}"))
    {
      return (object) array('count' => $count);
    }
    return false;
  }

  public function addAttempt($guard)
  {
    if($this->getAttemptsCountOrFail($guard))
    {
      $this->session->set("AUTH_ATTEMP_COUNT_{$guard}",function($session) use ($guard){
        return $session->get("AUTH_ATTEMP_COUNT_{$guard}")+1;
      });
    }
    else
    {
      $this->session->set("AUTH_ATTEMP_COUNT_{$guard}",1);
    }

  }



  public function startLockTime($guard,$lock_time)
  {
    $this->session->set("AUTH_ATTEMP_EXPIRE_{$guard}", strtotime("+ {$lock_time} seconds"));
  }


  public function deleteAttempt($guard)
  {
    $this->session->delete(array("AUTH_ATTEMP_COUNT_{$guard}","AUTH_ATTEMP_EXPIRE_{$guard}"));
  }



  public function expireDateOrFail($guard)
  {
    return $this->session->get("AUTH_ATTEMP_EXPIRE_{$guard}");
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




}
