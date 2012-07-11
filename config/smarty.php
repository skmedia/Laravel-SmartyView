<?php

return array(
    'debugging'      => false,
    'caching'        => true,
    'cache_lifetime' => 120,
    
    'template_path'  => path('app').'views',
    'cache_path'     => path('storage').'views/cache',
    'compile_path'   => path('storage').'views/compile',
);
