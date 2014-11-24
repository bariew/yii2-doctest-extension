<?php
/**
 * Created by PhpStorm.
 * User: pt
 * Date: 21.11.14
 * Time: 18:30
 */

namespace bariew\docTest;


class Mock
{
    /**
     * @var \ReflectionClass
     */
    private $reflection;

    private $body;

    public function create($className, $mockData = [], $initData = [])
    {
        $className        = str_replace('\\\\', '\\', '\\' . $className); // leaving only one leading slash
        $this->reflection = new \ReflectionClass($className);
        $shortName        = $this->reflection->getShortName();
        $newClass         = preg_replace('/^(.*)\\\\(\w+)$/', '$1\\' . "{$shortName}MockGhost", $className);
        if (!class_exists($newClass)) {
            $this->createMockClass($mockData);
        }
        $model = new $newClass($initData);
        foreach ($mockData as $attribute => $value) {
            if (
                is_callable($value)
                || !isset($model->$attribute)
                || !$this->reflection->hasProperty($attribute)
                || !(new \ReflectionProperty($this->reflection, $attribute))->isPublic()
            ) {
                continue;
            }
            $model->$attribute = $value;
        }

        return $model;
    }

    private function createMockClass($mockData = [])
    {
        $shortName  = $this->reflection->getShortName();
        $classStart = self::reflectionContent($this->reflection, 1, $this->reflection->getStartLine());
        $classStart = preg_replace("/class $shortName/", "class {$shortName}MockGhost", $classStart);
        $this->body = self::reflectionBody($this->reflection, false);
        foreach ($mockData as $attribute => $value) {
            if (is_callable($value)) {
                $this->insertMethod($attribute, $value);
            } else {
                $this->insertAttribute($attribute, $value);
            }
        }
        eval($classStart . $this->body);
    }

    /**
     * @param \ReflectionClass | \ReflectionMethod $reflection
     * @param bool $withName
     * @return string
     */
    public static function reflectionBody($reflection, $withName = true)
    {
        return self::reflectionContent(
            $reflection,
            $reflection->getStartLine() - $withName,
            $reflection->getEndLine()
        );
    }

    /**
     * @param \ReflectionClass | \ReflectionMethod $reflection
     * @return string
     */
    public static function reflectionName($reflection)
    {
        return self::reflectionContent(
            $reflection,
            $reflection->getStartLine() - 1,
            $reflection->getStartLine()
        );
    }

    /**
     * @param \ReflectionClass | \ReflectionMethod $reflection
     * @param $start
     * @param $end
     * @return string
     */
    public static function reflectionContent($reflection, $start, $end)
    {
        $source = file($reflection->getFileName());
        $length = $end - $start;

        return implode("", array_slice($source, $start, $length));
    }

    /**
     * @param $methodName
     * @param callable $replacement
     * @return array|mixed|string
     */
    private function insertMethod($methodName, callable $replacement)
    {
        $functionBody = self::reflectionBody(new \ReflectionFunction($replacement));
        $functionBody = preg_replace('/.*function\s*(\(.*\})[, \]]*$/', '$1', $functionBody);

        return ($this->reflection->hasMethod($methodName))
            ? $this->replaceMethod($methodName, $functionBody)
            : $this->addMethod($methodName, $functionBody);
    }

    /**
     * @param $attributeName
     * @param callable $replacement
     * @return array|mixed|string
     */
    private function insertAttribute($attributeName, $replacement)
    {
        $replacement = var_export($replacement, true);
        return ($this->reflection->hasProperty($attributeName))
            ? $this->replaceAttribute($attributeName, $replacement)
            : $this->addAttribute($attributeName, $replacement);
    }

    private function replaceAttribute($attributeName, $replacement)
    {
        return $this->body;
    }


    /**
     * @param $methodName
     * @param $functionBody
     * @return mixed
     */
    private function replaceMethod($methodName, $functionBody)
    {
        $methodReflection = $this->reflection->getMethod($methodName);
        $newName          = preg_replace('/^(.*)(\(.*\))(.*)/', '$1$3', self::reflectionName($methodReflection));
        return $this->body = str_replace(
            self::reflectionBody($methodReflection),
            $newName . $functionBody,
            $this->body
        );
    }

    /**
     * @param $methodName
     * @param $functionBody
     * @return array|string
     */
    private function addMethod($methodName, $functionBody)
    {
        return $this->addContent("public function {$methodName}{$functionBody}");
    }

    private function addAttribute($attributeName, $replacement)
    {
        return $this->addContent("public \${$attributeName} = {$replacement};");
    }

    private function addContent($content)
    {
        $body = explode("\n", $this->body);
        while (strpos($body[count($body) - 1], '}') === false) {
            array_pop($body);
        }
        array_push($body, $body[count($body) - 1]);
        $body[count($body) - 2] =$content;
        return $this->body = implode("\n", $body);
    }
} 