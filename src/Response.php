<?php
namespace XmlApiClient;

class Response {
    var $resp;

    function __construct($resp) {
        $this->resp = $resp;
    }

    //extract response code
    function code() {
        preg_match('/HTTP\/[10\.]+\s+([0-9]+)/', $this->resp, $code);
        return $code[1];
    }

    //extract response body
    function body() {
        //body is between two empty lines for "chunked" encoding
        $chunk = preg_split('/(?=^\r)/m',$this->resp);
        $body = "";
        if ($chunk[1]){
            //extract body from \nsize(body)\n0
            $body = str_replace("\n[0-9a-fA-F]+", "", $chunk[1]);
        }



        return trim($body);
    }

    function array(){
        $body = explode('<', trim($this->body()), 2);
        $body = trim('<'.$body[1]);
        $body = simplexml_load_string($body);
        return $body;
    }
}
