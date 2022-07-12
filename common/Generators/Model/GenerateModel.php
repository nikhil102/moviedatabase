<?php

namespace Common\Generators\Model;

use Illuminate\Foundation\Console\ModelMakeCommand;

class GenerateModel extends ModelMakeCommand
{
    public function fire()
    {
        $this->input->setOption('resource', true);
        $this->input->setOption('migration', true);
        $this->createPolicy();
        $this->createRequest();
        $this->createAction();

        parent::fire();
    }

    protected function createPolicy()
    {
        $modelName = $this->getNameInput();

        $this->call('make:policy', [
            'name' => "{$modelName}Policy",
            '--model' => $modelName
        ]);
    }

    protected function getStub()
    {
        return __DIR__.'/stubs/model.stub';
    }

    private function createRequest()
    {
        $modelName = $this->getNameInput();

        $this->call('make:request', [
            'name' => "Crupdate{$modelName}Request",
            '--model' => $modelName
        ]);
    }

    private function createAction()
    {
        $modelName = $this->getNameInput();

        $this->call('make:action', [
            'name' => "Crupdate{$modelName}",
            '--model' => $modelName
        ]);
    }
}