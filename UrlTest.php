<?php

namespace bariew\docTest;

class UrlTest extends UnitTest
{    
    /* TESTING */
    
    public function post($url, $params = [], $files = [])
    {
        return $this->request($url, $params, $files);
    }
    
    public function get($url)
    {
        return $this->request($url, false);
    }

    
    public function clear($string)
    {
        return preg_replace('/\W*/', '', $string);
    }
    
    public function request($url, $post = [], $files = [])
    {
        $curlOptions = [
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => 60,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_HTTPAUTH        => CURLAUTH_ANY,//CURLAUTH_BASIC,
            CURLOPT_VERBOSE         => true,
            CURLOPT_HEADER          => true
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
            $this->buildCuery($post, $postQuery);
            $curlOptions = array_merge($curlOptions, [
                CURLOPT_POST            => true,
                CURLOPT_POSTFIELDS      => $postQuery                
            ]);
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);        
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $body = substr($result, $header_size); 
        return $body;
    }

    function buildCuery($arrays, &$new = array(), $prefix = null)
    {
        if(is_object($arrays)){
            $arrays = get_object_vars($arrays);
        }

        foreach($arrays AS $key => $value){
            $k = isset($prefix) 
                ? $prefix . '[' . $key . ']' 
                : $key;
            if(is_array($value) OR is_object($value)){
                $this->buildCuery($value, $new, $k);
            }else{
                $new[$k] = $value;
            }
        }
    }
}