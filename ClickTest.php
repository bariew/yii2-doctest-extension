<?php
/**
 * UrlTest class file.
 * @copyright (c) 2013, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\docTest;

use yii\codeception\TestCase;

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
class ClickTest extends TestCase
{
    /* TESTING */

    public $responseHeaders;
    public $responseBody;
    public $errors = [];

    protected $_curl;

    public function request($url, $post = [])
    {
        $this->getCurl()->request($url, $post);
        if (!$this->getCurl()->isSuccess()) {
            $this->errors[$url] = $this->getCurl()->headers[0]['http_code'];
        }
        return $this;
    }

    public function login($url, $post)
    {
        $getResult = $this->request($url);
        if (preg_match('/\"_csrf\" value\=\"(\S+)\"/', $getResult, $matches)) {
            $post['_csrf'] = $matches[1];
        } else {
            return true;
        }
        $this->request($url, $post);
        return $this;
    }

    public function getCurl($options = [])
    {
        return $this->_curl ? $this->_curl : ($this->_curl = new Curl($options));
    }
}