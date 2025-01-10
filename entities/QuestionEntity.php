<?php

class QuestionEntity
{
    public $nfr;
    public $variable;
    public $feedback1;
    public $value;
    public $feedback2;
    public $recomend;
    public $other_recommended_values;
    public $feedback3;
    public $validar;

    public function __construct($nfr, $variable, $feedback1, $value, $feedback2, $recomend, $other_recommended_values, $feedback3, $validar)
    {
        $this->nfr = $nfr;
        $this->variable = $variable;
        $this->feedback1 = $feedback1;
        $this->value = $value;
        $this->feedback2 = $feedback2;
        $this->recomend = $recomend;
        $this->other_recommended_values = $other_recommended_values;
        $this->feedback3 = $feedback3;
        $this->validar = $validar;
    }
}
