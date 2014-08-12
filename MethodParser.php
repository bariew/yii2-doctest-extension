<?php
/**
 * MethodParser class file.
 * @copyright (c) 2013, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\docTest;
/**
 * Parses class docblocks.
 * 
 * Usage:
 * 1. Parse model method: $parser = new MethodParser($className, $methodName);
 * 2. Get data for some tag (e.g. @return): $return = $parser->tags['return']
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class MethodParser extends \ReflectionMethod
{
    public $tags = array();
    /**
     * gets tags lines from docblock
     * @author Qiang Xue <qiang.xue@gmail.com> 
     */
    protected function processComment()
    {
        $comment=strtr(trim(preg_replace('/^\s*\**( |\t)?/m','',trim($this->getDocComment(),'/'))),"\r",'');
        if(preg_match('/^\s*@\w+/m',$comment, $matches, PREG_OFFSET_CAPTURE)){
            $meta=substr($comment,$matches[0][1]);
            $this->processTags($meta);
        }
    }
    /**
     * extracts tags array from docblock
     * @param string $comment docblock
     * @author Qiang Xue <qiang.xue@gmail.com> 
     */
    protected function processTags($comment)
    {
        $tags = preg_split('/^\s*@/m',$comment,-1,PREG_SPLIT_NO_EMPTY);
        foreach($tags as $tag){
            $segs   = preg_split('/\s+/',trim($tag),2);
            $tagName= $segs[0];
            $param  = isset($segs[1]) ? trim($segs[1]) : '';
            $this->tags[$tagName][] = $param;
        }
    }
    
    
    /* PREPARE */
    /**
     * inits model
     * @param string $class parsed class classname
     * @param string $name parsed class methodname
     */
    public function __construct($class , $name)
    {
        parent::__construct($class, $name);
        $this->processComment();
    }
}