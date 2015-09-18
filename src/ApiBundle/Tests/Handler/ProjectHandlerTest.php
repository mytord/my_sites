<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 08.09.15
 * Time: 13:22
 */

namespace ApiBundle\Tests;

use ApiBundle\Handler\ProjectHandler;
use ApiBundle\Entity\IProject;

class ProjectHandlerTest extends \PHPUnit_Framework_TestCase {

    const PROJECT_CLASS = 'ApiBundle\Entity\Project';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_repository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_formFactory;

    public function setUp() {

        parent::setUp();

        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        // mock object manager
        $this->_objectManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        // mock repository
        $this->_repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        // mock form factory
        $this->_formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->_objectManager->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo(static::PROJECT_CLASS))
            ->will($this->returnValue($this->_repository));
        $this->_objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo(static::PROJECT_CLASS))
            ->will($this->returnValue($class));

    }

    public function testGet() {
        $projectId = 1;

        $project = $this->createProject();

        $this->_repository->expects($this->once())->method('find')
            ->with($this->equalTo($projectId))
            ->will($this->returnValue($project));

        $this->createHandler($this->_objectManager, static::PROJECT_CLASS, $this->_formFactory)->get($projectId);
    }

    public function testMake() {

        $params = array('name' => uniqid(), 'type_id' => 1);

        // project for test
        $project = $this->createProject();
        $project->setName($params['name']);
        $project->setTypeId($params['type_id']);

        // mock form builder
        $this->_formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->createFormMock($project, true)));

        $makedProject = $this->createHandler($this->_objectManager, static::PROJECT_CLASS, $this->_formFactory)->make($params);

        // assert
        $this->assertEquals($makedProject, $project);

    }

    public function testMakeShouldThrowExceptionIfFormInvalid() {

        $this->setExpectedException('ApiBundle\Exception\InvalidFormException', 'Invalid submitted data');

        $this->_formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->createFormMock(null, false)));

        // try to make project
        $this->createHandler($this->_objectManager, static::PROJECT_CLASS,  $this->_formFactory)->make(array(
            'name' => uniqid(),
            'type_id' => 1,
        ));

    }

    public function testPut() {

        $params = array('name' => uniqid('test_put_'), 'type_id' => 1);

        $project = $this->createProject();
        $project->setName($params['name']);
        $project->setTypeId($params['type_id']);

        $this->_formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->createFormMock($project, true)));

        $puttedProject = $this->createHandler($this->_objectManager, static::PROJECT_CLASS, $this->_formFactory)->put($project, $params);
        $this->assertEquals($puttedProject, $project);

    }

    public function testPatch() {
        $params = array('type_id' => 2);
        $project = $this->createProject();
        $project->setName(uniqid('test_patch_'));
        $project->setTypeId(1);

        $this->_formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->createFormMock($project, true)));

        $patchedProject = $this->createHandler($this->_objectManager, static::PROJECT_CLASS, $this->_formFactory)->patch($project, $params);
        $this->assertEquals($patchedProject, $project);
    }

    public function testList() {

        // arrange
        $limit = 10;
        $offset = 5;
        $order = 'name asc, type desc';

        $this->_repository->expects($this->once())
                ->method('findBy')
                ->with(array(), array('name' => 'asc', 'type' => 'desc'), $limit, $offset)
                ->will($this->returnValue(array()));

        $handler = $this->createHandler($this->_objectManager, static::PROJECT_CLASS, $this->_formFactory);

        // act && assert
        $handler->all($limit, $offset, $order);

    }

    public function testDelete() {

        $project = $this->createProject();
        $project->setName(uniqid('test_delete_'));
        $project->setTypeId(1);

        $this->_objectManager->expects($this->once())
            ->method('remove')
            ->with($project);

        $this->_objectManager->expects($this->once())
            ->method('flush');

        $handler = $this->createHandler($this->_objectManager, static::PROJECT_CLASS, $this->_formFactory);
        $handler->delete($project);

    }

    public function testCountAll() {
        $handler = $this->createHandler($this->_objectManager, static::PROJECT_CLASS, $this->_formFactory);

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')->disableOriginalConstructor()->getMock();

        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->will($this->returnValue(10));

        $this->_objectManager->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $this->assertEquals(10, $handler->countAll());

    }

    /**
     * @param $data
     * @param bool $isValid
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFormMock($data = null, $isValid = true) {
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $form->expects($this->once())
            ->method('submit')
            ->with($this->anything());
        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue($isValid));

        if($data) {
            $form->expects($this->once())
                ->method('getData')
                ->will($this->returnValue($data));
        }

        return $form;

    }

    /**
     * @return IProject
     */
    protected function createProject() {
        $projectClass = static::PROJECT_CLASS;
        return new $projectClass();
    }

    protected function createHandler($objectManager, $entityClass, $formFactory) {
        return new ProjectHandler($objectManager, $entityClass, $formFactory);
    }

}
