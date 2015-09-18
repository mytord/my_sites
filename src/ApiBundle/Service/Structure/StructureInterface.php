<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 10.09.15
 * Time: 13:19
 */
namespace ApiBundle\Service\Structure;

interface StructureInterface {

    /**
     * Builds project file structure
     * @return void
     */
    public function build();

    /**
     * Destroy project file structure
     * @return void
     */
    public function destroy();

    /**
     * Setup root path
     * @var $path
     * @return void
     */
    public function setPath($path);


    /**
     * Path to structure
     * @return string
     */
    public function getPath();


}