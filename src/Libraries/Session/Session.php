<?php namespace TT\Libraries\Session;

/**
 * @package    TT
 * @author  Samir Rustamov <rustemovv96@gmail.com>
 * @link https://github.com/SamirRustamov/TT
 * @subpackage    Library
 * @category    Session
 */


use TT\Libraries\Session\Drivers\SessionFileDriver;


class Session
{

    const ENC_KEY = "1xo86bFafRcUx8IccN6mdFflstIkcmJiY+li7Qi7hWScfJS2StKBmwnff4378";

    protected static $config;


    public function __construct()
    {
        if(is_null(static::$config))
        {
            $base_dir = dirname(__DIR__,2);

            static::$config = require_once $base_dir.'/configs/session.php';

            ini_set('session.cookie_httponly', static::$config['cookie']['http_only']);
            ini_set('session.use_only_cookies', static::$config['only_cookies']);
            ini_set('session.save_path',static::$config['file_location']);
            ini_set('session.gc_maxlifetime', static::$config['lifetime']);

            session_set_cookie_params(
              static::$config['lifetime'] ,
              static::$config['cookie']['path'],
              static::$config['cookie']['domain'],
              static::$config['cookie']['secure'],
              static::$config['cookie']['http_only']
            );

            if(!empty(trim(static::$config['cookie']['name']))) {
                session_name(static::$config['cookie']['name']);
            }

            if(strtolower(static::$config['driver']) == 'file')
            {
               session_set_save_handler(new SessionFileDriver(),true);
               register_shutdown_function('session_write_close');
            }


            if (session_status() == PHP_SESSION_NONE)
            {
                @session_start();
                $this->set('session_hash', $this->hash());
            }
            else
            {
                if($this->get('session_hash') != $this->hash())
                {
                    $this->destroy();
                }
            }
        }
    }


    /**
     * @param $key
     * @param $value
     * @param bool $encode
     * @return mixed
     */
    public function set($key, $value)
    {
        if (is_callable($value))
        {
            return $this->set($key, call_user_func($value, $this));
        }
        else
        {
            $_SESSION[$key] = $value;
            @session_regenerate_id(session_id());
        }
    }


    /**
     * @return string
     */
    private function hash():String
    {
        return sha1($_SERVER['REMOTE_ADDR'] ?? ''.self::ENC_KEY.($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }


    /**
     * @param $key
     * @param bool $encode
     * @return bool
     */

    public function get($key)
    {
        if (is_callable($key))
        {
            return $this->get(call_user_func($key, $this));
        }
        else
        {
            return $_SESSION[ $key ] ?? false;
        }
    }


    /**
     * @param array $data
     */
    public function setArray( Array $data )
    {
        foreach ($data as $key => $value)
        {
            $this->set($key, $value);
        }
    }


    /**
     * @return array
     */
    public function all():array
    {
        return $_SESSION;
    }



    /**
     * @param $key
     * @return Bool
     */
    public function has($key):Bool
    {
        return isset($_SESSION[ $key ]);
    }


    /**
     * @param $key
     */
    public function delete($key)
    {
        if (is_callable($key))
        {
            $this->delete(call_user_func($key, $this));
        }
        else
        {
            if (is_array($key))
            {
                foreach ($key as  $value)
                {
                    $this->delete($value);
                }
            }
            else
            {
                if (isset($_SESSION[ $key ]))
                {
                    unset($_SESSION[ $key ]);
                }
            }
        }
    }



    public function path($path = null)
    {
      $cookie_params = session_get_cookie_params();

      if(is_null($path))
      {
        return $cookie_params['path'];
      }

      session_set_cookie_params($cookie_params['lifetime'],$path);

      return $this;
    }


    public function domain($domain = null)
    {
      $cookie_params = session_get_cookie_params();

      if(is_null($domain))
      {
        return $cookie_params['domain'];
      }

      session_set_cookie_params($cookie_params['lifetime'],$cookie_params['path'],$domain);

      return $this;
    }



    public function __get($key)
    {
      return $this->get($key);
    }


    public function __set($key,$value)
    {
      return $this->set($key,$value);
    }


    public function __isset($key)
    {
      return $this->has($key);
    }


    public function __call($method,$args)
    {
      $value = $args[0] ?? null;
      return is_null($value)
             ? $this->get($method)
             : $this->set($method,$value);
    }


    public static function __callStatic($method,$args)
    {
      return (new static)->__call($method,$args);
    }



    public function destroy()
    {
        $_SESSION = [];
        session_destroy();
    }


}
