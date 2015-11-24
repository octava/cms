<?php

namespace Octava\Bundle\MuiBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OfficeAdminController extends CRUDController
{
    public function clearCacheAction()
    {
        if (!$this->admin->isGranted('CLEAR_CACHE')) {
            throw new AccessDeniedException();
        }

        $command = $this->getParameter('kernel.root_dir') . '/console cache:clear';
        $process = new Process($command);
        $process->setTimeout(3600);
        try {
            $process->run();
            $this->addFlash('sonata_flash_success', $this->admin->trans('admin.cache_clear_success'));
        } catch (RuntimeException $e) {
            $this->addFlash('sonata_flash_error', $e->getMessage());
        }
        return $this->redirectToRoute('admin_robo_office_office_list');
    }
}
