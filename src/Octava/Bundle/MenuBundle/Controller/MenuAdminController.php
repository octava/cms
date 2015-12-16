<?php

namespace Octava\Bundle\MenuBundle\Controller;

use Octava\Bundle\MenuBundle\Admin\MenuAdmin;
use Octava\Bundle\MenuBundle\Command\ResetProxyCommand;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MenuAdminController extends CRUDController
{
    public function importAction()
    {
        /** @var MenuAdmin $admin */
        $admin = $this->admin;
        $menuParentId = $admin->getFilteredParentId();
        $location = $admin->getFilteredLocation();
        $importService = $this->get('octava_menu.helper.import_from_structure');
        $importService->import($menuParentId, $location);
        $newItems = $importService->getUpdatedItems();

        $message = $this->get('translator')
            ->trans(
                'admin.import_flash_message',
                ['%count%' => count($newItems)],
                'OctavaMenuBundle'
            );
        foreach ($newItems as $item) {
            $message .= ' - '.$item->getTitle().'<br />';
        }

        $this->addFlash('success', $message);

        $admin->clearCache();

        return new RedirectResponse($admin->generateUrl('list'));
    }

    public function refreshCacheAction()
    {
        $command = new ResetProxyCommand();
        $command->setContainer($this->container);
        $input = new ArrayInput(['-b' => true]);
        $output = new NullOutput();

        $errorMessage = '';
        try {
            $resultCode = $command->run($input, $output);
            if ($resultCode) {
                $errorMessage = 'Something wrong';
            } else {
                $this->addFlash('success', 'Refresh complete');
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
        if ($errorMessage) {
            $this->addFlash('error', $errorMessage);
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
