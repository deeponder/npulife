<?php

namespace Addons\SendSchoolBusTicket;
use Common\Controller\Addon;

/**
 * 校车提醒上车插件
 * @author 无名
 */

    class SendSchoolBusTicketAddon extends Addon{

        public $info = array(
            'name'=>'SendSchoolBusTicket',
            'title'=>'校车提醒上车',
            'description'=>'这是一个临时描述',
            'status'=>1,
            'author'=>'无名',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/SendSchoolBusTicket/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/SendSchoolBusTicket/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }