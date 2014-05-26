<?php

namespace bariew\docTest;

use yii\codeception\TestCase;

class UnitTest extends TestCase
{
    public $className;
    public $reflection;
    public $methods = array();
    
    
    /* TESTING */
    
    public function test()
    {
        foreach ($this->methods as $name => $data) {
            $this->testMethod($name);
        }
    }
    
    public function testMethod($methodName)
    {
        if (!$examples = @$this->methods[$methodName]->tags['example']) {
            return true;
        }
        
        foreach ($examples as $example) {
            assert($example, " in {$this->className}::{$methodName}()");       
        }
    }
    

    /* PREPARE */
    
    protected function processMethods()
    {
        foreach($this->reflection->getMethods() as $method){
            $this->methods[$method->name] = new MethodParser($this->className, $method->name);
        }
    }
    
    /* BASE */
    
    public function __construct($className)
    {
        parent::__construct($className);
        $this->reflection = new \ReflectionClass($className);
        $this->className = $className;
        $this->processMethods();
    }
}