<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Servidor\Chat;
use Servidor\Cliente;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/cliente.php';
require_once __DIR__ . '/chat.php';

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new Chat()
		)
	),
	8080
);

$server->run();
	
?>