<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 08.09.15
 * Time: 13:21
 */

namespace ApiBundle\Handler;

use ApiBundle\Entity\IProject;
use ApiBundle\Entity\Project;
use ApiBundle\Exception\InvalidFormException;
use ApiBundle\Form\ProjectType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;

class ProjectHandler implements IProjectHandler
{

    protected $_objectManager;

    protected $_entityClass;

    /** @var \Doctrine\Common\Persistence\ObjectRepository  */
    protected $_repository;

    protected $_formFactory;

    public function __construct(EntityManager $om, $entityClass, FormFactoryInterface $formFactory) {
        $this->_objectManager = $om;
        $this->_entityClass = $entityClass;
        $this->_repository = $this->_objectManager->getRepository($this->_entityClass);
        $this->_formFactory = $formFactory;
    }

    /**
     * @param $id
     * @return Project|null
     */
    public function get($id) {
        return $this->_repository->find($id);
    }

    /**
     * @param array $params
     * @return IProject
     */
    public function make(array $params) {
        return $this->processForm($this->createProject(), $params, 'POST');
    }

    /**
     * @param IProject $project
     * @param array $params
     * @return IProject
     */
    public function put(IProject $project, array $params) {
        return $this->processForm($project, $params, 'PUT');
    }

    /**
     * @param IProject $project
     * @param array $params
     * @return IProject
     */
    public function patch(IProject $project, array $params) {
        return $this->processForm($project, $params, 'PATCH');
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param null $order
     * @return array
     */
    public function all($limit = 20, $offset = 0, $order = null) {
        return $this->_repository->findBy(array(), $this->parseSorting($order), $limit, $offset);
    }

    /**
     * @return int
     */
    public function countAll() {
        return $this->_objectManager
            ->createQuery("SELECT COUNT(p.id) FROM ApiBundle:Project p")
            ->getSingleScalarResult();
    }

    /**
     * @param IProject $project
     * @return IProject
     */
    public function delete(IProject $project) {
        return $this->processDelete($project);
    }

    /**
     * @return IProject
     */
    protected function createProject() {
        return new Project();
    }

    /**
     * @param IProject $project
     * @param array $params
     * @param string $method
     * @return mixed
     */
    protected function processForm(IProject $project, array $params, $method = 'PUT') {

        $form = $this->_formFactory->create(new ProjectType(), $project, array('method' => $method));
        $form->submit($params, 'PATCH' !== $method);

        if ($form->isValid()) {

            $project = $form->getData();
            $this->_objectManager->persist($project);
            $this->_objectManager->flush();

            return $project;

        }

        throw new InvalidFormException('Invalid submitted data', $form);

    }

    protected function processDelete(IProject $project) {

        $this->_objectManager->remove($project);
        $this->_objectManager->flush();

        return $project;
    }

    /**
     * @param $sorting
     * @return array
     */
    protected function parseSorting($sorting) {
        if(is_string($sorting)) {
            $result = array();

            // remove spaces
            $sorting = preg_replace('/\,\s/', ',', $sorting);

            foreach (explode(',', $sorting) as $value) {
                list($sort, $order) = explode(' ', $value);
                $result[$sort] = $order;
            }

            return $result;
        }

        return $sorting;
    }


}