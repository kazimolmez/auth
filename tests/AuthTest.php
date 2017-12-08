<?php




class AuthTest extends \PHPUnit_Framework_TestCase
{

    public function testAuthGuard()
    {
      $this->assertEquals((new TT\Auth\Authentication())->getGuard(),'user');

      (new TT\Auth\Authentication())->guard('admin');

      $this->assertEquals((new TT\Auth\Authentication())->getGuard(),'admin');
    }


    public function testLogout()
    {
        $this->assertTrue((new TT\Auth\Authentication())->logout());
    }


}