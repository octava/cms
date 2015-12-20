<?php
namespace Octava\Bundle\AdministratorBundle\Command;

use Octava\Bundle\AdministratorBundle\Entity\Group;
use Octava\Bundle\AdministratorBundle\Entity\Resource;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GrantAllForGroupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('octava:administrator:grant-all')
            ->setDescription('Grant all for group')
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Group name',
                ['admin']
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $groupRepository = $entityManager
            ->getRepository('OctavaAdministratorBundle:Group');
        $resourceRepository = $entityManager
            ->getRepository('OctavaAdministratorBundle:Resource');

        $groupNames = $input->getOption('group');
        foreach ($groupNames as $groupName) {
            /** @var Group $group */
            $group = $groupRepository->findOneBy(['name' => $groupName]);
            if (!$group) {
                $io->error(sprintf('Group "%s" not found', $groupName));
                continue;
            }

            $rows = [];

            $existsResources = $group->getResources();
            /** @var \Octava\Bundle\AdministratorBundle\Entity\Resource $resource */
            foreach ($resourceRepository->findAll() as $resource) {
                if ($existsResources->contains($resource)) {
                    continue;
                }
                $group->addResource($resource);
                $rows[] = [$resource->getResource(), $resource->getAction()];
            }

            if ($rows) {
                $io->section($groupName);
                $headers = ['Resource', 'Action'];
                $io->table($headers, $rows);
                $entityManager->persist($group);
                $entityManager->flush();
            }
        }
    }
}
