<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 25.11.14
 * Time: 12:50
 */

namespace bariew\docTest;


class FormTest
{
    private $url;
    private $content;

    private $baseUrl;
    private $selector = 'form';

    public $values = [];

    private $except = [];
    public $visited = [];

    public function postData($parentUrl)
    {
        $result = [];
        $doc = \phpQuery::newDocument($this->content);
        foreach ($doc->find($this->selector) as $el) {
            if (!$url = pq($el)->attr('action')) {
                $url = $this->url;
            }
            $post = $this->createPost(pq($el));
            if ($this->filterPost($url, $post)) {
                continue;
            }
//            if ($url == '/account/view') {
//                echo $parentUrl;exit;
//            }
            if (strtolower(@pq($el)->attr('method')) == 'post'){
                $result[$this->prepareUrl($url)] = ['post' => $post];
            } else {
                $result[$this->prepareUrl($url, $post)] = [];
            }
        }

        return $result;
    }

    private function createPost(\phpQueryObject $form)
    {
        $result = [];
        foreach ($form->find('input, select, checkbox, textarea') as $input) {
            $el = pq($input);
            if (
                ($value = $el->attr('example')) || $value = $el->attr('value')) {
                $result[$el->attr('name')] = substr($value, 0, 100);
                continue;
            }
            foreach ($this->values as $regex => $v) {
                if (preg_match($regex, $el->attr('name'))) {
                    $result[$el->attr('name')] = $v;
                    continue 2;
                }
            }
            switch($input->tagName) {
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

    private function addInput(\phpQueryObject $input, &$data, $value = null)
    {
        $data[$input->attr('name')] = substr($input->attr('value'), 0, 100) ? : 'test';
    }
    private function addSelect(\phpQueryObject $input, &$data, $value = null)
    {
        $value = $value ?? 1; // default
        foreach ($input->find('option') as $item) {
            $item = pq($item);
            $value = $item->attr('value');
            if ($item->attr('selected')) {
                break;
            }
        }
        $data[$input->attr('name')] = $value;
    }
    private function addCheckbox(\phpQueryObject $input, &$data, $value = null)
    {
        $data[$input->attr('name')] = 1;
    }
    private function addTextarea(\phpQueryObject $input, &$data, $value = null)
    {
        $data[$input->attr('name')] = "Any text";
    }

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

    public function setOptions($options)
    {
        foreach ($options as $attribute => $value) {
            $this->$attribute = $value;
        }
    }
} 