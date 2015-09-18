<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 10.09.15
 * Time: 17:44
 */

namespace ApiBundle\Service\Server;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class Nginx implements ServerInterface {

    /**
     * @var string
     */
    protected $_virtual_host_template = 'ApiBundle:Project:nginx_vhost.html.twig';

    /**
     * @var string
     */
    protected $_virtual_host_path;

    /**
     * @var EngineInterface
     */
    protected $_templating;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $_domain;

    /**
     * @var string
     */
    protected $_projectPath;

    /**
     * @return string
     */
    public function getVirtualHostTemplate()
    {
        return $this->_virtual_host_template;
    }

    /**
     * @param string $virtual_host_template
     */
    public function setVirtualHostTemplate($virtual_host_template)
    {
        $this->_virtual_host_template = $virtual_host_template;
    }

    /**
     * @return EngineInterface
     */
    public function getTemplating()
    {
        return $this->_templating;
    }

    /**
     * @param EngineInterface $templating
     */
    public function setTemplating($templating)
    {
        $this->_templating = $templating;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->_filesystem;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem($filesystem)
    {
        $this->_filesystem = $filesystem;
    }

    /**
     * @return string
     */
    public function getVirtualHostPath()
    {
        return $this->_virtual_host_path;
    }

    /**
     * @param string $virtual_host_path
     */
    public function setVirtualHostPath($virtual_host_path)
    {
        $this->_virtual_host_path = $virtual_host_path;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->_domain = $domain;
    }

    /**
     * @return string
     */
    public function getProjectPath()
    {
        return $this->_projectPath;
    }

    /**
     * @param string $projectPath
     */
    public function setProjectPath($projectPath)
    {
        $this->_projectPath = $projectPath;
    }

    /**
     * @return string
     */
    public function getProjectUniqueAlias() {
        return basename($this->getProjectPath());
    }

    /**
     * @param $name
     * @return string
     */
    public function getVirtualHostFilename() {
        return $this->getVirtualHostPath() . DIRECTORY_SEPARATOR . $this->getProjectUniqueAlias() . ".conf";
    }

    /**
     * Create virtual host configuration
     * @param $name
     */
    public function createVirtualHost()
    {

        $this->checkEnvironment();

        $virtualHostFile = $this->getVirtualHostFilename();

        // Vhost must be unique
        if($this->_filesystem->exists($virtualHostFile)) {
            throw new IOException(sprintf("Host '%s' already exists", $this->getProjectUniqueAlias()));
        }

        // Create virtual host config file using Templating
        $virtualHostContent = $this->_templating->render($this->getVirtualHostTemplate(), array(
            'host_root' => $this->getProjectPath(),
            'host_name' => $this->getProjectUniqueAlias() . '.' . $this->getDomain(),
        ));

        // Put it on
        file_put_contents($virtualHostFile, $virtualHostContent);

    }

    /**
     * Destroy virtual host configuration
     * @param $project
     * @return mixed
     */
    public function destroyVirtualHost()
    {
        $this->checkEnvironment();

        $virtualHostFile = $this->getVirtualHostFilename();

        // Remove file
        if($this->_filesystem->exists($virtualHostFile)) {
            $this->_filesystem->remove($virtualHostFile);
        }

    }

    /**
     * Reload server configuration
     * @return void
     */
    public function reloadConfiguration()
    {
        // TODO: Implement reloadConfiguration() method.
    }

    /**
     * @var $name
     * @return void
     */
    protected function checkEnvironment() {

        if($this->getProjectPath() === null) {
            throw new \InvalidArgumentException("You should set path to project");
        }

        if(!is_writable($this->getVirtualHostPath())) {
            throw new IOException("You should have write permissions in virtual hosts folder");
        }
    }


}