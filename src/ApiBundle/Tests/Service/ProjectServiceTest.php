<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 11.09.15
 * Time: 14:44
 */

namespace ApiBundle\Tests\Service;

use ApiBundle\Entity\IProject;
use ApiBundle\Service\ProjectService;


class ProjectServiceTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_project, $_projectHandler, $_server, $_structureFactory, $_structure;

    /** @var ProjectService */
    protected $_service;

    public function setUp() {
        $this->_project = $this->getMock('ApiBundle\Entity\IProject');

        $this->_projectHandler = $this->getMockBuilder('ApiBundle\Handler\IProjectHandler')
                                ->disableOriginalConstructor()
                                ->getMock();


        $this->_server = $this->getMock('ApiBundle\Service\Server\ServerInterface');

        $this->_structureFactory = $this->getMock('ApiBundle\Service\StructureFactoryInterface');
        $this->_structure = $this->getMock('ApiBundle\Service\Structure\StructureInterface');

        $this->_service = new ProjectService();
        $this->_service->setProjectHandler($this->_projectHandler);
        $this->_service->setProject($this->_project);
        $this->_service->setServer($this->_server);
        $this->_service->setStructureFactory($this->_structureFactory);

    }

    public function testBuildProjectInfrastructure() {

        // virtual host was created
        $this->_server->expects($this->once())
            ->method('createVirtualHost');

        // file structure was built
        $this->_structure->expects($this->once())
            ->method('build');

        $this->_structureFactory->expects($this->once())
            ->method('createBuilder')
            ->will($this->returnValue($this->_structure));

        $this->_service->setProjectHandler($this->_projectHandler);
        $this->_service->setProject($this->_project);
        $this->_service->setServer($this->_server);

        // Assert
        $this->_service->buildProjectInfrastructure();
    }

    public function testDestroyProjectInfrastructure() {

        // virtual host was destroyed
        $this->_server->expects($this->once())
            ->method('destroyVirtualHost');

        // file structure was destroyed
        $this->_structure->expects($this->once())
            ->method('destroy');

        $this->_structureFactory->expects($this->once())
            ->method('createBuilder')
            ->will($this->returnValue($this->_structure));

        $this->_service->setProjectHandler($this->_projectHandler);
        $this->_service->setProject($this->_project);
        $this->_service->setServer($this->_server);

        // Assert
        $this->_service->destroyProjectInfrastructure();
    }

}
