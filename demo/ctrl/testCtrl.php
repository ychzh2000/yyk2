<?php
class testCtrl extends yyk\Ctrl
{
  public function test()
  {
    // echo 'ctrl';
    $this->assign('test', 'test mvc');
    $this->display();
  }
}