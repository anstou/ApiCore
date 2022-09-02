<?php

namespace ApiCore\Library\Command\Commands\IDEHelp;

use ApiCore\Library\Command\Command;
use ApiCore\Library\Command\CommandKernel;
use ApiCore\Library\Command\Commands\Make\Filter;

class ControllerHelp extends CommandKernel
{

    /**
     * @var string|null 命令别名
     */
    public static ?string $Alias = 'ControllerHelp';

    protected  array $Params = ['--controller'];


    /**
     * @throws \Exception
     */
    public function Run(): int
    {
        $controllerPath = $this->Param('--controller');
        $p = explode('app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR, $controllerPath);
        $appPath = $p[0];
        $mc = explode(DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR, $p[1]);
        $module = $mc[0];
        $controller = str_replace(['.php', DIRECTORY_SEPARATOR], ['', '\\'], $mc[1]);

        $controllerNamespace = "\\App\\Modules\\$module\\Controllers\\$controller";
        $filterNamespace = "\\App\\Modules\\$module\\Filters\\$controller";


        if (!class_exists($controllerNamespace)) {
            throw new \Exception($controllerNamespace . '控制器不存在');
        }

        $reflection = new \ReflectionClass($controllerNamespace);
        if (!class_exists($filterNamespace)) {

            $filterPathname = Command::Dispatch(Filter::class, ['module_name' => $module, 'filter_name' => $controller]);
            if (!is_string($filterPathname)) {
                throw new \Exception('过滤器创建失败');
            }
            include $filterPathname;
        }

        $filterReflection = new \ReflectionClass($filterNamespace);
        $filterFilename = $filterReflection->getFileName();
        $filterCode = $this->readFileForLine($filterFilename);

        //为控制器动作添加过滤方法
        $date = date('Y-m-d H:i:s');
        foreach ($reflection->getMethods() as $method) {
            $controllerMethodName = $method->getName();
            if ($method->isProtected() && preg_match('/^[A-Z]+\w+Action$/', $controllerMethodName)) {
                $filterAction = str_replace('Action', 'Filter', $controllerMethodName);
                if (!$filterReflection->hasMethod($filterAction)) {

                    $params = $method->getParameters();

                    $paramsCode = '';
                    $dataStr = '';

                    if (!empty($params)) {


                        $names = [];
                        foreach ($params as $param) {
                            $paramsCode .= "        \$$param->name = \$this->request->get('$param->name');" . PHP_EOL;
                            $names[] = $param->name;
                        }
                        $dataStr = 'data: compact(\'' . implode("','", $names) . '\')';
                    }

                    $code = <<<CODE
    /**
     * @return ApiRestful
     * 创建时间:$date
     * 结果指向函数:{@link $controllerNamespace::$controllerMethodName}
     */
    public function $filterAction(): ApiRestful
    {
$paramsCode
        
        return new ApiRestful($dataStr);
    }
    

CODE;

                    array_splice($filterCode, $filterReflection->getEndLine() - 1, 0, $code);

                }
            }
        }

        file_put_contents($filterFilename, implode('', $filterCode));


        return 0;
    }

    public function readFileForLine(string $pathname): array
    {
        $data = [];
        $file = fopen($pathname, 'r');
        $line = 1;
        while (!feof($file)) {
            $data[$line] = fgets($file);
            $line++;
        }
        fclose($file);
        return $data;
    }
}