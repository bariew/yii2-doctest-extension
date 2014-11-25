<?php
/**
 * AllLinkClickTest class file.
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
class AllLinkClickTest extends \yii\codeception\TestCase
{
    /**
     * Clicks all app links.
     */
    public function testLinks()
    {
        $params = Yii::$app->params;
        $clickTest = new ClickTest($params['domainName'], [
            'formOptions' => [], // adding form sending
            'groupUrls' => true, // exclude urls with only different GET params
            'createExcepts' => [
                [['(.*\/)', false],['(\d+)', true]] // see ClickTest docs
            ],
            'curlOptions' => [
                'cookieFile' => $params['curlCookieFile'], //path to cookie file
            ]
        ]);
        $clickTest->selector = 'a:not([href=""])'; // phpQuery selector for searching urls on pages.
        $clickTest->except[] = '/storage/'; // exclude url.

        // login to your login page with your access data.
        $clickTest->request(
            '/index/logout' // first doing logout.
        )->login('/index/login', [ // and login.
            'LoginForm[username]' => $params['auth']['username'],
            'LoginForm[password]' => $params['auth']['password'],
            'LoginForm[language]'=>'en',
            // click all site links recursively starting from root '/' url.
        ]);
        $clickTest->clickAllLinks('/'); // this is the main action - clicking all found urls.
        $clickTest->result(); // returning result.
    }
}