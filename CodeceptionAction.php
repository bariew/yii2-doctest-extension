<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 29.01.15
 * Time: 15:18
 */
namespace bariew\docTest;

class CodeceptionAction extends \Codeception\Step
{
    public function run()
    {
        $this->executed = true;
        $activeModule   = \Codeception\SuiteManager::$modules[\Codeception\SuiteManager::$actions['grabTextFrom']];
        $method = new \ReflectionMethod($activeModule, $this->action);
        $method->setAccessible(true);
        return $method->invokeArgs($activeModule, $this->arguments);
    }
}