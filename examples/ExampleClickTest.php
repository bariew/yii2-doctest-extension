<?php
/**
 * ExampleClickTest class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

use \bariew\docTest\ClickTest;

/**
 * Example for ClickTest usage.
 *
 * Usage: it is for running with yii2 command "vendor/bin/codecept run acceptance"
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class ExampleClickTest extends \yii\codeception\TestCase
{
    /**
     * @inheritdoc
     */
    public $appConfig = '@backend/tests/unit/_config.php';

    /**
     * Clicks all app links.
     */
    public function testLinks()
    {
        $clickTest = $this->getClickTest();
        $clickTest->clickAllLinks('/');
        // display result.
        $clickTest->result();
    }

    /**
     * Creates click test instance.
     * @return ClickTest click test instance.
     */
    public function getClickTest()
    {
        // init clicktest with required base url param.
        $clickTest = new ClickTest("http://mydomain.com", [
            'groupUrls' => true,
            'curlOptions' => [
                'groupUrls' => false,
                'cookieFile' => '/tmp/clickTestCookie',
                'options'   => [
                    CURLOPT_HTTPAUTH=> CURLAUTH_BASIC,
                    CURLOPT_USERPWD => 'myUser:myPassword'
                ]
            ]
        ]);
        $clickTest->selector = 'a:not([href=""])';
        $clickTest->except[] = '/files/';

        // login to your login page with your access data.
        $clickTest->request(
            '/logout'
        )->login('/login', [
                    'LoginForm[username]'=>'User',
                    'LoginForm[password]'=>'my password',
                    // click all site links recursively starting from root '/' url.
                ]);
        return $clickTest;
    }

}