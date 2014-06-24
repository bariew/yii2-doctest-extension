
Doc test Yii2 extension
===================
Executes @example docBlock params as test cases.


Description
-----------

This extension is for fast testing.
It takes your docblock @example tag and runs its content as assert expression.
So you can define few @examples for your method to be sure that it 
still return the data you expect.
It also runs you url get and post requests, e.g. you can test your API Controller
with @example tags for each method.


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


Usage:
------
-----
```
    Unit tests:
    1. define @example docblock in your tested class method descriptions:
        /**
         * Returns user default name
         * @return string name.
         * @example $this->getDefaultName() == "Mr. Smith"
         */
        protected function getDefaultName()
        {
            return "Mr. Smith"
        }

    2. Call UnitTest from your test script: 
        $docTester = new \bariew\docTest\UnitTest("app\models\User");
        $docTester->test();

    You may also define your own docblock tag name, e.g.
    1. * @test ... assert impression
    2. $docTester = new UnitTest("My\Class\Name", ['tagName' => @test]);
```
```
    Url tests:  
    1. define @example docblocks in your target class method descriptions:
        $this->post("http://mySite.com/myPath", ["myParam"=>"MyValue"]) == '{"code":200, "message":"OK"}'
    2. Call DocTest from your test script:
        $docTester = new \bariew\docTest\UrlTest("app\controllers\UserController");
        $docTester->test();
```

```
    Click tests:
    1. 
```