<?php

namespace Addons\LikeCollect;
use Common\Controller\Addon;

/**
 * 集赞插件
 * @author huang
 */

    class LikeCollectAddon extends Addon{

        public $info = array(
            'name'=>'LikeCollect',
            'title'=>'集赞',
            'description'=>'集赞',
            'status'=>1,
            'author'=>'huang',
            'version'=>'0.1',
            'has_adminlist'=>1,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/LikeCollect/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/LikeCollect/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }