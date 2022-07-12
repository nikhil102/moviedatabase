<?php

namespace Common\Generators\Controller;

use Illuminate\Routing\Console\ControllerMakeCommand;

class GenerateController extends ControllerMakeCommand
{
    public function fire()
    {
        if (parent::fire() !== false) {
            // add route
            $path = base_path('routes/web.php');
            if ($this->option('model')) {
                $marker = "});";
                file_put_contents($path, str_replace(
                    $marker,
                    "\n{$this->getRouteStub()}\n{$marker}",
                    file_get_contents($path)
                ));
            }
        }
    }

    protected function getRouteStub()
    {
        $resourceName = kebab_case(class_basename($this->option('model')));
        $resourceComment = strtoupper(title_case($this->option('model')));
        return str_replace(
            ['dummyResourceName', 'dummyControllerName', 'dummyResourceComment'],
            [$resourceName, $this->getNameInput(), $resourceComment],
            file_get_contents(__DIR__ . '/stubs/route.resource.stub')
        );
    }

    protected function buildClass($name)
    {
        $modelClass = $this->parseModel($this->option('model'));
        $modelName = class_basename($modelClass);
        return str_replace('DummyModelName', $modelName, parent::buildClass($name));
    }

    protected function getStub()
    {
        if ($this->option('model')) {
            return __DIR__ . '/stubs/controller.model.stub';
        }

        return parent::getStub();
    }
}
