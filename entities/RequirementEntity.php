<?php

class RequirementEntity
{
    public $id;
    public $text;
    public $isValid;
    public $feedback;

    public function __construct($id, $text, $isValid, $feedback)
    {
        $this->id = $id;
        $this->text = $text;
        $this->isValid = $isValid;
        $this->feedback = $feedback;
    }
}
