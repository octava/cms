<?php
namespace Octava\Bundle\MuiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationCheckCommand extends ContainerAwareCommand
{
    const TYPE_ALL = 'all';
    const TYPE_PLACEHOLDERS = 'placeholders';
    const TYPE_FILE_STRUCTURE = 'file_structure';
    const TYPE_DB_EXPORT = 'db_export';
    const TYPE_DB_CONTENT = 'db_content';

    protected $types = [
        self::TYPE_ALL,
        self::TYPE_PLACEHOLDERS,
        self::TYPE_FILE_STRUCTURE,
        self::TYPE_DB_EXPORT,
        self::TYPE_DB_CONTENT,
    ];

    protected function configure()
    {
        $this->setName('octava:mui:translation:validate')
            ->setDescription('Validate translations')
            ->addArgument(
                'type',
                InputArgument::IS_ARRAY,
                'Validate type (default: all)',
                self::TYPE_ALL
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //@todo: ops
    }
}
