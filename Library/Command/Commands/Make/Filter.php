<?php

namespace ApiCore\Library\Command\Commands\Make;

use ApiCore\Library\Command\Command;
use ApiCore\Library\Command\CommandKernel;
use ApiCore\Library\Module\Module as ModuleBase;

class Filter extends CommandKernel
{
    /**
     * @var string|null 命令别名
     */
    public static ?string $Alias = 'make:filter';

    protected  array $Params = [
        'module_name',
        'filter_name',
    ];

    public function Run(): false|string
    {
        $path = dirname(__FILE__);

        $module_name = $this->Param('module_name');
        $filter_name = $this->Param('filter_name');
        if (!ModuleBase::hasModule($module_name)) {
//            return new ApiRestful(1, '模块不存在');
            return false;
        }
        $filterDirname = ModuleBase::getModulePath($module_name . DIRECTORY_SEPARATOR . 'Filters' . DIRECTORY_SEPARATOR . $filter_name . '.php');
        if (file_exists($filterDirname)) {
//            return new ApiRestful(1, '控制器文件已存在');
            return false;
        }

        $filterCodeStr = file_get_contents($path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'filter.template');
        $controllerNamespace = "\\App\\Modules\\$module_name\\Controllers\\$filter_name";
        $filterCodeStr = str_replace(['{module_name}', '{filter_name}', '{controller_namespace}'], [$module_name, $filter_name, $controllerNamespace], $filterCodeStr);
        filePutContents($filterDirname, $filterCodeStr);
        return $filterDirname;
    }
}