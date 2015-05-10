<?php

namespace Home\Controller;
use Think\Controller;

class UploadController extends Controller {
	
  public function login(){

    // $user = I('post.user');
    // $pw = I('post.pw');
    // $member['user'] = 'peng';
    // $member['pw'] = 123;
    // session($member);
    session('username','hello');
    $name = 'nn';
      $this->check();

  }

  public function check(){
    dump(session('username'));
    dump($name);
  }

	}

?>