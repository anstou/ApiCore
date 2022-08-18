<?php

namespace ApiCore\Command\Make;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\InterfaceWarehouse\Command;
use ApiCore\Library\Module\Module as ModuleBase;

class Controller extends Command
{
    protected array $params = [
        'module_name',
        'controller_name',
    ];

    public function run(): bool
    {
        $module_name = $this->param('module_name');
        $controller_name = $this->param('controller_name');
        if (!ModuleBase::hasModule($module_name)) {
//            return new ApiRestful(1, '模块不存在');
            return false;
        }
        $controllerDirname = ModuleBase::getModulePath($module_name . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $controller_name . '.php');
        if (file_exists($controllerDirname)) {
//            return new ApiRestful(1, '控制器文件已存在');
            return false;
        }
        $controllerTemplatePath = module_path('Application' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'controller.template');
        $controllerCodeStr = file_get_contents($controllerTemplatePath);
        $controllerCodeStr = str_replace(['{module_name}', '{controller_name}'], [$module_name, $controller_name], $controllerCodeStr);

        filePutContents($controllerDirname, $controllerCodeStr);
        Command::dispatch(Filter::class, ['module_name' => $module_name, 'filter_name' => $controller_name]);

        return true;
    }
}