<?php


namespace malmax\emul;


class SymbolicVariable
{
    public string $variable_name;
    public string $variable_value;

    public function __construct($variable_name='',$variable_value = '*')
    {
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