<?php

namespace Home\Controller;
use Think\Controller;
class YuefaController extends Controller {
	//首页/美发照片展示
		public function index(){
			$hlist = M('hairs','yf_','DB_CONFIG1')->order('likes desc')->select();

			$this->hlist = $hlist;
			$this->display();
			// dump($hlist);
			// echo "hello weiphp";
		}

	//加载更多
		public function getmore(){


		}

	//理发师详情页
		public function barber(){
			$bid = I('get.bid');
			// dump($bid);
			$bmap['id'] = $bid;
			$hmap['belong'] = $bid;
			$barber = M('barber','yf_','DB_CONFIG1')->where($bmap)->select();
			$hlist = M('hairs','yf_','DB_CONFIG1')->where($hmap)->select();
			$this->barber = $barber;
			$this->hlist = $hlist;
			// dump($barber);
			$this->display();
		}	

}

