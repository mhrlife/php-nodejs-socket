# PHP NodeJS Socket

this project use https://github.com/bitkill/php-socketio-broadcast as socket.io.php

## how does it work

fisrt we create a websocket with nodeJS websocket . users will connect to nodejs websocket .

for making notification , php sends some data to nodejs then nodejs handle this datas to connected users .

this project require nodejs Websocket :

```npm install websocket```

### Files :

_ jq.js : JQuery 

_ send.php : PHP file to notificate   

_ socket.io.php : socketIO Class

___ NodeJS

_______ client.html : html file that contains websocket connection . you can run this file in browser :)

_______ test.js : NodeJS server file


you can use send.php?msg=MESSAGE HERE to send notifications .

