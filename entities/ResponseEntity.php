<?php

class ResponseEntity
{
    public $userId;
    public $surveyQuestionId;
    public $response;

    public function __construct($userId, $surveyQuestionId, $response)
    {
        $this->userId = $userId;
        $this->surveyQuestionId = $surveyQuestionId;
        $this->response = $response;
    }
}
