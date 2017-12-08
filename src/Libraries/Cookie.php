<?php namespace TT\Libraries;

/**
 * @package    TT
 * @author  Samir Rustamov <rustemovv96@gmail.com>
 * @link https://github.com/SamirRustamov/TT
 * @subpackage    Library
 * @category    Cookie
 */




class Cookie
{


    const ENC_KEY = "1xo86bFafRcUx8IccN6mdFflstIkcmJiY+li7Qi7hWScfJS2StKBmwnff4378";

    private static $config;

    private $prefix = '';

    private $http_only = true;

    private $secure = false;

    private $path = '/';

    private $domain = '';


    /**
     * Cookie constructor.
     */
    public function __construct()
    {
        if (is_null(static::$config))
        {

          $base_dir = dirname(__DIR__);

          static::$config = require_once $base_dir.'/configs/cookie.php';
        }

        $config = static::$config;
        $this->prefix    = !empty($config[ 'prefix' ])     ? $config[ 'prefix' ]    : $this->prefix;
        $this->http_only = is_bool($config[ 'http_only' ]) ? $config[ 'http_only' ] : $this->http_only;
        $this->secure    = is_bool($config[ 'secure' ])    ? $config[ 'secure' ]    : $this->secure;
        $this->path      = !empty($config[ 'path' ])       ? $config[ 'path' ]      : $this->path;
        $this->domain    = !empty($config[ 'domain' ])     ? $config[ 'domain' ]    : $this->domain;
    }


    /**
     * @param Bool $http
     * @return Cookie
     */
    public function http_only(Bool $http)
    {
        $this->http_only = $http;
        return $this;
    }


    /**
     * @param String $path
     * @return Cookie
     */
    public function path(String $path)
    {
        $this->path = $path;
        return $this;
    }


    /**
     * @param String $domain
     * @return $this
     */
    public function domain(String $domain)
    {
        $this->domain = $domain;
        return $this;
    }


    /**
     * @param Bool $bool
     */
    public function secure(Bool $secure)
    {
        $this->secure = $secure;
        return $this;
    }


    /**
     * Flush $_COOKIE variable
     */
    public function flush()
    {
        foreach (array_keys($_COOKIE) as $Cookie)
        {
            $this->forget($Cookie);
        }
    }


    /**
     * @param $Key
     */
    public function forget($key)
    {
        $this->set($key, '', -1);
    }


    /**
     * @param $key
     * @param $value
     * @param Int $time
     * @throws \Exception
     */
    public function set($key, $value, Int $time)
    {
        if (is_callable($value))
        {
            return $this->set($key, call_user_func($value, $this), $time);
        }

        if (!empty(trim($value)))
        {
            $value = $this->encrypt(json_encode($value));
        }

        $set = setcookie(
            $this->prefix . $key,
            $value,
            time()+$time,
            $this->path, $this->domain,
            $this->secure,
            $this->http_only
        );

        if (!$set)
        {
            throw new \Exception("Could not set the cookie!");
        }
    }



    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_COOKIE[ $this->prefix . $key ]);
    }

    /**
     * @param $Key
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
            if (isset($_COOKIE[ $this->prefix . $key ]))
            {
                return json_decode($this->decrypt($_COOKIE[ $this->prefix . $key ]));
            }
            return false;
        }
    }


    private function encrypt($data)
    {
      $encrypted_data = openssl_encrypt(
        $data, "AES-256-CBC", mb_substr(self::ENC_KEY,0,32),OPENSSL_RAW_DATA,mb_substr(self::ENC_KEY,0,16)
      );
      return base64_encode ( $encrypted_data );
    }


    private function decrypt($data)
    {
      $decrypted_data = openssl_decrypt(
        base64_decode($data), "AES-256-CBC", mb_substr(self::ENC_KEY,0,32),OPENSSL_RAW_DATA,mb_substr(self::ENC_KEY,0,16)
      );
      return $decrypted_data;
    }
}
