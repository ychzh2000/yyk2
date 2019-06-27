<?php
class testGet extends yyk\Rest
{
  public function exec($req, $get)
  {
    $r['code'] = 200;
    $r['data'] = 'ok';
    $this->export($r);
  }
}