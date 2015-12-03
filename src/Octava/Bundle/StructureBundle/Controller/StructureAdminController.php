<?php

namespace Octava\Bundle\StructureBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StructureAdminController extends CRUDController
{
    public function refreshCacheAction()
    {
        $this->get('octava_structure.structure_manager')->update();
        $this->get('session')->getFlashBag()->add("success", "admin.refresh.complete");

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
