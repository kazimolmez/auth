<?php namespace TT\Auth\Drivers;




interface DriverInterface
{
  public function getAttemptsCountOrFail($guard);

  public function addattempt($guard);

  public function startLockTime($guard,$lock_time);

  public function deleteAttempt($guard);

  public function expireDateOrFail($guard);

  public function getRemainingSecondsOrFail($guard);
}
