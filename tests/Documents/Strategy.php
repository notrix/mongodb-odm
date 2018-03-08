<?php

declare(strict_types=1);

namespace Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\Document(collection="strategy") */
class Strategy
{
    /** @ODM\Id */
    public $id;

    /** @ODM\Field(type="collection") */
    public $logs = [];

    /** @ODM\EmbedMany(targetDocument="Message", strategy="set") */
    public $messages = [];

    /** @ODM\ReferenceMany(targetDocument="Task", strategy="set") */
    public $tasks = [];
}
