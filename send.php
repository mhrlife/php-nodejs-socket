<?php

require "socket.io.php";
$message = isset($_GET['msg']) ? $_GET['msg'] : "Hora ! PHP S4A ! :)";
$send2 = [
    "message"=>$message,
    "key"=>"@pp2007ws:)"
];
$socketio = new SocketIO('127.0.0.1', 1337);
if ($socketio->send('message',json_encode($send2)) ){
    echo json_encode($send2);
} else {
    echo 'Sorry, we have a mistake :\'(';
}