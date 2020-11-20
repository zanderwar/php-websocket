<?php

require __DIR__ . '/../src/Connection.php';
require __DIR__ . '/../src/Socket.php';
require __DIR__ . '/../src/Server.php';
require __DIR__ . '/../src/Timer.php';
require __DIR__ . '/../src/TimerCollection.php';

require __DIR__ . '/../src/Application/ApplicationInterface.php';
require __DIR__ . '/../src/Application/Application.php';
require __DIR__ . '/../src/Application/DemoApplication.php';
require __DIR__ . '/../src/Application/StatusApplication.php';

$server = new \Bloatless\WebSocket\Server('127.0.0.1', 8000);
//$server->setStreamContext([
//	'ssl' => [
//		'local_cert' => "/path/to/cert",
//		'verify_peer_name' => false,
//		'verify_peer' => false,
//		'cipher' => 'ALL'
//	]
//]);
$server->createSocket();

// server settings:
$server->setMaxClients(100);
$server->setCheckOrigin(false);
$server->setAllowedOrigin('foo.lh');
$server->setMaxConnectionsPerIp(100);
$server->setMaxRequestsPerMinute(2000);

// Hint: Status application should not be removed as it displays usefull server informations:
$server->registerApplication('status', \Bloatless\WebSocket\Application\StatusApplication::getInstance());
$server->registerApplication('demo', \Bloatless\WebSocket\Application\DemoApplication::getInstance());

$server->run();
