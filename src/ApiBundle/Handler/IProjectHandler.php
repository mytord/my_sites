<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 11.09.15
 * Time: 14:32
 */
namespace ApiBundle\Handler;

use ApiBundle\Entity\IProject;
use ApiBundle\Entity\Project;

interface IProjectHandler
{
    /**
     * @param $id
     * @return Project|null
     */
    public function get($id);

    /**
     * @param array $params
     * @return IProject
     */
    public function make(array $params);

    /**
     * @param IProject $project
     * @param array $params
     * @return IProject
     */
    public function put(IProject $project, array $params);

    /**
     * @param IProject $project
     * @param array $params
     * @return IProject
     */
    public function patch(IProject $project, array $params);

    /**
     * @param int $limit
     * @param int $offset
     * @param null $order
     * @return array
     */
    public function all($limit = 20, $offset = 0, $order = null);

    /**
     * @param IProject $project
     * @return IProject
     */
    public function delete(IProject $project);
}