<?php

class userLog
{
    private $user_log_file;
    public const EMAIL_SENDER ='From:NAME<SENDER EMAIL ADDRESS>';

    public function __construct($user_log_file)
    {
        $this->user_log_file = $user_log_file;
    }

    public function sendUserErrorByEmail($errorMessage, $userName, $email)
    {
        $logmessage =  '[' . date('Y-m-d h:i:s') .' '.$userName.'] '. print_r($errorMessage, true) . "\n";
        error_log($logmessage, 1, $email, 'Subject:'.self::EMAIL_SENDER);
    }

    public function setUserError($errorMessage, $userName)
    {
        $logmessage =  '[' . date('Y-m-d h:i:s') .' '.$userName.'] '. print_r($errorMessage, true) . "\n";
        error_log($logmessage, 3, $this->user_log_file);
    }

    public function truncateUserLogFile()
    {
        $fileHandler = fopen($this->user_log_file, 'r+');
        ftruncate($fileHandler, 0);
        fclose($fileHandler);
    }
}
