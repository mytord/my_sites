<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 11.09.15
 * Time: 23:26
 */
namespace ApiBundle\Service;

use ApiBundle\Entity\IProject;
use ApiBundle\Service\Structure\StructureInterface;

interface StructureFactoryInterface
{
    /**
     * Simple strategy
     * @param IProject $project
     * @return StructureInterface
     */
    public function createBuilder(IProject $project);
}