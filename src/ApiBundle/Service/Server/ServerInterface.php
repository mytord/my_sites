<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 10.09.15
 * Time: 13:22
 */

namespace ApiBundle\Service\Server;

interface ServerInterface {

    /**
     * @var $path
     * @return mixed
     */
    public function setProjectPath($path);

    /**
     * @return mixed
     */
    public function getProjectPath();

    /**
     * Create virtual host configuration
     * @var $name
     * @return void
     */
    public function createVirtualHost();

    /**
     * Destroy virtual host configuration
     * @param $name
     * @return mixed
     */
    public function destroyVirtualHost();

    /**
     * Reload server configuration
     * @return void
     */
    public function reloadConfiguration();

}