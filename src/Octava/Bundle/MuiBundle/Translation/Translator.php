<?php
namespace Octava\Bundle\MuiBundle\Translation;

use Doctrine\DBAL\DBALException;
use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\MessageSelector;

class Translator extends BaseTranslator
{
    /**
     * @var array
     */
    protected $resources;

    /**
     * Constructor.
     *
     * Available options:
     *
     *   * cache_dir: The cache directory (or null to disable caching)
     *   * debug:     Whether to enable debugging or not (false by default)
     *   * resource_files: List of translation resources available grouped by locale.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     * @param MessageSelector $selector The message selector for pluralization
     * @param array $loaderIds An array of loader Ids
     * @param array $options An array of options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ContainerInterface $container,
        MessageSelector $selector,
        $loaderIds = [],
        array $options = []
    ) {
        $domains = $resources = [];

        foreach ($options['resource_files'] as $locale => $files) {
            foreach ($files as $file) {
                if (preg_match('!([^.]+)\.([^.]+)\.[^\.]+$!', basename($file), $a)) {
                    $resources[] = $file;
                    $domains[] = $a[1];
                }
            }
        }

        try {
            $locales = $container->get('octava_mui.locale_manager')->getAllAliases();
            foreach ($domains as $domain) {
                foreach ($locales as $locale) {
                    if (empty($options['resource_files'][$locale])) {
                        $options['resource_files'][$locale] = [];
                    }
                    $options['resource_files'][$locale][] = $domain . '.' . $locale . '.zdb';
                }
            }
        } catch (DBALException $e) {
            //for unit test, if cache empty and db scheme not created
        }

        $this->resources = $options['resource_files'];

        parent::__construct($container, $selector, $loaderIds, $options);
    }

    /**
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }
}
