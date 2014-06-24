<?php
/**
 * UnitTest class file.
 * @copyright (c) 2013, Galament
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace bariew\docTest;

use yii\codeception\TestCase;
/**
 * Tests model with asserts defined in model method @example doc blocks.
 * 
 * Usage:
 *    define @example docblocks in your target class method descriptions:
 *        ...
 *         * @example $this->getDefaultName() == "Mr. Smith"
 *        ...
 *        protected function getDefaultName()
 *        {
 *            return "Mr. Smith"
 *        }
 *
 *    Call DocTest from your test script: 
 *       $docTester = new \bariew\docTest\UnitTest("app\models\User");
 *       $docTester->test();
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class UnitTest extends TestCase
{
    /**
     * @var string called model class name
     */
    public $className;
    /**\
     * @var \ReflectionClass reflection instance for called model 
     */
    public $reflection;
    /**
     * @var array called model method docblocks parsed with MethodParser
     */
    public $methods = [];
    
    
    /* TESTING */
    
    /**
     * runs all model methods docblocks @examples as assets
     */
    public function test()
    {
        foreach ($this->methods as $name => $data) {
            $this->testMethod($name);
        }
    }
    /**
     * tests model method @examples as assets
     * @param string $methodName the name of tested method
     * @return boolean true if no examples in dockblocks provided
     */
    public function testMethod($methodName)
    {
        if (!$examples = @$this->methods[$methodName]->tags['example']) {
            return true;
        }
        
        foreach ($examples as $example) {
            $unitModel = new $this->className();
            $example = str_replace(
                ['$this->', 'new self', 'self::'], 
                ['$unitModel->', 'new ' . $this->className, $this->className . '::'], 
                $example
            );
            assert($example, " in {$this->className}::{$methodName}()");       
        }
    }
    
    
    /* CONSTRUCT */
    
    /**
     * initiates model
     * @param string $className texted model class name
     */
    public function __construct($className)
    {
        parent::__construct($className);
        $this->reflection = new \ReflectionClass($className);
        $this->className = $className;
        $this->parseMethodDocs();
    }
    /**
     * parses tested model method docblocks
     * and creates arrays of params content from docs
     */
    protected function parseMethodDocs()
    {
        foreach($this->reflection->getMethods() as $method){
            $this->methods[$method->name] = new MethodParser($this->className, $method->name);
        }
    }
}