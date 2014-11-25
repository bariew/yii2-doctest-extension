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
    private $postData;

    private $baseUrl;
    private $selector = 'form';

    private $except = [];
    private $visited = [];

    public function postData()
    {
        $result = [];
        $doc = \phpQuery::newDocument($this->content);
        foreach ($doc->find($this->selector) as $el) {
            $url = pq($el)->attr('action');
            $post = $this->createPost(pq($el));
            if ($this->filterPost($url, $post)) {
                continue;
            }
            $result[$this->prepareUrl($url)] = $post;
        }
        return $result;
    }

    private function createPost(\phpQueryObject $form)
    {
        $result = [];
        foreach ($form->find('input, select, checkbox') as $input) {
            $el = pq($input);
            switch($el->tagName) {
                case 'input' : $this->addInput($el, $result);
                    break;
                case 'select': $this->addSelect($el, $result);
                    break;
                case 'checkbox': $this->addCheckbox($el, $result);
                    break;
            }

        }
        return [];
    }

    private function addInput(\phpQueryObject $input, &$data)
    {
        $data[] = [];
    }
    private function addSelect(\phpQueryObject $input, &$data)
    {
        $data[] = [];
    }
    private function addCheckbox(\phpQueryObject $input, &$data)
    {
        $data[] = [];
    }

    private function filterPost($url, $post)
    {
        $parsedUrl = parse_url($url);
        $fullUrl = $this->prepareUrl($url);
        $jsonPost = json_encode($post);
        if (isset($this->visited[$fullUrl]) && in_array($jsonPost, $this->visited[$fullUrl])) {
            return true;
        }
        $this->visited[$fullUrl] = $jsonPost;
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
     * @return string Full url.
     */
    private function prepareUrl($url)
    {
        return $this->baseUrl . str_replace($this->baseUrl, "", $url);
    }

    public function setOptions($options)
    {
        foreach ($options as $attribute => $value) {
            $this->$attribute = $value;
        }
    }
} 