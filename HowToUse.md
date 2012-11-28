# How To Use this library

## Standalone Command Line Interface
Open a command line interface and type :
``` bash
php console.php
```

## Using this library without symfony2
Perhaps you should have to modify the autoloader.
See the file test WAMService1Test.php for an real example

## CLI with symfony2.1
Open a command line interface and type :
``` bash
php app/console wam:prolog:console
```

## service for symfony2.1
Here is an example. You can watch PrologGuiController.php
<code>
$machine = $this->get('prolog.wam');
$compiler = new Prolog\PrologCompiler($machine);
$prog = $compiler->compile($prog);
$machine->addProgram($prog);
$result = $machine->runQuery($query);
</code>