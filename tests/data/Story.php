<?php

namespace yiiunit\extensions\graphql\data;

use GraphQL\Utils\Utils;

class Story
{
    public $id;

    public $authorId;

    public $title;

    public $body;

    public bool $isAnonymous = false;

    public function __construct(array $data)
    {
        Utils::assign($this, $data);
    }
}
