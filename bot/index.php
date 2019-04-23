<?php
use CharlotteDunois\Yasmin\Client;
use React\EventLoop\Factory;

require __DIR__ . '/../config/bootstrap.php';

$loop = Factory::create();
$client = new Client([], $loop);

$client->on('ready', function () use ($client) {
    echo 'Successfully logged into ' . $client->user->tag.PHP_EOL;
});

$client->login($_SERVER['DISCORD_BOT_TOKEN']);
$loop->run();
