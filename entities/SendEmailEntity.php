<?php

class SendEmailEntity
{
    public string $email;
    public string $Subject;
    public string $Body;    

    public function __construct(string $email, string $Subject, string $Body)
    {
        $this->email = $email;
        $this->Subject = $Subject;
        $this->Body = $Body;
    }
}
