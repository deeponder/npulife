<?php
namespace Home\Controller;
use Think\Controller;
class ElecreditController extends Controller 
{
public function elecredit()
{
$name = I('get.name');
$this->name = $name;
$this->display();
}
}
?>