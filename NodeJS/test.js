var WebSocketInit = require("websocket").server;
var http = require("http");
var server = http.createServer(function(request, response) {
});
server.listen(1337);

var wSocket = new WebSocketInit({
    httpServer:server
});

var connections = [];




wSocket.on('request',function(request){


  var connection = request.accept(null , request.origin);
  var index = connections.push(connection) -1;
  connection.on("message",function(message){
   var given = message.utf8Data;
   try{


     var givenData = JSON.parse(given);

    if(givenData.key = "@pp2007ws:)"){

     var phpMessage = givenData.message;

     console.log("PHP NOT.");

     for(var i=0;i<connections.length;i++){
      connections[i].sendUTF("New Notification : "+phpMessage);
     }
    }


   }catch (e){

    console.log("ERR:"+e.toString());

   }

   console.log(message);

   console.log("on message "+given);

  });

  console.log("\n"+connections.toString()+"\n");

  connection.on("close",function(connection){
    connections.splice(index,1);
   console.log("index "+index+" closed");
  });

});

