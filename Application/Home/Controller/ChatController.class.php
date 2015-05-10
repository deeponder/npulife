<?php
namespace Home\Controller;
use Think\Controller;
class ChatController extends Controller
{

	public function main(){

		$webSocketServer = new Common\Api\WebSocketApi();
		//$webSocketServer = Api('WebSocket');
		 
		$onMessage = function ($clientID, $message, $messageLength, $binary) use ($webSocketServer) {
		
			$ip = long2ip( $webSocketServer->wsClients[$clientID][6] );
		
			// check if message length is 0
			if ($messageLength == 0) {
				$webSocketServer->wsClose($clientID);
				return;
			}
		
			//The speaker is the only person in the room. Don't let them feel lonely.
			if ( sizeof($webSocketServer->wsClients) == 1 )
				$webSocketServer->wsSend($clientID, "无其他用户");
			else
			//Send the message to everyone but the person who said it
			foreach ( $webSocketServer->wsClients as $id => $client )
			if ( $id != $clientID )
				$webSocketServer->wsSend($id, "用户 $clientID ($ip) 说 \"$message\"");
		
			 
		};
		$onOpen = function ($clientID) use ($webSocketServer) {
		
		
			$ip = long2ip( $webSocketServer->wsClients[$clientID][6] );
		
			$webSocketServer->log( "$ip ($clientID) 已连接。" );
		
			//Send a join notice to everyone but the person who joined
			foreach ( $webSocketServer->wsClients as $id => $client )
			if ( $id != $clientID )
				$webSocketServer->wsSend($id, "用户 $clientID ($ip) 加入房间。");
		
		};
		$onClose = function ($clientID, $status) use ($webSocketServer) {
		
			$ip = long2ip( $webSocketServer->wsClients[$clientID][6] );
		
			$webSocketServer->log( "$ip ($clientID) 已断开。" );
		
			//Send a user left notice to everyone in the room
			foreach ( $webSocketServer->wsClients as $id => $client )
				$webSocketServer->wsSend($id, "用户 $clientID ($ip) 离开了房间。");
		};
		$webSocketServer -> bind('message', $onMessage);
		$webSocketServer -> bind('open', $onOpen);
		$webSocketServer -> bind('close', $onClose);
		$serverStatus = $webSocketServer -> wsStartServer('0.0.0.0', 8080);
		if($serverStatus == false){
			echo $webSocketServer -> error;
		}else{
			echo 'webSocketServer Normal end';
		}		
	}

	public function index(){

		$this->display();
	}

}
?>