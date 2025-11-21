<?php
/**
 * FormTest class file
 */

namespace bariew\docTest;


use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDomInterface;

/**
 * Class FormTest
 *
 * Automatically fill html form found in the crawled site
 * @package bariew\docTest
 */
class FormTest
{
    private $url;
    private $content;

    private $baseUrl;
    private $selector = 'form';

    public $values = [];

    private $except = [];
    public $visited = [];

    /**
     * @param $parentUrl
     * @return array
     */
    public function postData($parentUrl)
    {
        $result = [];
        $doc = HtmlDomParser::str_get_html($this->content);
        foreach ($doc->find($this->selector) as $el) {
            if (!$url = $el->getAttribute('action')) {
                $url = $this->url;
            }
            $post = $this->createPost($el);
            if ($this->filterPost($url, $post)) {
                continue;
            }
//            if ($url == '/account/view') {
//                echo $parentUrl;exit;
//            }
            if (strtolower(@$el->getAttribute('method')) == 'post'){
                $result[$this->prepareUrl($url)] = ['post' => $post];
            } else {
                $result[$this->prepareUrl($url, $post)] = [];
            }
        }

        return $result;
    }

    /**
     * @param SimpleHtmlDomInterface $form
     * @return array
     */
    private function createPost($form)
    {
        $result = [];
        foreach ($form->find('input, select, checkbox, textarea') as $el) {
            if (
                ($value = $el->getAttribute('example')) || $value = $el->getAttribute('value')) {
                $result[$el->getAttribute('name')] = substr($value, 0, 100);
                continue;
            }
            foreach ($this->values as $regex => $v) {
                if (preg_match($regex, $el->getAttribute('name'))) {
                    $result[$el->getAttribute('name')] = $v;
                    continue 2;
                }
            }
            switch($el->tag) {
                case 'input' : $this->addInput($el, $result, $value);
                    break;
                case 'select': $this->addSelect($el, $result, $value);
                    break;
                case 'checkbox': $this->addCheckbox($el, $result, $value);
                    break;
                case 'textarea': $this->addTextarea($el, $result, $value);
                    break;
            }

        }
        return $result;
    }

    /**
     * @param SimpleHtmlDomInterface $input
     * @param $data
     * @param null $value
     */
    private function addInput($input, &$data, $value = null)
    {
        $data[$input->getAttribute('name')] = substr($input->getAttribute('value'), 0, 100) ? : 'test';
    }

    /**
     * @param SimpleHtmlDomInterface $input
     * @param $data
     * @param null $value
     */
    private function addSelect($input, &$data, $value = null)
    {
        $value = $value ?? 1; // default
        foreach ($input->find('option') as $item) {
            $value = $item->getAttribute('value');
            if ($item->getAttribute('selected')) {
                break;
            }
        }
        $data[$input->getAttribute('name')] = $value;
    }

    /**
     * @param SimpleHtmlDomInterface $input
     * @param $data
     * @param null $value
     */
    private function addCheckbox($input, &$data, $value = null)
    {
        $data[$input->getAttribute('name')] = 1;
    }

    /**
     * @param SimpleHtmlDomInterface $input
     * @param $data
     * @param null $value
     */
    private function addTextarea($input, &$data, $value = null)
    {
        $data[$input->getAttribute('name')] = "Any text";
    }

    /**
     * @param $url
     * @param $post
     * @return bool
     */
    private function filterPost($url, $post)
    {
        $parsedUrl = parse_url($url);
        $fullUrl = $this->prepareUrl($url);
        if (in_array($fullUrl, $this->visited)) {
            return true;
        }
        $this->visited[] = $fullUrl;
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

    /**
     * Adds base path to url.
     * @param string $url url.
     * @param array $data
     * @return string Full url.
     */
    private function prepareUrl($url, $data = [])
    {
        $url = $this->baseUrl . str_replace($this->baseUrl, "", $url);
        if (!$data) {
            return $url;
        }
        $url = parse_url($url);
        return $url['path'] . '?' . http_build_query(array_merge((array) @$url['query'], $data));
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        foreach ($options as $attribute => $value) {
            $this->$attribute = $value;
        }
    }
} 