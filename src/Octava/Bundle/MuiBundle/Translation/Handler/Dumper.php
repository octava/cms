<?php
namespace Octava\Bundle\MuiBundle\Translation\Handler;

use Octava\Bundle\MuiBundle\TranslationManager;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class Dumper
 * @package Octava\Bundle\MuiBundle\Translation\Handler
 */
class Dumper implements DumperInterface
{
    /**
     * @var TranslationManager
     */
    protected $translationManager;

    public function __construct(TranslationManager $translationManager)
    {
        $this->translationManager = $translationManager;
    }

    /**
     * Dumps the message catalogue.
     *
     * @param MessageCatalogue $messages The message catalogue
     * @param array $options Options that are used by the dumper
     */
    public function dump(MessageCatalogue $messages, $options = [])
    {
        $this->translationManager->saveTranslations($messages, false, true);
    }
}
