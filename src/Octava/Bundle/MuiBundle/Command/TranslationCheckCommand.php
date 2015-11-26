<?php
namespace Octava\Bundle\MuiBundle\Command;

use Octava\Bundle\MuiBundle\Translation\AbstractCheck;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationCheckCommand extends ContainerAwareCommand
{
    const TYPE_PLACEHOLDERS = 'placeholders';
    const TYPE_FILE_STRUCTURE = 'file_structure';
    const TYPE_DB_CONTENT = 'db_content';

    protected $types = [
        self::TYPE_PLACEHOLDERS => 'octava_mui.translation_check.placeholders',
        self::TYPE_FILE_STRUCTURE => 'octava_mui.translation_check.file_structure',
        self::TYPE_DB_CONTENT => 'octava_mui.translation_check.db_content',
    ];

    protected function configure()
    {
        $this->setName('octava:mui:translation:validate')
            ->setDescription('Validate translations')
            ->addArgument(
                'type',
                InputArgument::IS_ARRAY,
                'Validate type (default: all)',
                array_keys($this->types)
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return int
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = true;

        foreach ($this->types as $type => $serviceName) {
            /** @var AbstractCheck $check */
            $check = $this->getContainer()->get($serviceName);
            $check->getLogger()->pushHandler(new ConsoleHandler($output));
            $check->getLogger()->debug('Run checker', ['class' => get_class($check)]);
            $res = $check->execute();
            if (0 == $res) {
                $check->getLogger()->info('<info>Everything ok</info>');
            }
            $result = $result && !$res;
        }

        if (!$result) {
            throw new \RuntimeException(
                'Something wrong with translations.'
                .'Please run "'.$this->getName().'" manually with verbose mode'
            );
        }

        return 0;
    }
}
