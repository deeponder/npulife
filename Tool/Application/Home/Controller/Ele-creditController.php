<?php
namespace Home\Controller;
use Think\Controller;
class Ele_creditController extends Controller 
{
public ele_credit()
{
$name = I('get.name');
$this->name = $name;
$this->display();
}
}