<?php

namespace Smart\Core;

use Symfony\Component\Console\Command\Command as SynfonyCommand;
use Symfony\Component\Console\Helper\QuestionHelper;

abstract class Command extends SynfonyCommand
{
    use ComponentRegistryTrait;

    /**
     * @var QuestionHelper
     * @deprecated
     */
    protected $dialog;

    /**
     * @var QuestionHelper
     */
    protected $question;

    public function __construct()
    {
        $this->dialog = $this->question = new QuestionHelper();
        parent::__construct();
    }

    public function isVagrant()
    {
        $class = new \ReflectionClass($this);
        return $class->hasConstant("RUN_VAGRANT");
    }
}
