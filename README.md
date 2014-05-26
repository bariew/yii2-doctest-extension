
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
    define @example docblocks in your target class method descriptions:
        @example app\models\User::instantiate()->getDefaultName() == "Paul"
    Call DocTest from your test script: 
        $docTester = new \bariew\docTest\UnitTest("app\models\User");
        $docTester->test();
```
```
    Url tests:  
    define @example docblocks in your target class method descriptions:
        $this->post("http://mySite.com/myPath", ["myParam"=>"MyValue"]) == '{"code":200, "message":"OK"}'
    Call DocTest from your test script: 
        $docTester = new \bariew\docTest\UnitTest("app\models\User");
        $docTester->test();
```