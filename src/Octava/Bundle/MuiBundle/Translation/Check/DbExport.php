<?php
namespace Octava\Bundle\MuiBundle\Translation\Check;

use Octava\Bundle\MuiBundle\Translation\AbstractCheck;

class DbExport extends AbstractCheck
{
    /**
     * Import translations from DB to XLIFF format
     * for English and Russian languages, and compare imported items.
     */
    public function execute()
    {
        return 0;
//        $xmlRu = simplexml_load_string($this->getXliffData('ru'));
//        $xmlEn = simplexml_load_string($this->getXliffData('en'));
//
//        $dataRu = $this->getDataFromXmlObject($xmlRu);
//        $dataEn = $this->getDataFromXmlObject($xmlEn);
//
//        $missingEn = $this->searchMissing($dataRu, $dataEn);
//        $missingRu = $this->searchMissing($dataEn, $dataRu);
//
//        if (count($missingEn) > 0) {
//            $this->getLogger()->error('EN errors:', $missingEn);
//        }
//
//        if (count($missingRu) > 0) {
//            $this->getLogger()->error('RU errors:', $missingRu);
//        }
//
//        $this->getLogger()->info('<info>EN</info> - found errors: '.count($missingEn));
//        $this->getLogger()->info('<info>RU</info> - found errors: '.count($missingRu));
    }

//    protected function getXliffData($locale)
//    {
//        /** @var \Robo\TranslationBundle\Helper\XliffWriter $writer */
//        $writer = $this->getContainer()->get('robo_translation.helper.xliff_writer');
//
//        /** @var \Robo\TranslationBundle\Helper\Translation $helper */
//        $helper = $this->getContainer()->get('robo_translation.helper.translation');
//
//        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
//        $domains = $em->getRepository('RoboTranslationBundle:Translation')->getAllDomains();
//
//        $catalogue = new MessageCatalogue($locale);
//        $helper->fillExtendedCatalogue(
//            $catalogue,
//            $locale,
//            $locale,
//            $domains,
//            false
//        );
//
//        return $writer->getTranslations($catalogue);
//    }
//
//    protected function getDataFromXmlObject($xmlObject)
//    {
//        $data = [];
//        foreach ($xmlObject->file->group as $group) {
//            foreach ($group as $label) {
//                $data[strval($label['id'])] = [
//                    'id' => strval($label['id']),
//                    'group' => strval($group['resname']),
//                    'resname' => strval($label['resname']),
//                    'target' => strval($label->target),
//                ];
//            }
//        }
//
//        return $data;
//    }
//
//    protected function searchMissing($dataSource, $dataTarget)
//    {
//        $missing = [];
//        foreach ($dataSource as $key => $value) {
//            if (!isset($dataTarget[$key])) {
//                $missing[] = $value;
//            }
//        }
//
//        return $missing;
//    }
}
