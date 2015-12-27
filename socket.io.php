<?php
class SocketIO
{
    protected
        $host = null,
        $port = null,
        $address = null,
        $transport = null,
        $socket = null;

    const TIMEOUT_SOCKET = 5;

    public function __construct($host = '127.0.0.1', $port = 8080, $address = "/socket.io/?EIO=2", $transport = 'websocket') {
        $this->host = $host;
        $this->port = $port;
        $this->address = $address;
        $this->transport = $transport;
    }

    private function connect() {
        $errno = '';
        $errstr = '';
        $this->socket = stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, self::TIMEOUT_SOCKET);
        return $this->handshake();
    }

    private function handshake() {
        $key = $this->generateKey();
        $out = "GET $this->address&transport=$this->transport HTTP/1.1\r\n";
        $out.= "Host: http://$this->host:$this->port\r\n";
        $out.= "Upgrade: WebSocket\r\n";
        $out.= "Connection: Upgrade\r\n";
        $out.= "Sec-WebSocket-Key: $key\r\n";
        $out.= "Sec-WebSocket-Version: 13\r\n";
        $out.= "Origin: *\r\n\r\n";

        if (!fwrite($this->socket, $out)) {
            // fclose($this->socket);
            $this->socket = null;
            return false;
        }
        // 101 switching protocols, see if echoes key
        $result= fread($this->socket,1000);
        //var_dump($result);

        preg_match('#Sec-WebSocket-Accept:\s(.*)$#mU', $result, $matches);
        $keyAccept = trim($matches[1]);
        $expectedResonse = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        return ($keyAccept === $expectedResonse) ? true : false;
    }
    
    public function send($action= "message",  $data = null)
    {
        if ($this->connect()) {
            fwrite($this->socket, $this->hybi10Encode($data));
            fread($this->socket, 10);
            fclose($this->socket);
            return true;
        } else {return false;}
    }
    private function generateKey($length = 16)
    {
        $c = 0;
        $tmp = '';
        while ($c++ * 16 < $length) { $tmp .= md5(mt_rand(), true); }
        return base64_encode(substr($tmp, 0, $length));
    }
    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $payloadLength = strlen($payload);
        switch ($type) {
            case 'text':
                $frameHead[0] = 129;
                break;
            case 'close':
                $frameHead[0] = 136;
                break;
            case 'ping':
                $frameHead[0] = 137;
                break;
            case 'pong':
                $frameHead[0] = 138;
                break;
        }
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            if ($frameHead[2] > 127) {
                $this->close(1004);
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }
}