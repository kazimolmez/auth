<?php namespace TT\Auth;


/**
* Php Authentication Library
*
* @author Samir Rustamov <rustemovv96@gmail.com>
* @version 	1
* @copyright	2017
* @link https://github.com/SamirRustamov/Auth
*/


use TT\Auth\Drivers\Session_Attempt_Driver;
use TT\Auth\Drivers\Database_Attempt_Driver;
use TT\Libraries\Session\Session as Auth_Session;
use TT\Libraries\Cookie as Auth_Cookie;
use TT\Libraries\Database\Database as Auth_DB;





class Authentication
{

  private static $config;


  private static $message;


  private static $guard  = 'user';


  private $driver;


  private $table;


  private $lock_time;


  private $max_attempts;


  private $hidden;


  private $cookie;


  private $session;




  function __construct()
  {
    if(is_null(self::$config))
    {
      $base_dir = dirname(__DIR__);

      if(file_exists($base_dir.'/configs/authentication.php'))
      {
        static::$config = require_once $base_dir.'/configs/authentication.php';
      }
      else
      {
          throw new \Exception("Config file [".$base_dir.'/configs/authentication.php'."] not found");
      }

    }

    $this->cookie  = new Auth_Cookie();
    $this->session = new Auth_Session();

  }



  protected function beforeLogin($guard,$login_user_data)
  {
    //
  }



  public function guard($guard)
  {
      static::$guard = $guard;
      return $this;
  }



  public  function getGuard()
  {
    return static::$guard;
  }




  public  function attempt($data, $remember = false)
  {

    $this->setConfigItems();

    if($attempts = $this->driver[static::$guard]->getAttemptsCountOrFail(static::$guard)) {
      if($attempts->count >= $this->max_attempts[static::$guard]) {
        if($seconds =  $this->driver[static::$guard]->getRemainingSecondsOrFail(static::$guard)) {
            static::$message = "You have been temporarily locked out! Please wait {$this->convertTime($seconds)}";
            return false;
        }
      }
    }

    $password = $data['password']; unset($data['password']);

    if($result = (new Auth_DB())->table($this->table[static::$guard])->where($data)->first()) {
      if(password_verify($password,$result->password))
      {
          $this->driver[static::$guard]->deleteAttempt(static::$guard);

          if($remember)
          {
              $this->setRemember($result);
          }

          $this->beforeLogin(static::$guard, $result);

          $this->setSession($result);

          return true;
      }

    }

    $this->driver[static::$guard]->addAttempt(static::$guard);

    $remaining =  $this->max_attempts[static::$guard] - $this->driver[static::$guard]->getAttemptsCountOrFail(static::$guard)->count;
    if($remaining == 0)
    {
      $this->driver[static::$guard]->startLockTime(static::$guard,$this->lock_time[static::$guard]);
    }
    static::$message = "Login or password incorrect! ".sprintf("%d attempts remaining !",$remaining);

    return false;


  }


  public function register($user)
  {
    if(is_array($user) || is_object($user))
    {
      if(is_object($user))
      {
        $user = (array) $user;
      }

      $required_fields = array('name','email','password');

      foreach($required_fields as $field)
      {
        if(array_key_exists($field,$user) && !empty(trim($user[$field])))
        {
          continue;
        }
        static::$message = "{$field} is required";

        return false;
      }

      $unique_check = (new Auth_DB())->table($this->table[static::$guard])->where('email',$user['email']);

      if(!$unique_check->first())
      {
        $hash_password = password_hash($user['password'],PASSWORD_BCRYPT,['cost' => 10]);

        if ($hash_password === false)
        {
          throw new \RuntimeException('Bcrypt hashing not supported.');
        }
        else
        {
          $user['password'] = $hash_password;
        }

        return (new Auth_DB())->table($this->table[static::$guard])->set($user)->insert();
      }
      else
      {
          static::$message = "Email is already in use !";
      }
      return false;
    }
    else
    {
      return false;
    }
  }




