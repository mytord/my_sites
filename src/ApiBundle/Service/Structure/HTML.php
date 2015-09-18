<?php
/**
 * Created by PhpStorm.
 * User: evgeny
 * Date: 10.09.15
 * Time: 13:27
 */

namespace ApiBundle\Service\Structure;

use Symfony\Component\Filesystem\Filesystem;

class HTML implements StructureInterface {

    /**
     * @var string
     */
    protected $_path;

    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    protected $_templateEngine;

    /**
     * @var string
     */
    protected $_template = 'ApiBundle:Project:simple_html.html.twig';

    /**
     * @var Filesystem
     */
    protected $_fs;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_fs = new Filesystem();
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param mixed $engine
     */
    public function setTemplating($engine)
    {
        $this->_templateEngine = $engine;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Builds project file structure
     * @return void
     */
    public function build()
    {
        $this->checkEnvironment();

        // render template
        $content = $this->_templateEngine->render($this->_template, array(
            'project_directory' => $this->_path,
        ));

        // create index.html file inside path with content
        file_put_contents($this->_path . DIRECTORY_SEPARATOR . 'index.html', $content);
    }

    /**
     * Destroy project file structure
     * @return void
     */
    public function destroy()
    {
        $this->checkEnvironment();

        $files = new \RecursiveDirectoryIterator($this->_path);

        // unlink all files
        foreach ($files as $file) {
            if($file->isFile()) {
                unlink($file);
            }
        }

        // remove directory
        rmdir($this->_path);

    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function checkEnvironment() {

        if(!$this->_path) {
            throw new \InvalidArgumentException("You must set path to your structure");
        }

        if(!$this->_templateEngine) {
            throw new \InvalidArgumentException("You must set engine to render templates");
        }

        if(!is_dir($this->_path)) {
            $this->_fs->mkdir($this->_path);
        }
    }

}