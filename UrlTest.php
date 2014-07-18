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
 *        $docTester = new \bariew\docTest\UrlTest('app\controllers\SiteController');
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
    {
        return $this->getCurl()->request($url, false);
    }

    public function getCurl($options = [])
    {
        return new Curl($options);
    }
}