<?php
require_once($config['root'].'modules/Twig/lib/Twig/Autoloader.php');

class DataTemplater {

    function __construct($pConfig){
        Twig_Autoloader::register();
        $this->config = $pConfig;
        $this->loader = new Twig_Loader_Filesystem($pConfig['root'].'templates/');
        $this->twig = new Twig_Environment($this->loader);
    }
    
    
    public function render($lTemplateUrl, $lData){
        if(!empty($lTemplateUrl))
            return $this->twig->render($lTemplateUrl, $lData); 
        
    } 
}
