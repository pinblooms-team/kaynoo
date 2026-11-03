<?php

namespace Tecksky\Optimise\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TruncateLogTable
 */
class CleanLogTable extends Command
{
    /**
     * @var \Tecksky\Optimise\Model\Database
     */
    private $database;

    /**
     * TruncateLogTable constructor.
     * @param \Tecksky\Optimise\Model\Database $database
     */
    public function __construct(
        \Tecksky\Optimise\Model\Database $database
    )
    {
        $this->database = $database;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('tecksky:log:table:cleanup')
            ->setDescription('Clear table log')
            ->setDefinition([]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->database->truncateLogTable();
            $output->writeln("<info>Table log successfully cleared.</info>");
        } catch (\Exception $exception) {
            $output->writeln("<error>{$exception->getMessage()}</error>");
        }
    }
}