<?php

use PHPEmul\OOEmulator;

function mysqli_result_fetch_object_mock($emul, $class, $constructor_args){
    $classname = strtolower($class->value->value);
    $class_obj = $emul->new_user_object($classname, [], true);
    foreach($class_obj->properties as &$property){
        $property = new \PHPEmul\SymbolicVariable();
    }
    return $class_obj;
}
