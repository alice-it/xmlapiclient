<?php

use SimpleXMLElement;

class httpRequest {
    var $host;
    var $port;

    //constructor
    function __construct($host, $port){
        $this->host = $host;
        $this->port = $port;
    }

    //uri to get
    function get($uri) {
        return $this->request('GET',$uri,'');
    }

    //uri to put body(xml)
    function put($uri,$body) {
        return $this->request('PUT',$uri,$body);
    }

    //uri to post body(xml)
    function post($uri,$body) {
        return $this->request('POST',$uri,$body);
    }

    //uri to delete
    function delete($uri) {
        return $this->request('DELETE',$uri,'');
    }

    // private methods
	//make request to server
    function request($method, $uri, $body){
        //open socket
        $sd = fsockopen($this->host, $this->port, $errno,$errstr);
        if (!$sd) {
            $result = "Error: connection failed";
        }else{
            if(!empty($body)){
                $xml = new NoSimpleXMLElement('<request xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>');
                array_walk_recursive($body, array ($xml, 'addChild'));
                $body = $xml->asXML();
            }


            //send request to server
            fputs($sd,$this->make_string($method, $uri, $body));

            //read answer
            $nl = 0;//new line detector

            //initialize body length on a high value
            $count = 65535;
            $result = "";


            while ($str = fgets($sd, 1024)){
                $result .= $str;
                $count = $count - strlen($str);
                if ($nl == 1) {
                    //set count to actual body length
                    $count = hexdec($str);
                    $nl = 0;
                }

                //remove CR/LF
                $str = preg_replace('/\015\012/', '',$str);
                if ($str == '') {
                    $nl = 1;
                }
                if ($count <= 0) {
                    break;
                }
            }
        }

        //close socket
        if($sd) {
            fclose($sd);
        }

        $this->response = $result;
        return $result;
    }

    //create request
    function make_string($method, $uri, $body){
        //header: method + host
        $str = strtoupper($method)." ".$uri." HTTP/1.1\nHOST: ".$this->host;

        //header: ...
        $str .= "\nConnection: Keep-Alive\nUser-Agent: bdomHTTP\nContent-Type: text/xml; charset=iso-8859-1";

        //header: body size ... if any
        if ($body) {
            $str .= "\nContent-Length: ".strlen($body);
        }

        $str .= "\n\n";

        //append body ... if any
        if ($body) {
            $str .= $body ;
        }
        return $str;
    }

}
class NoSimpleXMLElement extends SimpleXMLElement {
    public function addChild($name,$value = NULL,$ns = NULL) {
        parent::addChild($value,$name,$ns);
    }
}
