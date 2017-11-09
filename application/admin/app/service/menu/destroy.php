<?php
// ©2017 http://your.domain.com All rights reserved.
namespace admin\app\service\menu;

use admin\app\service\menu\destroy_failed;
use admin\is\repository\admin_menu as repository;

/**
 * 后台菜单删除
 *
 * @author Name Your <your@mail.com>
 * @package $$
 * @since 2017.10.12
 * @version 1.0
 */
class destroy {
    
    /**
     * 后台菜单仓储
     *
     * @var \admin\is\repository\admin_menu
     */
    protected $oRepository;
    
    /**
     * 父级菜单
     *
     * @var int
     */
    protected $intParentId;
    
    /**
     * 构造函数
     *
     * @param \admin\is\repository\admin_menu $oRepository            
     * @return void
     */
    public function __construct(repository $oRepository) {
        $this->oRepository = $oRepository;
    }
    
    /**
     * 响应方法
     *
     * @param int $intId            
     * @return array
     */
    public function run($intId) {
        return $this->delete ( $this->oRepository->find ( $intId ) );
    }
    
    /**
     * 查找实体
     *
     * @param int $intId            
     * @return \admin\domain\entity\admin_menu|void
     */
    protected function find($intId) {
        try {
            return $this->oRepository->findOrFail ( $intId );
        } catch ( model_not_found $oE ) {
            throw new destroy_failed ( $oE->getMessage () );
        }
    }
    
    /**
     * 删除实体
     *
     * @param \admin\domain\entity\admin_menu $objMenu            
     * @return int
     */
    protected function delete($objMenu) {
        $this->checkChildren ( $objMenu->id );
        return $this->oRepository->delete ( $objMenu );
    }
    
    /**
     * 判断是否存在子菜单
     *
     * @param int $intId            
     * @return void
     */
    protected function checkChildren($intId) {
        if ($this->oRepository->hasChildren ( $intId )) {
            throw new destroy_failed ( '菜单包含子菜单，无法删除' );
        }
    }
}