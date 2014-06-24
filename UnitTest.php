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
<<<<<<< HEAD
    public $className;
    /**\
     * @var \ReflectionClass reflection instance for called model 
     */
    public $reflection;
    /**
     * @var array called model method docblocks parsed with MethodParser
     */
    public $methods = [];
    
    
=======
    protected $className;
    /**\
     * @var \ReflectionClass reflection instance for called model 
     */
    protected $reflection;
    /**
     * @var array called model method docblocks parsed with MethodParser
     */
    protected $methods = [];
    /**
     * @var string name of docblock tag with the assert tests 
     */
    protected $tagName = 'example';
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
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
<<<<<<< HEAD
    public function testMethod($methodName)
=======
    protected function testMethod($methodName)
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
    {
        if (!$examples = @$this->methods[$methodName]->tags['example']) {
            return true;
        }
        
        foreach ($examples as $example) {
<<<<<<< HEAD
            $unitModel = new $this->className();
            $example = str_replace(
                ['$this->', 'new self', 'self::'], 
                ['$unitModel->', 'new ' . $this->className, $this->className . '::'], 
                $example
            );
            assert($example, " in {$this->className}::{$methodName}()");       
        }
    }
    
    
=======
            $this->runExample($example, $methodName);
        }
    }
    /**
     * runs current example assert
     * @param string $example assert impression
     * @param string $methodName called class method name
     */
    protected function runExample($example, $methodName)
    {
        $unitModel = new $this->className(null, null, null, null, null);
        $example = str_replace(
            ['$this->', 'new self', 'self::'], 
            ['$unitModel->', 'new ' . $this->className, $this->className . '::'], 
            $example
        );
        assert($example, " in {$this->className}::{$methodName}()"); 
    }
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
    /* CONSTRUCT */
    
    /**
     * initiates model
     * @param string $className texted model class name
     */
<<<<<<< HEAD
    public function __construct($className)
=======
    public function __construct($className, $options = [])
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
    {
        parent::__construct($className);
        $this->reflection = new \ReflectionClass($className);
        $this->className = $className;
<<<<<<< HEAD
=======
        foreach ($options as $attribute => $value) {
            $this->$attribute = $value;
        }
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
        $this->parseMethodDocs();
    }
    /**
     * parses tested model method docblocks
     * and creates arrays of params content from docs
     */
    protected function parseMethodDocs()
    {
<<<<<<< HEAD
        foreach($this->reflection->getMethods() as $method){
=======
        foreach ($this->reflection->getMethods() as $method) {
>>>>>>> b79c8910c297ef4222570383ff4edafaf2da4473
            $this->methods[$method->name] = new MethodParser($this->className, $method->name);
        }
    }
}