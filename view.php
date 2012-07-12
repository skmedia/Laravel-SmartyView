<?php

namespace SmartyView;

use Laravel;

class View extends Laravel\View
{
    /**
     *
     * @var string
     */
    protected $bundle_root = '';

    /**
     *
     * @var string
     */
    protected $template = '';

    /**
     *
     * @var string
     */
    protected $template_ext = '.tpl';

    /**
     * Get the path to a view on disk.
     *
     * @param $view
     *
     * @return string
     * @throws \Exception
     */
    protected function path($view)
    {
        $view = str_replace('.', '/', $view);
        $this->bundle_root = Laravel\Bundle::path(Laravel\Bundle::name($view)) . 'views';
        $path = $this->bundle_root . DS . Laravel\Bundle::element($view) . $this->template_ext;

        if (file_exists($path)) {
            if (str_contains($view, '::')) {
                $this->template = substr($view, strpos($view, '::') + 2, strlen($view)) . $this->template_ext;
            } else {
                $this->template = $view . $this->template_ext;
            }

            return $path;
        }
        
        throw new \Exception("View [$view] does not exist.");
    }
    
    /**
     * Render the view.
     *
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        Laravel\Event::fire("laravel.composing: {$this->view}", array($this));
        
        ob_start();
        
        try {
            
            require_once dirname(__FILE__) . '/Smarty/libs/Smarty.class.php';
            
            $configKey = 'SmartyView::smarty';
        
            $caching = Laravel\Config::get($configKey . '.caching');
            $cache_lifetime = Laravel\Config::get($configKey . '.cache_lifetime');
            $debugging = Laravel\Config::get($configKey . '.debugging');

            $template_path = Laravel\Config::get($configKey . '.template_path');;
            $compile_path  = Laravel\Config::get($configKey . '.compile_path');;
            $cache_path    = Laravel\Config::get($configKey . '.cache_path');;

            $Smarty = new \Smarty();

            $Smarty->setTemplateDir($template_path);
            $Smarty->setCompileDir($compile_path);
            $Smarty->setCacheDir($cache_path);
            
            if ($this->bundle_root) {
                $Smarty->addTemplateDir($this->bundle_root);
            }

            $Smarty->debugging = $debugging;
            $Smarty->caching = $caching;
            $Smarty->cache_lifetime = $cache_lifetime;

            foreach ($this->data() as $var => $val) {
                $Smarty->assign($var, $val);
            }

            print $Smarty->display($this->template);
            
        } catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }

        return ob_get_clean();
    }
}
