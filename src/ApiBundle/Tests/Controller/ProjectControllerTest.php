<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 08.09.15
 * Time: 14:24
 */

namespace ApiBundle\Tests\Controller;

use ApiBundle\Service\EventListener;
use ApiBundle\Tests\Fixtures\Entity\Projects;
use Doctrine\Common\EventManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class ProjectControllerTest extends WebTestCase {

    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    protected $_client;

    public function setUp() {

        // Disable listeners that creates project infrastructure
        $this->disableProjectServiceEventListeners();

        $this->_client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'www',
            'PHP_AUTH_PW'   => 'www',
        ));

        $this->loadFixtures(array('ApiBundle\Tests\Fixtures\Entity\Projects'));

    }

    public function testJsonViewAction() {

        // arrange
        $project = array_pop(Projects::$projects);

        $route = $this->getUrl('project_view', array('id' => $project->getId(), '_format' => 'json'));

        // act
        $this->_client->request('GET', $route, array('accept' => 'application/json'));

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $content = json_decode($response->getContent());
        $this->assertTrue(property_exists($content, 'id'));
    }

    public function testJsonViewActionShouldReturns404IfProjectNotFound() {

        // arrange
        $route = $this->getUrl('project_view', array('id' => 0, '_format' => 'json'));

        // act
        $this->_client->request('GET', $route);

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $content = json_decode($response->getContent());
        $this->assertTrue(property_exists($content, 'message'));
    }

    public function testJsonCreateAction() {

        // arrange
        $route = $this->getUrl('project_create', array('_format' => 'json'));

        // act
        $this->_client->request('POST', $route, array(), array(), array('CONTENT_TYPE' => 'application/json'), json_encode(array(
            'project' => array(
                'name'        => 'New project',
                'alias'       => uniqid('new_project_'),
                'type_id'     => 1,
                'description' => 'New project!',
            )
        )));

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());

    }

    public function testJsonNewAction() {

        // act
        $this->_client->request(
            'GET',
            $this->getUrl('project_new', array('_format' => 'json')),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $this->assertTrue(property_exists(json_decode($response->getContent()), 'children'));
    }

    public function testJsonPutActionShouldModify() {

        // arrange
        $project = array_pop(Projects::$projects);

        // act
        $this->_client->request(
            'PUT',
            $this->getUrl('project_put', array('id' => $project->getId(), '_format' => 'json')),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array('project' => array('name' => 'replaced_name', 'alias' => 'replaced_alias', 'type_id' => 2)))
        );

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals($response->getStatusCode(), 204, $response->getContent());
        $this->assertTrue($response->headers->contains(
            'Location',
            $this->getUrl('project_view', array('id' => $project->getId(), '_format' => 'json'), true)
        ), $response->headers);

    }

    public function testJsonPutActionShouldCreate() {

        // act
        $this->_client->request(
            'PUT',
            $this->getUrl('project_put', array('id' => 0, '_format' => 'json')),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array('project' => array('name' => 'Test put should modify', 'alias' => uniqid('test_put_should_create_'), 'type_id' => 1)))
        );

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals($response->getStatusCode(), 201, $response->getContent());

    }

    public function testJsonPatchAction() {
        // arrange
        $project = array_pop(Projects::$projects);

        // act
        $this->_client->request(
            'PATCH',
            $this->getUrl('project_patch', array('id' => $project->getId(), '_format' => 'json')),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array('project' => array('description' => 'New description')))
        );

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals($response->getStatusCode(), 204, $response->getContent());

    }

    public function testJsonListAction() {
        // act
        $this->_client->request(
            'GET',
            $this->getUrl('project_list', array('_format' => 'json')),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals($response->getStatusCode(), 200, $response->getContent());

    }

    public function testJsonDeleteAction() {

        $project = array_pop(Projects::$projects);

        // act
        $this->_client->request(
            'DELETE',
            $this->getUrl('project_delete', array('id' => $project->getId(), '_format' => 'json')),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json')
        );

        $response = $this->_client->getResponse();

        // assert
        $this->assertEquals($response->getStatusCode(), 204, $response->getContent());

    }
    
    private function disableProjectServiceEventListeners() {
        /** @var EventManager $eventManager */
        $eventManager = $this->getContainer()->get('doctrine.orm.entity_manager')->getEventManager();

        foreach ($eventManager->getListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if($listener instanceof EventListener) {
                    $eventManager->removeEventListener($event, $listener);
                }
            }
        }
    }

}
