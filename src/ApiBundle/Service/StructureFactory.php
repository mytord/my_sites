<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 11.09.15
 * Time: 23:01
 */

namespace ApiBundle\Service;


use ApiBundle\Entity\IProject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ApiBundle\Service\Structure\StructureInterface;

class StructureFactory implements StructureFactoryInterface
{

    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci) {
        $this->_container = $ci;
    }

    /**
     * Simple strategy
     * @param IProject $project
     * @return StructureInterface
     */
    public function createBuilder(IProject $project) {
        $structure = null;

        switch($project->getTypeId()) {

            default:
                $structure = $this->createHtmlStructure();
        }

        // setup path based on project name
        $path = $this->_container->getParameter('api.project.service.structure.path') . DIRECTORY_SEPARATOR . $project->getAlias();
        $structure->setPath($path);

        return $structure;
    }

    /**
     * @return StructureInterface
     */
    protected function createHtmlStructure() {
        return $this->_container->get('api.project.structure.html');
    }

}