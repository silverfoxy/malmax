<?php

use PHPEmul\OOEmulator;

function dbl_result_fetch_object_mock($emul, $class, $constructor_args = []){
    $classname = strtolower($class->value->value);
    $class_obj = $emul->new_user_object($classname, [], true);
    foreach($class_obj->properties as $property_name => $property_value) {
        if ($property_value instanceof \PHPEmul\EmulatorObject) {
            $class_obj->properties[$property_name] = new \PHPEmul\SymbolicVariable($property_name, '*', \PhpParser\Node\Stmt\ClassLike::class, true, [$property_value], $property_value->classname);
        }
        else {
            $class_obj->properties[$property_name] = new \PHPEmul\SymbolicVariable();
        }
    }
    return $class_obj;
}
