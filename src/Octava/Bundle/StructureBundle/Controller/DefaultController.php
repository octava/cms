<?php
namespace Octava\Bundle\StructureBundle\Controller;

use Octava\Bundle\StructureBundle\Entity\Structure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($id)
    {
        /** @var Structure $structure */
        $structure = $this->get('doctrine.orm.entity_manager')
            ->getRepository('OctavaStructureBundle:Structure')
            ->getById($id);

        $template = $structure->getTemplate()
            ?: $this->get('octava_structure.config.structure_config')
                ->getDefaultTemplate();

        return $this->render($template, ['structure' => $structure]);
    }

    public function error404Action()
    {
        throw $this->createNotFoundException();
    }
}
