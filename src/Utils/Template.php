<?php

/**
 * Template.php - Template engine
 *
 * Generate from templates with template vars.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils;

class Template
{
    protected $aNamespaces;
    protected $sDefaultNamespace;
    protected $xEngine;

    public function __construct($sTemplateDir)
    {
        $this->xEngine = new \Latte\Engine;
        $this->aNamespaces = [];
        $this->sDefaultNamespace = '';
        $this->addNamespace('jaxon', rtrim(trim($sTemplateDir), "/\\"), '.tpl', false);
    }

    /**
     * Add a namespace to the template system
     *
     * @param string        $sNamespace         The namespace name
     * @param string        $sDirectory         The namespace directory
     * @param string        $sExtension         The extension to append to template names
     * @param string        $bIsDefault         Is it the default namespace?
     *
     * @return void
     */
    public function addNamespace($sNamespace, $sDirectory, $sExtension = '', $bIsDefault = false)
    {
        if($sNamespace == 'jaxon' && key_exists($sNamespace, $this->aNamespaces))
        {
            return;
        }
        $this->aNamespaces[$sNamespace] = [
            'directory' => $sDirectory,
            'extension' => $sExtension,
        ];
        if($bIsDefault)
        {
            $this->sDefaultNamespace = $sNamespace;
        }
    }

    /**
     * Set a cache directory for the template engine
     *
     * @param string        $sCacheDir            The cache directory
     *
     * @return void
     */
    public function setCacheDir($sCacheDir)
    {
        $sCacheDir = (string)$sCacheDir;
        if(is_writable($sCacheDir))
        {
            $this->xEngine->setTempDirectory($sCacheDir);
        }
    }

    /**
     * Render a template
     *
     * @param string        $sTemplate            The name of template to be rendered
     * @param string        $aVars                The template vars
     *
     * @return string        The template content
     */
    public function render($sTemplate, array $aVars = array())
    {
        // Get the namespace name
        $sNamespace = '';
        $iSeparatorPosition = strpos($sTemplate, '::');
        if($iSeparatorPosition !== false)
        {
            $sNamespace = substr($sTemplate, 0, $iSeparatorPosition);
            $sTemplate = substr($sTemplate, $iSeparatorPosition + 2);
        }
        if($sNamespace == '')
        {
            $sNamespace = $this->sDefaultNamespace;
        }
        // Check if the namespace is defined
        $sNamespace = trim($sNamespace);
        if(!key_exists($sNamespace, $this->aNamespaces))
        {
            return false;
        }
        $sNamespace = $this->aNamespaces[$sNamespace];
        // Get the template path
        $sTemplateName = trim($sTemplate) . $sNamespace['extension'];
        $sTemplatePath = $sNamespace['directory'] . '/' . $sTemplateName;
        // Render the template
        $sRendered = $this->xEngine->renderToString($sTemplatePath, $aVars);
        return $sRendered;
    }
}
