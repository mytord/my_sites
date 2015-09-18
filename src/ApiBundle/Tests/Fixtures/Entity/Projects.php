<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 08.09.15
 * Time: 17:16
 */

namespace ApiBundle\Tests\Fixtures\Entity;

use ApiBundle\Entity\Project;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ApiBundle\Entity\IProject;

class Projects implements FixtureInterface {

    static public $projects = array();

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createProject());
        $manager->persist($this->createProject());
        $manager->persist($this->createProject());
        $manager->persist($this->createProject());
        $manager->persist($this->createProject());
        $manager->flush();
    }

    /**
     * @return IProject
     */
    protected function createProject() {
        $project = new Project();

        $project->setName('Test fixture project');
        $project->setAlias(uniqid());
        $project->setTypeId(1);

        self::$projects[] = $project;

        return $project;

    }

}