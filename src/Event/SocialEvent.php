<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class SocialEvent extends Event
{
    public const SEND_POST = 'app.social.send_post';
    public const SEND_PHOTO = 'app.social.send_photo';
    private mixed $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
       return $this->data;
    }
}