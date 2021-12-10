<?php


namespace PHPEmul;


class SymbolicVariable
{
    public string $variable_name;
    public string $variable_value;
    public $isset;

    public function __construct($variable_name='',$variable_value = '*', $isset=null)
    {
        /*
         * For normal SymbolicVariables, isset also returns symbolic.
         * In extended logs emulation mode, certain Symbolic parameters Symbolic but isset returns true
         */
        $this->isset = $isset ?? new SymbolicVariable('isset', '*', true);
        $this->variable_name = $variable_name;
        $this->variable_value = $variable_value;
    }

    public function __toString()
    {
        /* Default value is "*" which works with regex for file inclusion
         * May confuse logging in other places
         */
        return $this->variable_value;
    }
}