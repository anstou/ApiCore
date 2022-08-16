<?php

namespace ApiCore\Command\Make;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\InterfaceWarehouse\Command;
use ApiCore\Library\Module\Module as ModuleBase;

class Filter extends Command
{
    protected array $params = [
        'module_name',
        'filter_name',
    ];

    public function run(): false|string
    {
        $module_name = $this->param('module_name');
        $filter_name = $this->param('filter_name');
        if (!ModuleBase::hasModule($module_name)) {
//            return new ApiRestful(1, '模块不存在');
            return false;
        }
        $filterDirname = ModuleBase::getModulePath($module_name . DIRECTORY_SEPARATOR . 'Filter' . DIRECTORY_SEPARATOR . $filter_name . '.php');
        if (file_exists($filterDirname)) {
//            return new ApiRestful(1, '控制器文件已存在');
            return false;
        }
        $filterTemplatePath = module_path('Application' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'filter.template');
        $filterCodeStr = file_get_contents($filterTemplatePath);
        $controllerNamespace = "\\App\\Modules\\$module_name\\Controllers\\$filter_name";
        $filterCodeStr = str_replace(['{module_name}', '{filter_name}', '{controller_namespace}'], [$module_name, $filter_name, $controllerNamespace], $filterCodeStr);
        filePutContents($filterDirname, $filterCodeStr);
        return $filterDirname;
    }
}