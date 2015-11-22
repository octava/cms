<?php
namespace Octava\Bundle\AdministratorBundle\Command;

use Octava\Bundle\AdministratorBundle\Annotation\AclActions;
use Octava\Bundle\AdministratorBundle\Annotation\Hidden;
use Octava\Bundle\AdministratorBundle\Entity\Resource as EntityResource;
use Octava\Bundle\AdministratorBundle\Entity\Resource;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportAclResourcesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('octava:administrator:import-acl-resources');
        $this->setDescription('Imports resources and actions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminPool = $this->getContainer()->get('sonata.admin.pool');
        $annotationReader = $this->getContainer()->get('annotation_reader');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $sort = 1;

        $groups = [];
        $adminGroups = $adminPool->getAdminGroups();
        foreach ($adminGroups as $key => $value) {
            foreach ($value['items'] as $item) {
                if (is_array($item) && array_key_exists('admin', $item)) {
                    $groups[$item['admin']] = [$value['label'], $value['label_catalogue']];
                }
            }
        }

        foreach ($adminPool->getAdminServiceIds() as $serviceId) {
            $admin = $adminPool->getInstance($serviceId);
            $reflection = new \ReflectionObject($admin);
            $label = $admin->getLabel();

            $annotation = $annotationReader->getClassAnnotation(
                $reflection,
                'Octava\\Bundle\\AdministratorBundle\\Annotation\\AclActions'
            );

            if (empty($annotation)) {
                $annotation = new AclActions();
            }

            $hiddenAnnotation = $annotationReader->getClassAnnotation(
                $reflection,
                'Octava\\Bundle\\AdministratorBundle\\Annotation\\Hidden'
            );

            if (empty($hiddenAnnotation)) {
                $hiddenAnnotation = new Hidden();
                $hiddenAnnotation->value = false;
            }

            $actions = $annotation->value;
            if (!is_array($actions)) {
                $actions = [$actions];
            }
            $hidden = $hiddenAnnotation->value;

            $resource = get_class($admin);

            /** @var EntityResource[] $rows */
            $rows = $em->getRepository('OctavaAdministratorBundle:Resource')->findBy(
                ['resource' => $resource]
            );

            /** @var EntityResource[] $dbActions */
            $dbActions = [];

            foreach ($rows as $row) {
                $dbActions[$row->getAction()] = $row;
            }

            $group = 'Admin';
            $groupDomain = 'OctavaAdministratorBundle';

            if (!empty($groups[$serviceId])) {
                $group = $groups[$serviceId][0];
                $groupDomain = $groups[$serviceId][1];
            }

            foreach ($actions as $action) {
                if (empty($dbActions[$action])) {
                    $obj = new Resource();
                    $obj->setResource($resource);
                    $obj->setAction($action);
                    $obj->setLabel($label);
                    $obj->setSort($sort);
                    $obj->setGroupLabel($group);
                    $obj->setGroupLabelDomain($groupDomain);
                    $obj->setHidden($hidden);
                    $em->persist($obj);
                    $output->writeln(
                        "<fg=green>Insert</fg=green> ACL resource: <fg=cyan>" .
                        $obj->getResource() .
                        "</fg=cyan> <comment>" . $obj->getAction() . "</comment>"
                    );
                } else {
                    $dbActions[$action]->setLabel($label);
                    $dbActions[$action]->setSort($sort);
                    $dbActions[$action]->setGroupLabel($group);
                    $dbActions[$action]->setGroupLabelDomain($groupDomain);
                    $dbActions[$action]->setHidden($hidden);
                    $em->persist($dbActions[$action]);
                }
                unset($dbActions[$action]);
            }

            foreach ($dbActions as $row) {
                $output->writeln(
                    "<fg=red>Remove</fg=red> ACL resource: <fg=cyan>"
                    . $row->getResource()
                    . "</fg=cyan> <comment>" . $row->getAction() . "</comment>"
                );
                $em->remove($row);
            }
            $sort++;
        }

        $em->flush();
    }
}
