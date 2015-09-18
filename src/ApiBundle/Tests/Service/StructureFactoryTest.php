<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 11.09.15
 * Time: 23:14
 */

namespace ApiBundle\Tests\Service;

use ApiBundle\Service\StructureFactory;

class StructureFactoryTest extends \PHPUnit_Framework_TestCase {

    public function testPrepareShouldReturnsHtmlStructureBuilder() {

        $projectName = "Test_name";
        $path = "/path/to/project";

        // html structure
        $structure = $this->getMock('ApiBundle\Service\Structure\HTML');

        // di container
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->will($this->returnValue($structure));
        $container->expects($this->once())
            ->method('getParameter')
            ->will($this->returnValue($path));

        // project
        $project = $this->getMock('ApiBundle\Entity\IProject');
        $project->expects($this->once())
            ->method('getAlias')
            ->will($this->returnValue($projectName));
        $project->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(1));

        $factory = new StructureFactory($container);
        $result = $factory->createBuilder($project);

        $this->assertEquals($result, $structure);

    }

}
