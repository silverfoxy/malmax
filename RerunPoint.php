<?php


namespace malmax;


class RerunPoint
{
    public $snapshot;
    public array $covered_conditions;
    public string $current_file;

    public function __construct($snapshot, $covered_conditions, $current_file)
    {
        $this->snapshot = $snapshot;
        $this->covered_conditions[] = $covered_conditions;
        $this->current_file = $current_file;
    }
}