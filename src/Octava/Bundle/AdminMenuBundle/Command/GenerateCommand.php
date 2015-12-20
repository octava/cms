<?php
namespace Octava\Bundle\AdminMenuBundle\Command;

use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('octava:admin-menu:generate')
            ->setDescription('Generate admin menu from sonata admin definitions');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $poll = $this->getContainer()->get('sonata.admin.pool');

        $translator = $this->getContainer()->get('translator');
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repository = $manager->getRepository('OctavaAdminMenuBundle:AdminMenu');
        $translation = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('GedmoTranslatable:Translation');
        foreach ($poll->getAdminGroups() as $name => $group) {
            $title = $translator->trans($group['label'], [], $group['label_catalogue']);
            $parent = $repository->findOneBy(['title' => $title, 'type' => AdminMenu::TYPE_FOLDER]);

            if ($output->isVerbose()) {
                $output->writeln(sprintf('Folder "%s"', $title));
            }

            if (!$parent) {
                $parent = new AdminMenu();
                $parent->setTitle($title);
                $parent->setType(AdminMenu::TYPE_FOLDER);

                if ($output->isVerbose()) {
                    $output->writeln(sprintf('Generate "%s" folder', $title));
                }

                $translation->translate($parent, 'title', 'en', $title);
            }
            $manager->persist($parent);

            foreach ($group['items'] as $item) {
                if ($output->isVerbose()) {
                    $output->writeln(sprintf('Module "%s"', $item['admin']));
                }

                $serviceId = $item['admin'];
                $title = $translator->trans($item['label'], [], $group['label_catalogue']);

                $menu = $repository->findOneBy(
                    [
                        'serviceId' => $serviceId,
                    ]
                );
                if (!$menu) {
                    $menu = new AdminMenu();
                    $menu->setTitle($title);
                    $menu->setType(AdminMenu::TYPE_MODULE);
                    $menu->setServiceId($serviceId);
                    $menu->setParent($parent);

                    $translation->translate($menu, 'title', 'en', $title);
                    $manager->persist($menu);

                    if ($output->isVerbose()) {
                        $output->writeln(sprintf('Generate "%s" item', $title));
                    }
                }
            }
        }

        $manager->flush();
    }
}
