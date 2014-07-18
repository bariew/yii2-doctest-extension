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
        $clickTest->result();
    }

    /**
     * Creates click test instance.
     * @return ClickTest click test instance.
     */
    public function getClickTest()
    {
        // init clicktest with required base url param.
        $params = Yii::$app->params;
        $clickTest = new ClickTest($params['domainName'], [
            'groupUrls' => true,
            'createExcepts' => [
                [['(.*\/)', false],['(\d+)', true]]
            ],
            'curlOptions' => [
                'cookieFile' => Yii::$app->params['curlCookieFile'],
                'options'   => [
                    CURLOPT_HTTPAUTH=> CURLAUTH_BASIC,
                    CURLOPT_USERPWD => $params['httpAuth']['username'].':'.$params['httpAuth']['password']
                ]
            ]
        ]);
        $clickTest->selector = 'a:not([href=""])';
        $clickTest->except[] = '/storage/';

        // login to your login page with your access data.
        $clickTest->request(
            '/index/logout'
        )->login('/index/login', [
                    'LoginForm[username]'=>'Alena',
                    'LoginForm[password]'=>'password',
                    'LoginForm[language]'=>'en',
                    // click all site links recursively starting from root '/' url.
                ]);
        return $clickTest;
    }

}