<?php
namespace Sinergi\Core;

use Sinergi\Core\Registry\ComponentRegistryTrait;
use Symfony\Component\Console\Command\Command as SynfonyCommand;
use Symfony\Component\Console\Helper\DialogHelper;

abstract class Command extends SynfonyCommand
{
    use ComponentRegistryTrait;

    /**
     * @var DialogHelper
     */
    protected $dialog;

    public function __construct()
    {
        $this->dialog = new DialogHelper;
        parent::__construct();
    }
}
