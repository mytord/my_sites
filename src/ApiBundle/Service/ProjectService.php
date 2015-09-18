<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 10.09.15
 * Time: 13:25
 */

namespace ApiBundle\Service;

use ApiBundle\Entity\IProject;
use ApiBundle\Service\Server\ServerInterface;
use ApiBundle\Handler\IProjectHandler;

class ProjectService {

    /**
     * @var IProject
     */
    protected $_project;

    /**
     * Wrapper to database interaction
     * @var IProjectHandler
     */
    protected $_projectHandler;

    /**
     * @var ServerInterface
     */
    protected $_server;

    /**
     * @var StructureFactoryInterface
     */
    protected $_structureFactory;

    /**
     * @return IProject
     */
    public function getProject()
    {
        return $this->_project;
    }

    /**
     * @param IProject $project
     */
    public function setProject(IProject $project)
    {
        $this->_project = $project;
    }

    /**
     * @return IProjectHandler
     */
    public function getProjectHandler()
    {
        return $this->_projectHandler;
    }

    /**
     * @param IProjectHandler $projectHandler
     */
    public function setProjectHandler(IProjectHandler $projectHandler)
    {
        $this->_projectHandler = $projectHandler;
    }

    /**
     * @return ServerInterface
     */
    public function getServer()
    {
        return $this->_server;
    }

    /**
     * @param ServerInterface $server
     */
    public function setServer(ServerInterface $server)
    {
        $this->_server = $server;
    }

    /**
     * @return StructureFactoryInterface
     */
    public function getStructureFactory()
    {
        return $this->_structureFactory;
    }

    /**
     * @param StructureFactoryInterface $structureFactory
     */
    public function setStructureFactory($structureFactory)
    {
        $this->_structureFactory = $structureFactory;
    }

    /**
     * Create project infrastructure (virtual hosts, files, directories, dns-records)
     * @return void
     */
    public function buildProjectInfrastructure() {

        $this->checkEnvironment();

        // Create project file structure
        $structureBuilder = $this->getStructureFactory()->createBuilder($this->_project);
        $structureBuilder->build();

        // Create Virtual host
        $server = $this->getServer();
        $server->setProjectPath($structureBuilder->getPath());
        $server->createVirtualHost();

    }

    /**
     * Destroy project infrastructure
     * @return void
     */
    public function destroyProjectInfrastructure() {

        $structureBuilder = $this->getStructureFactory()->createBuilder($this->_project);
        $structureBuilder->destroy();

        $server = $this->getServer();
        $server->setProjectPath($structureBuilder->getPath());
        $server->destroyVirtualHost();


    }

    /**
     * @return void
     */
    protected function checkEnvironment() {
        if(!$this->_project) {
            throw new \InvalidArgumentException("You should setup project");
        }
    }

}