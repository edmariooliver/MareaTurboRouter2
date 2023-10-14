<?php
declare(strict_types=1);

namespace MareaTurbo;
use MareaTurbo\MareaTurboException;
use ReflectionClass;
use MareaTurbo\RouteParameters;

class Controller
{
    private String $classController;
    private Mixed $controller;

    public function __construct(String $classController)
    {
        $this->classController = $classController;
    }

    private function build() : void
    {
        $reflectionControllerInstance = new ReflectionClass($this->classController);
        $this->controller = $this->recursiveDependenciesBuild($reflectionControllerInstance);
    }

    public function runMethod(String $method, RouteParameters $routeParams) : mixed
    {
        if($this->isValidMethod($method) == false) {
            throw new MareaTurboException("Method not found");
        }
        $this->build();
        return $this->controller->$method($routeParams);
    }

    private function isValidMethod(String $method, ) : bool
    {
        return method_exists($this->classController, $method);
    }

    private function recursiveDependenciesBuild(ReflectionClass $reflectionControllerInstance) : mixed
    {
        if($reflectionControllerInstance->getConstructor() == null) {
            return $reflectionControllerInstance->newInstance();
        }
        
        $dependencies = $reflectionControllerInstance->getConstructor()->getParameters();
        if($dependencies == null) {
            return $reflectionControllerInstance->newInstance();
        }

        $arguments = array();
        foreach($dependencies as $dependency){
            $depencyClassName = $dependency->getType()->getName();
            $dependencyReflectionClass = new ReflectionClass($depencyClassName);
            $arguments[] = $this->recursiveDependenciesBuild($dependencyReflectionClass);
        }
        return $reflectionControllerInstance->newInstanceArgs($arguments);
    }
}