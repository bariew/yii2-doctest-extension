<?php
/**
 * UrlTest class file.
 * @copyright (c) 2013, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\docTest;
/**
 * Tests url requests results using class @example docblocks as assets.
 * 
 * Usage:
 *    1. define @example docblocks in your target class method descriptions:
 *       $this->post("http://mySite.com/myPath", ["myParam"=>"MyValue"]) == '{"code":200, "message":"OK"}'
 *    2. Call DocTest from your test script: 
 *        $docTester = new \bariew\docTest\UnitTest("app\models\User");
 *        $docTester->test();
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class UrlTest extends UnitTest
{    
    /* TESTING */

    /**
     * @inheritdoc
     */
    protected function runExample($example, $methodName)
    {
        assert($example, " in {$this->className}::{$methodName}()"); 
    }
<<<<<<< HEAD
    
    protected function request($url, $post = [], $files = [])
=======
    /**
     * sends post request
     * @param string $url post url
     * @param array $params post params
     * @param array $files $_FILES array
     * @return string response body
     */
    public function post($url, $params = [], $files = [])
    {
        return $this->getCurl()->request($url, $params, $files);
    }
    /**
     * sends get request
     * @param string $url post url
     * @return string response body
     */
    public function get($url)
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
    {
        return $this->getCurl()->request($url, false);
    }

<<<<<<< HEAD
    protected function buildCuery($arrays, &$new = array(), $prefix = null)
=======
    public function getCurl($options = [])
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
    {
        return new Curl($options);
    }
}