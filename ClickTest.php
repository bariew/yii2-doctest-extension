<?php
/**
 * ClickTest class file.
 * @copyright (c) 2014, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\docTest;

use RollingCurl\Request;
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
        '/^mailto/',
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
    public $groupUrls = true;
    
    /**
     * @var array regexps to add to exception like [['(.*\/)', false],['(\d+)', true]]
     * @see ClickTest::createExcept()
     */
    public $createExcepts = [];

    /**
     * @var array curl options.
     */
    public $curlOptions = [];

    protected $urlCounter = 1;
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
     * @param string $startUrl base path for page to click all links on.
     * @return \self $this this
     */
    public function clickAllLinks($startUrl = '/')
    {
        ob_end_flush();
        echo "\n\n Visiting app urls: \n";
        if (!$this->startTime) {
            $this->startTime = time();
        }
        $startUrl = $this->prepareUrl($startUrl);
        $this->visited[] = $startUrl;
        $this->getCurl()->multiRequest([$startUrl], function($request) {
            return $this->visitContentUrls($request);
        });
        return $this;
    }

    /**
     * Callback for curl multirequests.
     * Gets urls from response body and calls multicurl again.
     * @param Request $request curl request.
     * @return mixed nevermind.
     */
    public function visitContentUrls(Request $request)
    {
        $result =  $request->getResponseInfo();
        echo "\n" . $this->urlCounter++ .". {$result['url']} (", round($result['total_time'], 1)
            . " sec.) - {$result['http_code']} " . ($result['http_code'] < 400 ? "OK" : "ERROR");
        if ($result['http_code'] >= 400) {
            return $this->errors[$request->getUrl()] = $this->responseHeader($request, 'http_code');
        }
        if (!$urls = $this->getPageUrls($request->getResponseText(), $result['url'])) {
            return;
        }
        $this->getCurl()->multiRequest($urls, function($request) {
            return $this->visitContentUrls($request);
        });
    }

    /**
     * gets response header value.
     * @param Request $request curl request
     * @param string $key header hey.
     * @return string header value.
     */
    public function responseHeader(Request $request, $key)
    {
        $info = $request->getResponseInfo();
        return @$info[$key];
    }


    /**
     * Finds all page urls.
     * @param string $body page body.
     * @param $parentUrl
     * @return array urls
     */
    protected function getPageUrls($body, $parentUrl)
    {
        $result = [];
        $doc = \phpQuery::newDocument($body);
        foreach ($doc->find($this->selector) as $el) {
            $url = $this->passedUrls[] = pq($el)->attr('href');
//            if (strpos($url, 'access/ip/user-index')) {
//                echo '--------'. $parentUrl;exit;
//            }
            if (pq($el)->attr('disabled') || pq($el)->attr('data-method') || $this->filterUrl($url)) {
                continue;
            }
            $result[] = $this->visited[] = $this->prepareUrl($url);
        }
        return $result;
    }

    /**
     * Checks whether url has to be rejected by filters.
     * @param string $url url
     * @return boolean whether url is in filter
     */
    protected function filterUrl($url)
    {
        $parsedUrl = parse_url($url);
        $fullUrl = $this->prepareUrl($url);
        if (in_array($fullUrl, $this->visited)) {
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
        foreach ($this->createExcepts as $patternAr) {
            $this->createExcept($url, $patternAr);
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
     * @return Curl curl instance
     */
    public function getCurl()
    {
        return $this->_curl ? $this->_curl : ($this->_curl = new Curl($this->curlOptions));
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
     * Adds base path to url.
     * @param string $url url.
     * @return string Full url.
     */
    protected function prepareUrl($url)
    {
        return $this->baseUrl . str_replace($this->baseUrl, "", $url);
    }

    /**
     * Generates new except regexp rule based on string and regexp array.
     * E.g. $string 'http://lkoffice.dev2/customer/note/view/5429/set'
     * and $patternAr = [['(.*\/)', false], ['(\d+)', true],['(\/\w+)', false]];
     * Will return '/http\:\/\/lkoffice\.dev2\/customer\/note\/view\/(\d+)\/set/' regexp.
     * @param string $string compared string to regexp.
     * @param array $patternAr array of regex parts with remark true/false whether part is variable.
     * @return bool|string regular expression.
     */
    protected function createExcept($string, $patternAr)
    {
        $pattern = '/';
        foreach($patternAr as $data) {
            $pattern .= $data[0];
        }
        $pattern .= '/';
        if (!preg_match($pattern, $string, $matches)) {
            return false;
        }
        $result = '';
        foreach($patternAr as $key => $data){
            $result .= $data[1] ? $data[0] : preg_quote($matches[$key+1], '/');
        }
        return $this->except[] = '/'.$result .'/';
    }
}