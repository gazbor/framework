<?php
namespace app\framework;

class Response {
    protected $status = 200;
    protected $headers = [];
    protected $body = "";
    protected $file = null;
    
    public function __construct() {
        
    }
    
    public static function view($path, $params = []) {
        $view = new View;
        return self::html($view->render($path, $params));
    }
    
    public static function html($str) {
        $res = new Response;
        $res->body = $str;
        $res->headers["Content-Type"] = "text/html; charset=UTF-8";
        return $res;
    }
    
    public static function file($hash) {
        $path = Storage::path($hash);
        if ($path) {
            $res = new Response;
            $res->header("Content-Transfer-Encoding", "Binary");
            $res->header("Content-Length", filesize($path));
            $res->file = $path;
            return $res;
        } else {
            $res = new Response;
            return $res->status(404)->html("File not found");
        }
    }
    
    public static function download($hash, $filename = "noname") {
        return self::file($hash)->header("Content-Disposition", "attachment; filename=" . $filename);
    }
    
    public static function json($val) {
        $res = new Response;
        $res->body = json_encode($val, JSON_PRETTY_PRINT);
        $res->headers["Content-Type"] = "application/json; charset=UTF-8";
        return $res;
    }
    
    public static function redirect($url, $status = 302) {
        $res = new Response;
        $res->header("Location", $url);
        $res->status = $status;
        return $res;
    }
    
    public static function redirectPermanently($url) {
        return $this->redirect($url, 301);
    } 
    
    public function header($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function status($code) {
        $this->status = $code;
        return $this;
    }
    
    public function write() {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header($key . ": " . $value);
        }
        if ($this->file) {
            readfile($this->file);
        } else if ($this->body) {
            echo $this->body;
        }
    }
}

?>