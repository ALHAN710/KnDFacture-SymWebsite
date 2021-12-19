<?php

namespace App\Message;

class UserNotificationMessage
{

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $media;

    /**
     * @var string
     */
    private $object;

    /**
     * Path File variable
     *
     * @var string
     */
    private $pathFile;

    public function __construct(int $userId, string $message, string $media, string $object, string $pathFile = '')
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->media = $media;
        $this->object = $object;
        $this->pathFile = $pathFile;
    }

    /**
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the value of message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the value of media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Get the value of object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get path File variable
     *
     * @return  string
     */
    public function getPathFile()
    {
        return $this->pathFile;
    }

    /**
     * Set path File variable
     *
     * @param  string  $pathFile  Path File variable
     *
     * @return  self
     */
    public function setPathFile(string $pathFile)
    {
        $this->pathFile = $pathFile;

        return $this;
    }
}
