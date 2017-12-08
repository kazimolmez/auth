<?php


/**
 * @author  Samir Rustamov <rustemovv96@gmail.com>
 * @link 	https://github.com/SamirRustamov/Auth
 */



function auth( $guard = null)
{
    if(class_exists('TT\Auth\Authentication'))
    {
        return is_null($guard)
               ? (new TT\Auth\Authentication())
               : (new TT\Auth\Authentication())->guard($guard);
    }
    else
    {
        throw new Exception('TT\Auth\Authentication class Not Found');
    }
    

}


function isAuthentication($guard = null)
{
  return auth($guard)->check();
}



function post($name)
{
  if($_SERVER['REQUEST_METHOD'] == 'POST')
  {
    if(isset($_POST[$name]))
    {
      if(empty(trim($_POST[$name])))
      {
        return false;
      }
      return trim($_POST[$name]);
    }
  }
  return false;
}