<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 10.09.15
 * Time: 13:29
 */

namespace ApiBundle\Tests\Service\Structure;

use ApiBundle\Service\Structure\HTML;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class HTMLStructureTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var vfsStreamDirectory
     */
    protected $_root;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_templateEngine;

    public function setUp() {
        // We'll use vfsStream to mock filesystem
        $this->_root = vfsStream::setup();

        // Mock template engine
        $this->_templateEngine = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->disableOriginalConstructor()
            ->getMock();

    }

    public function testBuild() {
        // arrange
        $content = "<html><body></body></html>";

        $this->_templateEngine->expects($this->any())
            ->method('render')
            ->with($this->anything())
            ->will($this->returnValue($content));

        $structure = $this->createStructure(vfsStream::url('root/test_build'));

        // act
        $structure->build();

        // assert
        $this->assertEquals(
            array(
                'root' => array(
                    'test_build' => array(
                        'index.html' => $content,
                    ),
                )
            ),
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );

    }

    public function testDestroy() {
        // create file
        $this->_root = vfsStream::setup('root', null, array(
            'test_destroy' => array(
                'index.html' => '',
            ),
        ));

        $structure = $this->createStructure(vfsStream::url('root/test_destroy'));

        // act
        $structure->destroy();

        // assert
        $this->assertEquals(
            array(
                'root' => array(
                )
            ),
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );

    }

    /**
     * @return HTML
     */
    protected function createStructure($path) {
        $structure = new HTML();
        $structure->setPath($path);
        $structure->setTemplating($this->_templateEngine);
        return $structure;
    }

}
