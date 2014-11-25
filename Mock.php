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
    private $model;

    public function create($className, $mockData = [], $initData = [])
    {
        $className        = str_replace('\\\\', '\\', '\\' . $className); // leaving only one leading slash
        $this->reflection = new \ReflectionClass($className);
        $shortName        = $this->reflection->getShortName();
        $newClass         = $this->reflection->getNamespaceName() . "\\{$shortName}MockGhost";
        //if (!class_exists($newClass)) {
            $this->createMockClass($mockData);
        //}
        $this->model = new $newClass($initData);
        $this->addData($mockData);
        return $this->model;
    }

    private function addData($mockData)
    {
        foreach ($mockData as $attribute => $value) {
            if (
                is_callable($value)
                || !isset($this->model->$attribute)
                || !$this->reflection->hasProperty($attribute)
                || !(new \ReflectionProperty($this->reflection, $attribute))->isPublic()
            ) {
                continue;
            }
            $this->model->$attribute = $value;
        }
    }

    private function createMockClass($mockData = [])
    {
        $shortName  = $this->reflection->getShortName();
        $classStart = self::reflectionContent($this->reflection, 1, $this->reflection->getStartLine()-1);
        $classStart .= PHP_EOL . "class {$shortName}MockGhost extends $shortName" . PHP_EOL;
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
     * @param \ReflectionClass | \ReflectionMethod | \ReflectionFunction $reflection
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
     * @param \ReflectionMethod $reflection
     * @return string
     */
    public static function methodName(\ReflectionMethod $reflection)
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
     * @throws \Exception
     * @return array|mixed|string
     */
    private function insertMethod($methodName, callable $replacement)
    {
        $functionReflection = new \ReflectionFunction($replacement);
        $function = self::reflectionBody($functionReflection);
        $function = str_replace(PHP_EOL, "", $function);
        if (!preg_match('/.*function\s*(\(.*\))\s*(\{.*\})[, \]]*$/', $function, $matches)) {
            throw new \Exception("Can not parse function: $function");
        }
        list($all, $args, $body) = $matches;
        return ($this->reflection->hasMethod($methodName))
            ? $this->replaceMethod($methodName, $body)
            : $this->addMethod("function " . $methodName,  $args.$body);
    }

    /**
     * @param $methodName
     * @param $functionBody
     * @return mixed
     */
    private function replaceMethod($methodName, $functionBody)
    {
        $methodReflection = $this->reflection->getMethod($methodName);
        $newName = self::methodName($methodReflection);
        if ($methodReflection->getDeclaringClass()->name != $this->reflection->name) {
            return $this->addMethod($newName, $functionBody);
        }
        return $this->body = str_replace(
            self::reflectionBody($methodReflection),
            $newName . PHP_EOL . $functionBody,
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
        return $this->addContent("{$methodName}{$functionBody}");
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

    private function addAttribute($attributeName, $replacement)
    {
        return $this->addContent("public \${$attributeName} = {$replacement};");
    }

    private function addContent($content)
    {
        $body = explode(PHP_EOL, $this->body);
        while (strpos($body[count($body) - 1], '}') === false) {
            array_pop($body);
        }
        array_push($body, $body[count($body) - 1]);
        $body[count($body) - 2] =$content;
        return $this->body = implode(PHP_EOL, $body);
    }
} 