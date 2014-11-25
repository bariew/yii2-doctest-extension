
Doc test Yii2 extension
===================
If you don't have a time to write tests.


Description
-----------

This extension is for testing without test writing. It doe few things.
1. It uses Curl to visit all your app pages (it finds them too) and submits all found forms
just to make sure they are generally ok. It is useful to avoid critical errors like 404 or 500 error pages.

2. It takes your docblock @example tag and runs its content as assert expression. See examples below.

3. It also runs you url get and post requests, e.g. you can test your controller, eg API.
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


Unit tests:
------
1. define @example docblock in your tested class method descriptions:

```
    /**
     * Returns user default name
     * @return string name.
     * @example $this->getDefaultName() == "Mr. Smith"
     */
    protected function getDefaultName()
    {
        return "Mr. Smith"
    }
```

2. Call UnitTest from your test script:
```
    $docTester = new \bariew\docTest\UnitTest("app\models\User");
    $docTester->test();
```

Url tests:
----------

1. define @example docblocks in your target class method descriptions:
```
    $this->post("http://mySite.com/myPath", ["myParam"=>"MyValue"]) == '{"code":200, "message":"OK"}'
```

2. Call DocTest from your test script:
```
    $docTester = new \bariew\docTest\UrlTest("app\controllers\UserController");
    $docTester->test();
```


3. Click tests. See in examples folder.
