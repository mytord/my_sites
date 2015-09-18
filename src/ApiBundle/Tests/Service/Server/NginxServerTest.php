<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 10.09.15
 * Time: 17:46
 */
namespace ApiBundle\Tests\Service\Server;

use ApiBundle\Service\Server\Nginx as NginxServer;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use Symfony\Component\Filesystem\Filesystem;

class NginxServerTest extends \PHPUnit_Framework_TestCase {

    const VIRTUAL_HOST_TEMPLATE = 'dummy_template.html';

    public function testCreateVirtualHost() {

        // We'll use vfsStream to mock filesystem
        vfsStream::setup('root', null, array(
            'vhosts' => array()
        ));

        // Virtual host template content
        $virtualHostContent = "server { listen: 80; server_name: localhost; }";

        $templating = $this->createTemplatingMock();

        $templating->expects($this->once())
            ->method('render')
            ->with($this->equalTo(self::VIRTUAL_HOST_TEMPLATE))
            ->will($this->returnValue($virtualHostContent));

        // Act
        $server = $this->createServer();
        $server->setTemplating($templating);
        $server->setProjectPath('/path/to/test_project');
        $server->createVirtualHost();

        // Assert
        $this->assertEquals(
            array(
                'root' => array(
                    'vhosts' => array(
                        'test_project.conf' => $virtualHostContent
                    ),
                )
            ),
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );

    }

    public function testDestroyVirtualHost() {
        // We'll use vfsStream to mock filesystem
        vfsStream::setup('root', null, array(
            'vhosts' => array(
                'test_delete_vhost.conf' => ''
            ),
        ));

        // Act
        $server = $this->createServer();
        $server->setTemplating($this->createTemplatingMock());
        $server->setProjectPath('/path/to/test_delete_vhost');
        $server->destroyVirtualHost();

        // Assert
        $this->assertEquals(
            array(
                'root' => array(
                    'vhosts' => array(),
                )
            ),
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );

    }

    /**
     * @return NginxServer
     */
    protected function createServer() {

        $server = new NginxServer();
        $server->setVirtualHostTemplate(self::VIRTUAL_HOST_TEMPLATE);
        $server->setVirtualHostPath(vfsStream::url('root/vhosts'));
        $server->setFilesystem(new Filesystem());

        return $server;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createTemplatingMock() {
        return $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

}
