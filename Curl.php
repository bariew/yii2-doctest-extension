<?php
/**
 * Curl class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\docTest;

/**
 * Description.
 *
 * Usage:
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class Curl
{
    public $url;
    public $post;
    public $headers = [];
    public $body = '';
    public $cookieFile = '/tmp/clickTestCookie';
    public $options = [];
    /**
     * sends curl request
     * @param string $url post url
     * @param array $post post params
     * @param array $files $_FILES array
     * @return string response body
     */
    public function request($url, $post = [], $files = [])
    {
        if (!file_exists($this->cookieFile)) {
            touch($this->cookieFile);
            chmod($this->cookieFile, 0777);
        }
        $this->url = $url;
        $this->post = $post;
        $curlOptions = $this->options + [
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
        $this->headers = $this->setHeaders(substr($result, 0, $header_size));
        return $this->body = substr($result, $header_size);
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

        foreach($arrays as $key => $value){
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

    /**
     * Gets cookie value.
     * @param string $key cookie key in array.
     * @return mixed cookie value.
     */
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

    /**
     * Checks response headers for 0-399 http code range.
     * @return bool whether request is successful
     */
    public function isSuccess()
    {
        if (!preg_match('/ (\d+) /', $this->getHeader('http_code'), $matches)) {
            return false;
        }
        return $matches[1] < 400; // http response code;
    }

    /**
     * Gets header value.
     * @param string $key header name.
     * @param mixed $num number of headers array (if many in response)
     * @return string header value.
     * @throws \Exception exception Could not connect.
     */
    public function getHeader($key, $num = false)
    {
        if (!$this->headers) {
            throw new \Exception("Could not connect to " . $this->url);
        }
        $headers = $num === false ? end($this->headers) : $this->headers[$num];
        return $headers[$key];
    }

    public function __construct($options = [])
    {
        $this->options = $options;
    }
} 