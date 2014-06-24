
Doc test Yii2 extension
===================
Executes @example docBlock params as test cases

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bariew/yii2-doctest-extension "*"
```

or add

```
"bariew/yii2-doctest-extension": "*"
```

to the require section of your `composer.json` file.


Usage
-----
```
    Unit tests:  
    1. define @example docblocks in your target class method descriptions:
        /**
         * returns user default name
         * @param string $methodName the name of tested method
         * @return boolean true if no examples in dockblocks provided
         * @example $this->getDefaultName() == "Mr. Smith"
         */
        protected function getDefaultName()
        {
            return "Mr. Smith"
        }

    2. Call DocTest from your test script: 
        $docTester = new \bariew\docTest\UnitTest("app\models\User");
        $docTester->test();
```
```
    Url tests:  
    1. define @example docblocks in your target class method descriptions:
        $this->post("http://mySite.com/myPath", ["myParam"=>"MyValue"]) == '{"code":200, "message":"OK"}'
    2. Call DocTest from your test script: 
        $docTester = new \bariew\docTest\UnitTest("app\models\User");
        $docTester->test();
```

```
    You may also define your own docblock tag name, e.g. 
    1. * @test ... assert impression
    2. $docTester = new UnitTest("My\Class\Name", ['tagName' => @test]);
```