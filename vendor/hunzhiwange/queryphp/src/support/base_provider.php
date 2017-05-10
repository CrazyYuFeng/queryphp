<?php
// [$QueryPHP] A PHP Framework Since 2010.10.03. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
namespace Q\support;

<<<queryphp
##########################################################
#   ____                          ______  _   _ ______   #
#  /     \       ___  _ __  _   _ | ___ \| | | || ___ \  #
# |   (  ||(_)| / _ \| '__|| | | || |_/ /| |_| || |_/ /  #
#  \____/ |___||  __/| |   | |_| ||  __/ |  _  ||  __/   #
#       \__   | \___ |_|    \__  || |    | | | || |      #
#     Query Yet Simple      __/  |\_|    |_| |_|\_|      #
#                          |___ /  Since 2010.10.03      #
##########################################################
queryphp;

use Q\helper\helper;

/**
 * 注册基础服务提供者
 *
 * @author Xiangmin Liu<635750556@qq.com>
 * @package $$
 * @since 2017.04.23
 * @version 1.0
 */
class base_provider extends provider {
    
    /**
     * 单一实例
     *
     * @var array
     */
    private $arrSingleton = [
            // cache
            'Q\cache\filecache',
            'Q\cache\memcache',
            
            // cookie
            'Q\cookie\cookie',
            
            // database
            'Q\database\database',
            
            // event
            'Q\event\event',
            
            // exception
            'Q\exception\exception',
            
            // i18n
            'Q\i18n\i18n',
            'Q\i18n\tool',
            
            // image
            'Q\image\image',
            
            // log
            'Q\log\log',
            
            // option
            'Q\option\option',
            
            // request
            'Q\http\request',
            'Q\http\response',
            
            // router
            'Q\router\router',
            
            // view
            'Q\view\compilers',
            'Q\view\parsers',
            'Q\view\theme',
            
            // xml
            'Q\xml\xml' 
    ];
    
    /**
     * 其它类型
     *
     * @var array
     */
    private $arrOther = [
            // collection
            'Q\datastruct\collection\collection',
            
            // queue
            'Q\datastruct\queue\queue',
            'Q\datastruct\queue\stack' 
    ];
    
    /**
     * 注册基础工厂数据
     *
     * @return void
     */
    public function register() {
        // 注册 singleton
        $this->registerSingleton_ ();
        
        // 注册 other
        $this->registerOther_ ();
    }
    
    /**
     * 注册单一实例
     *
     * @return void
     */
    private function registerSingleton_() {
        foreach ( $this->arrSingleton as $strCore ) {
            $this->objProject->singleton ( $strCore, function () use($strCore) {
                return helper::newInstanceArgs ( $strCore, func_get_args () );
            } );
        }
    }
    
    /**
     * 注册非单一实例
     *
     * @return void
     */
    private function registerOther_() {
        foreach ( $this->arrOther as $strOther ) {
            $this->objProject->register ( $strOther, function () use($strOther) {
                return helper::newInstanceArgs ( $strOther, func_get_args () );
            } );
        }
    }
}
