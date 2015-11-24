<?php
namespace Octava\Bundle\MuiBundle\Command;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\Translation as GedmoTranslation;
use Octava\Bundle\MuiBundle\Entity\Translation as OctavaTranslation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AliasUpdateRelationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('octava:mui:locale:alias-update-relations')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Source locale alias')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Destination locale alias')
            ->setDescription('Change locale alias relations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $input->getOption('from');
        $to = $input->getOption('to');
        if (!$from || !$to) {
            throw new \InvalidArgumentException();
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $extRepository = $em->getRepository('\Gedmo\Translatable\Entity\Translation');
        /** @var GedmoTranslation[] $extItems */
        $extItems = $extRepository->createQueryBuilder('e')
            ->where('e.locale = :locale')
            ->setParameter('locale', $from)
            ->getQuery()->getResult();

        $extCount = 0;
        foreach ($extItems as $extItem) {
            $extItem->setLocale($to);
            $em->persist($extItem);

            $extCount++;
        }
        $em->flush();

        $output->writeln('<info>Translations updated:</info> ' . $extCount);

        $transRepository = $em->getRepository('OctavaMuiBundle:Translation');
        /** @var OctavaTranslation[] $transItems */
        $transItems = $transRepository->findAll();
        $transCount = 0;
        foreach ($transItems as $transItem) {
            $translations = $transItem->getTranslations();
            if (isset($translations[$from])) {
                $translations[$to] = $translations[$from];
                unset($translations[$from]);
                $transItem->setTranslations($translations);
                $em->persist($transItem);

                $transCount++;
            }
        }
        $em->flush();

        $output->writeln('<info>Labels updated:</info> ' . $transCount);
    }
}