  public function login($user,$remember = false)
  {
    if(is_array($user) || is_object($user))
    {
      try
      {
        $this->setSession($user);

        if($remember)
        {
          $this->setRemember($user);
        }
        return true;
      }
      catch(\Exception $e)
      {
        return false;
      }

    }
    return false;
  }




  public function check()
  {
      if($this->session->get(static::$guard.'_login') === true)
      {
          return true;
      }
      else
      {
        if($result = $this->remember(static::$guard))
        {
            $this->beforeLogin(static::$guard, $result);

            $this->setSession($result);

            return true;
        }
        return false;
      }

  }



  public function guest()
  {
      return !$this->check();
  }


  public function remember($guard)
  {
    if($_token = $this->cookie->get('remember_'.$guard))
    {
        return (new Auth_DB())->table($this->table[static::$guard])->where('remember_token',base64_decode($_token))->first();
    }
    return false;
  }


  public function setRemember($user)
  {
    if($_token = $user->remember_token)
    {
      $this->cookie->set('remember_' . static::$guard, base64_encode($_token), 3600 * 24 * 30);
    }
    else
    {
      $_token = hash_hmac('sha256',$user->email . $user->name,self::ENC_KEY);
      $this->cookie->set('remember_'.static::$guard, base64_encode($_token), 3600 * 24 * 30);
      (new Auth_DB())->table($this->table[static::$guard])->set(['remember_token' => $_token])->where('id',$user->id)->update();
    }
  }



  private function setSession($guard_data)
  {
    $guard_data = (array) $guard_data;

    foreach ($this->hidden[static::$guard] as $key) {
        unset($guard_data[$key]);
    }

    foreach ($guard_data as $key => $value) {
      $guard_data[static::$guard . '_'.$key] = $value;
    }
    $guard_data[ static::$guard . '_login' ] = true;

    $this->session->setArray($guard_data);
  }




  public  function logout()
  {
    try
    {
      $this->session->delete(function ($session){
          $subject = array_keys($session->all());
          $data    = array();
          foreach ($subject as $key => $value)
          {
            if (preg_match("/".static::$guard."_(.*)/", $value))
            {
              array_push($data, $value);
            }
          }
          return $data;
      });

      if($this->cookie->has('remember_'.static::$guard))
      {
        $this->cookie->forget('remember_'.static::$guard);
      }

      return $this->guest();
    }
    catch(\Exception $e)
    {
      static::$message = $e->getMessage();
      return false;
    }
  }



  private function convertTime($seconds)
  {
    $minute = ""; $second = "";
    if($seconds >= 60) {
      $minute = (int) ($seconds/60);
      if ($minute > 1) {
        $minute = $minute." minutes ";
      } else {
        $minute = $minute." minute ";
      }
      if($seconds%60 > 0) {
        $second = ($seconds%60);
        if ($second > 1) {
          $second = $second." seconds ";
        } else {
          $second = $second." second ";
        }
      }
    }
    else {
      $second = $seconds > 1 ? $seconds." seconds " : $seconds." second ";
    }

    return $minute.$second;
  }


  private function setConfigItems()
  {
    foreach (static::$config['guards'] as $guard => $config) {
      $this->max_attempts[$guard] = $config['max_attempts'];
      $this->lock_time[$guard]    = $config['lock_time'];
      $this->table[$guard]        = $config['table'];
      $this->hidden[$guard]       = $config['hidden'];

      switch (strtolower($config['attempts_driver']))
      {
        case 'session':
          $this->driver[$guard] = new Session_Attempt_Driver();
          break;
        case 'database':
          $this->driver[$guard] = new Database_Attempt_Driver();
          break;
        default:
          $this->driver[$guard] = new Session_Attempt_Driver();
          break;
      }
    }
  }



  public function getMessage()
  {
    return static::$message;
  }



  public function __get($key)
  {
    return $this->session->get(static::$guard.'_'.$key);
  }

  public function __set($key,$value)
  {
    $this->session->set($key,static::$guard.'_'.$value);
  }



  public static function __callStatic($method, $args)
  {
      return ( new static )->__call($method, $args);
  }




  public function __call($method, $args)
  {

  }



}
