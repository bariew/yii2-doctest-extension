<?php
/**
 * ClickTest class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\docTest;

use \Yii;

/**
 * Clicks all site links.
 * 
 * See place example ExampleClickTest file from examples folder into your tests/unit folder
 * And run "vendor/bin/codecept run unit" command.
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class ClickTest
{
    /* TESTING */

    public $response;
    /**
     * @var Curl curl instance.
     */
    protected $_curl;

    /**\
     * @var string base site url
     */
    public $baseUrl;

    /**
     * @var string css selector for all site links
     */
    public $selector = 'a:not([href=""]):not([disabled])';

    /**
     * @var array excluded urls regexps.
     */
    public $except = [
        '/\/logout$/',
        '/\/delete/',
        '/#/',
        '/sort=/'
    ];

    /**
     * @var array urles that have already benn pass.
     */
    public $passedUrls = [];

    /**
     * @var array urls that have already been visited.
     */
    public $visited = [];

    /**
     * @var array links click all result errors.
     */
    public $errors = [];

    /**
     * @var integer start timestamp
     */
    protected $startTime;

    /**
     * @var bool whether to skip urls with the same path (but different GET params)
     */
    public $groupUrls = false;

    /**
     * @inheritdoc
     */
    public function __construct($baseUrl, $options = [])
    {
        $this->baseUrl = $baseUrl;
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        ini_set("xdebug.max_nesting_level", 1000);
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
     * Clicks all link on page recursively.
     * @param string $url base path for page to click all links on.
     */
    public function clickAllLinks($url = '/')
    {
        if (!$this->startTime) {
            $this->startTime = time();
        }
        $startUrl = $this->prepareUrl($url);
        $this->visited[] = $startUrl;
        $this->visitUrls($this->getPageUrls($startUrl));
    }

    /**
     * Adds base path to url.
     * @param string $url url.
     * @return string Full url.
     */
    protected function prepareUrl($url)
    {
        return $this->baseUrl . str_replace($this->baseUrl, "", $url);
    }

    /**
     * echoes result errors if any
     */
    public function result()
    {
        echo "\n Checked " . count($this->visited) . " urls in " . (time()-$this->startTime) . " sec. \n\n";
        if ($this->errors) {
            echo "\n Errors:";
            foreach ($this->errors as $url => $code) {
                echo "\n {$url} - {$code} \n";
            }
            exit(1);
        } else {
            exit("\n OK! \n");
        }
    }
    /**
     * Finds all page urls.
     * @param string $url
     * @return array urls
     */
    protected function getPageUrls($url)
    {
        if (!$body = $this->request($url)->response){
            return [];
        }
        $result = [];
        $doc = \phpQuery::newDocument($body);
        foreach ($doc->find($this->selector) as $el) {
            $result[] = pq($el)->attr('href');
        }
        return $result;
    }

    /**
     * Clicks urls.
     * @param array $urls urls
     */
    protected function visitUrls($urls)
    {
        foreach ($urls as $url) {
            if ($this->filterUrl($url)) {
                continue;
            }
            $this->clickAllLinks($url);
        }
    }

    /**
     * Checks whether url has to be rejected by filters.
     * @param string $url url
     * @return boolean whether url is in filter
     */
    protected function filterUrl($url)
    {
        $preparedUrl = $this->prepareUrl($url);
        $parsedUrl = parse_url($url);
        if (in_array($preparedUrl, $this->passedUrls)) {
            return true;
        }
        $this->passedUrls[] = $preparedUrl;
        if (in_array($url, $this->visited)) {
            return true;
        }
        $regexp = '/'. str_replace('/', '\/', @$parsedUrl['path']) . '/';
        if ($this->groupUrls && preg_grep($regexp, $this->visited)) {
            return true;
        }
        if (isset($parsedUrl['host']) && !strpos($this->baseUrl, $parsedUrl['host'])) {
            return true;
        }

        foreach ($this->except as $filter) {
            if (preg_match($filter, $url)) {
                return true;
            }
        }
        return false;
    }


    /* CURL */

    /**
     * Creates remote request.
     * @param string $url request url.
     * @param array $post post data;
     * @return bool|string response body.
     */
    public function request($url, $post = [])
    {
        $url = $this->prepareUrl($url);
        $this->response = $this->getCurl()->request($url, $post);
        if (!$this->getCurl()->isSuccess()) {
            $this->errors[$url] = $this->getCurl()->getHeader('http_code');
            $this->response = false;
        }

        return $this;
    }

    /**
     * Logs user in.
     * @param string $url login url.
     * @param array $post login post.
     * @throws \Exception
     * @return object $this self instance
     */
    public function login($url, $post)
    {
        $getResult = $this->request($url)->response;
        $doc = \phpQuery::newDocument($getResult);
        $inputName = key($post);
        if (!$doc->find("[name='{$inputName}']")->length) {
            throw new \Exception("Could not get page with {$inputName} on {$url}");
        }
        foreach ($doc->find("[name=_csrf]") as $el) {
            $post['_csrf'] = pq($el)->attr('value');
        }
        return $this->request($url, $post);
    }

    /**
     * Gets Curl object instance.
     * @param array $options curl options.
     * @return Curl curl instance
     */
    public function getCurl($options = [])
    {
        return $this->_curl ? $this->_curl : ($this->_curl = new Curl($options));
    }
}