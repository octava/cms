<?php
namespace Octava\Bundle\MuiBundle\Translation\Check;

use Octava\Bundle\MuiBundle\Translation\AbstractCheck;
use Symfony\Component\Yaml\Parser;

class FileStructure extends AbstractCheck
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @return string
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    public function execute()
    {
        $rootDir = $this->getRootDir();

        $files = glob($rootDir.'/src/*/*/*/translations/*.yml');
        $files = array_merge_recursive($files, glob($rootDir.'/vendor/octava/*/*/*/*/translations/*.yml'));
        $files = array_merge_recursive($files, glob($rootDir.'/app/*/*/translations/*.yml'));

        $data = $this->sort($files);

        $result = 0;
        $totalLostTranslations = 0;
        foreach ($data as $bundleName => $domain) {
            foreach ($domain as $translations) {
                $parser = new Parser();
                $eng = $parser->parse(file_get_contents($translations['en']));
                $rus = $parser->parse(file_get_contents($translations['ru']));


                if (is_array($eng)) {
                    $engKeys = array_keys($eng);
                } else {
                    $engKeys = [];
                }
                if (is_array($rus)) {
                    $rusKeys = array_keys($rus);
                } else {
                    $rusKeys = [];
                }

                $engDiff = array_diff($rusKeys, $engKeys);
                $rusDiff = array_diff($engKeys, $rusKeys);

                if ($engDiff) {
                    $this->getLogger()->error(sprintf('%s - %s', $bundleName, $translations['en']));
                    $this->getLogger()->error(sprintf('%s', print_r($engDiff, true)));

                    $totalLostTranslations += count($engDiff);
                    $result = 1;
                }

                if ($rusDiff) {
                    $this->getLogger()->error(sprintf('%s - %s', $bundleName, $translations['ru']));
                    $this->getLogger()->error(sprintf('%s', print_r($rusDiff, true)));

                    $totalLostTranslations += count($rusDiff);
                    $result = 1;
                }
            }
        }

        if ($totalLostTranslations) {
            $this->getLogger()->error(sprintf('Total lost translation keys - %d', $totalLostTranslations));
        }

        return $result;
    }

    protected function sort(array $files)
    {
        $result = [];
        foreach ($files as $file) {
            $bundleName = substr($file, 0, strpos($file, '/Resources'));
            $bundleName = substr($bundleName, strrpos($bundleName, '/') + 1);

            $res = preg_match('/(.*)\.(..)\.yml/', basename($file), $matches);
            if ($res) {
                $domain = $matches[1];
                $lang = $matches[2];

                if (empty($result[$bundleName][$domain])) {
                    $result[$bundleName][$domain] = [];
                }

                $result[$bundleName][$domain][$lang] = $file;
            }
        }

        return $result;
    }
}
