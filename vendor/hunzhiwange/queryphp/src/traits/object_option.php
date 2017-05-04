<?php
// [$QueryPHP] A PHP Framework Since 2010.10.03. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
namespace Q\traits;

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

/**
 * 对象参数复用
 *
 * @author Xiangmin Liu<635750556@qq.com>
 * @package $$
 * @since 2017.04.23
 * @version 4.0
 */
trait object_option {
    
    /**
     * 配置
     *
     * @var array
     */
    protected $arrObjectOption = [ ];
    
    /**
     * 设置配置
     *
     * @param array|string $mixOptionName            
     * @param string $strOptionValue            
     * @return void
     */
    public function setObjectOption($mixOptionName, $strOptionValue = null) {
        if (is_array ( $mixOptionName )) {
            $this->arrObjectOption = array_merge ( $this->arrObjectOption, $mixOptionName );
        } else {
            $this->arrObjectOption [$mixOptionName] = $strOptionValue;
        }
        return $this;
    }
    
    /**
     * 返回配置
     *
     * @param string $strOptionName            
     * @return mixed
     */
    protected function getObjectOption_($strOptionName) {
        return isset ( $this->arrObjectOption [$strOptionName] ) ? $this->arrObjectOption [$strOptionName] : null;
    }
    
    /**
     * 合并默认配置
     *
     * @return array
     */
    protected function mergeObjectOption_() {
        $this->arrObjectOption = array_merge ( $this->arrDefaultObjectOption, $this->arrObjectOption );
    }
}
