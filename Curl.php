<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 6/24/14
 * Time: 10:48 AM
 */

namespace bariew\docTest;


class Curl
{
    public $url;
    public $post;
    public $headers = [];
    public $body = '';
    public $cookieFile = '/tmp/clickTestCookie';

    /**
     * sends curl request
     * @param string $url post url
     * @param array $post post params
     * @param array $files $_FILES array
     * @return string response body
     */
    public function request($url, $post = [], $files = [])
    {
        $this->url = $url;
        $this->post = $post;
        $curlOptions = [
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => 60,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_HTTPAUTH        => CURLAUTH_ANY,//CURLAUTH_BASIC,
            CURLOPT_VERBOSE         => true,
            CURLOPT_HEADER          => true,
            CURLOPT_COOKIEJAR       => $this->cookieFile,
            CURLOPT_COOKIEFILE      => $this->cookieFile,
            CURLOPT_VERBOSE         => false,
        ];
        if($files){
            foreach(array_keys($files['name']) as $name){
                if(!$path = @$files['tmp_name'][$name]){
                    continue;
                }
                $post[get_class($this)][$name] = "@{$path};filename=" . basename($files['name'][$name]);
            }
        }
        if($post){
            $this->buildQuery($post, $postQuery);
            $curlOptions[CURLOPT_POST]            = true;
            $curlOptions[CURLOPT_POSTFIELDS]      = $postQuery;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $body = substr($result, $header_size);
        $this->headers = $this->setHeaders(substr($result, 0, $header_size));
        return $body;
    }
    /**
     * Creates post query string with subqueries
     * @param array $arrays params
     * @param array $new returned likned array
     * @param string $prefix key prefix
     * @author not me
     */
    protected function buildQuery($arrays, &$new = array(), $prefix = null)
    {
        if(is_object($arrays)){
            $arrays = get_object_vars($arrays);
        }

        foreach($arrays AS $key => $value){
            $k = isset($prefix)
                ? $prefix . '[' . $key . ']'
                : $key;
            if(is_array($value) OR is_object($value)){
                $this->buildQuery($value, $new, $k);
            }else{
                $new[$k] = $value;
            }
        }
    }

    public function getCookie($key)
    {
        $cookies = file_get_contents($this->cookieFile);
        return (preg_match('/\s+'.$key.'\s+(\w+)/', $cookies, $matches))
            ? $matches[1] : false;
    }

    /**
     * @link http://stackoverflow.com/questions/10589889/returning-header-as-array-using-curl
     */
    public function setHeaders($headerContent)
    {
        $headers = array();
        // Split the string on every "double" new line.
        $arrRequests = explode("\r\n\r\n", $headerContent);
        // Loop of response headers. The "count() -1" is to
        //avoid an empty row for the extra line break before the body of the response.
        for ($index = 0; $index < count($arrRequests) -1; $index++) {
            foreach (explode("\r\n", $arrRequests[$index]) as $i => $line){
                if ($i === 0) {
                    $headers[$index]['http_code'] = $line;
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$index][$key] = $value;
                }
            }
        }

        return $headers;
    }

    public function isSuccess()
    {
        if (!preg_match('/ (\d+) /', $this->getHeader('http_code'), $matches)) {
            return false;
        }
        return $matches[1] < 400; // http response code;
    }

    public function getHeader($key, $num = false)
    {
        if (!$this->headers) {
            throw new \Exception("Could not connect to " . $this->url);
        }
        $headers = $num === false ? end($this->headers) : $this->headers[$num];
        return $headers[$key];
    }
} 