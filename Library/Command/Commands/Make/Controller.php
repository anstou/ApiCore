<?php

namespace ApiCore\Library\Command\Commands\Make;

use ApiCore\Library\Command\Command;
use ApiCore\Library\Command\CommandKernel;
use ApiCore\Library\Module\Module as ModuleBase;

class Controller extends CommandKernel
{
    /**
     * @var string|null 命令别名
     */
    public static ?string $Alias = 'make:controller';
    
    protected  array $Params = [
        'module_name',
        'controller_name',
    ];

    public function Run(): bool
    {
        $path = dirname(__FILE__);


        $module_name = $this->Param('module_name');
        $controller_name = $this->Param('controller_name');
        if (!ModuleBase::hasModule($module_name)) {
//            return new ApiRestful(1, '模块不存在');
            return false;
        }
        $controllerDirname = ModuleBase::getModulePath($module_name . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $controller_name . '.php');
        if (file_exists($controllerDirname)) {
//            return new ApiRestful(1, '控制器文件已存在');
            return false;
        }
        $controllerTemplatePath = $path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'controller.template';
        $controllerCodeStr = file_get_contents($controllerTemplatePath);
        $controllerCodeStr = str_replace(['{module_name}', '{controller_name}'], [$module_name, $controller_name], $controllerCodeStr);

        filePutContents($controllerDirname, $controllerCodeStr);
        Command::Dispatch(Filter::class, ['module_name' => $module_name, 'filter_name' => $controller_name]);

        return true;
    }
}