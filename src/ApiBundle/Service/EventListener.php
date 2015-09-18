<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 12.09.15
 * Time: 10:50
 */

namespace ApiBundle\Service;

use ApiBundle\Entity\IProject;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventListener {

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
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args) {

        $entity = $args->getEntity();

        if($entity instanceof IProject) {
            // Build infrastructure around project
            $this->buildProjectInfrastructure($entity);
        }

    }

    public function postRemove(LifecycleEventArgs $args) {

        $entity = $args->getEntity();

        if($entity instanceof IProject) {
            // Destroy infrastructure around project
            $this->destroyProjectInfrastructure($entity);
        }

    }

    private function buildProjectInfrastructure(IProject $project) {
        $service = $this->getService();
        $service->setProject($project);
        $service->buildProjectInfrastructure();
    }

    private function destroyProjectInfrastructure(IProject $project) {
        $service = $this->getService();
        $service->setProject($project);
        $service->destroyProjectInfrastructure();
    }

    /**
     * @return \ApiBundle\Service\ProjectService
     */
    private function getService() {
        return $this->_container->get('api.project.service');
    }
}
