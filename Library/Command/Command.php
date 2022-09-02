<?php

namespace ApiCore\Library\Command;

abstract class Command
{
    /**
     * 初始化的命令行们
     * @var array
     */
    private static array $Commands = [];

    /**
     * 快捷使用控制命令
     *
     * @param string $command 命令类
     * @param array $params 要传递的参数
     * @return mixed
     * @throws \Exception
     */
    final public static function Dispatch(string $command, array $params = []): mixed
    {
        if (static::class !== self::class) throw new \Exception('请从' . self::class . '中调用dispatch');
        if (class_exists($command)) {
            return (new $command($params))->run();
        }
        return throw new \Exception($command . '::class不存在');
    }

    /**
     * 加入一个command
     *
     * @param string $alias 别名
     * @param array $commandData ['class','params]  command类
     * @return void
     */
    final public static function PushCommand(string $alias, array $commandData): void
    {
        static::$Commands[$alias] = $commandData;
    }

    /**
     * command命令存在的文件夹
     * 里面的实现不太智能
     * 现在没空去弄智能的了
     * 先暂时这样
     *
     * @param string[] $commandPaths
     * @return void
     * @throws \ReflectionException
     */
    public static function Init(array $commandPaths): void
    {
        $keyWords = [
            'api-core' . DIRECTORY_SEPARATOR . 'Library' => 'ApiCore\Library',
            'app' . DIRECTORY_SEPARATOR . 'Commands' => 'App\Commands'
        ];
        foreach ($commandPaths as $commandPath) {
            if (empty($commandPath)) continue;

            $filenames = scandir($commandPath);
            if (is_array($filenames)) foreach ($filenames as $filename) {
                if ($filename === '.' || $filename === '..') continue;

                $tName = $commandPath . DIRECTORY_SEPARATOR . $filename;
                if (is_dir($tName) && preg_match('/^[A-Z]+/', $tName) > 0) {
                    self::Init([$commandPath . DIRECTORY_SEPARATOR . $filename]);
                    continue;
                }

                if (is_file($tName) && preg_match('/^[A-Z]+[a-zA-Z]+.php$/', $filename) > 0) {
                    $tName = str_replace([...array_keys($keyWords), '.php'], [... array_values($keyWords), ''], $tName);

                    $className = '';
                    foreach ($keyWords as $str) {
                        $className = strstr($tName, $str);
                        if (is_string($className)) break;
                    }
                    if (is_string($className) && class_exists($className)) {
                        $r = new \ReflectionClass($className);
                        $alias = $r->hasProperty('Alias') ? $r->getStaticPropertyValue('Alias') : '';
                        if ($r->isInstantiable() && $r->isSubclassOf(CommandKernel::class) && !empty($alias)) {
                            static::PushCommand($alias, [
                                'class' => $className,
                                'params' => $r->getProperty('Params')->getDefaultValue()
                            ]);
                        }
                    }

                }//if (is_file($tName) && preg_match('/^[A-Z]+[a-zA-Z]+.php$/', $filename) > 0)


            }//if (is_array($filenames)) foreach ($filenames as $filename)

        }//foreach ($commandPaths as $commandPath)

    }

    /**
     * 所有已经注册的命令行
     *
     * @return array
     */
    public static function AllCommands(): array
    {
        return static::$Commands;
    }

    /**
     * 自动处理command命令
     *
     * @return mixed
     * @throws \Exception
     */
    public static function CommandAutoHandle(): mixed
    {
        $params = cliParams();
        $alias = $params[1] ?? '';
        $className = static::$Commands[$alias] ?? '';
        if (empty($className)) {
            echo '没有要运行的命令', PHP_EOL;
            return 0;
        }
        return static::Dispatch($className, $params);
    }

}