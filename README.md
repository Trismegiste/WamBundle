# WamBundle

[![Build Status](https://secure.travis-ci.org/Trismegiste/WamBundle.png?branch=master)](http://travis-ci.org/Trismegiste/WamBundle)

## A Warren's Abstract Machine for Symfony 2.3
Original java version by Stefan Büttcher.<br/>
PHP port, PhpUnit tests and bundle for Symfony2.3 by Florent Genette.

Version 1.5

## What ?

A Warren Abstract Machine (WAM) is a virtual machine (like a JVM for Java) for
Prolog. This library is intended to run on PHP 5.4 and preferably on Symfony 2.3.
Prolog is a logic language which solve problems with an inference engine.

This bundle uses my standalone prolog library [trismegiste/wam-prolog][4]

## Install

```bash
$ composer.phar require trismegiste/prolog dev-master
```

## CLI with symfony 2.3

Open a command line interface and type :
``` bash
php app/console wam:prolog:console
```

## Service for symfony 2.3

You can read PrologGuiController.php.
Here is an example :
```php
$machine = $this->get('prolog.wam');
$compiler = new Prolog\PrologCompiler($machine);
$code = $compiler->compile($prog);
$machine->addProgram($code);
$result = $machine->runQuery($query);
```

## Licence
![cc-by-sa](http://i.creativecommons.org/l/by-sa/3.0/88x31.png)

This work is provided with the Creative Commons Attribution Share Alike 3.0 Licence.
It means you must keep my name and must provide any derivative works with this licence.
You can make money with this as long as you follow these rules. In other words :

    licence(wam_bundle, cc_by_sa_3).
    derivate_work_from(your_work, wam_bundle).
    licence(X, L) :- derivate_work_from(X, Y), licence(Y, L).
    price(wam_bundle, 0).
    price(your_work, _).

## Contributors
 * Lead : [Trismegiste](https://github.com/Trismegiste)

## Special thanks
 * Johann Sebastian Bach
 * William Gibson
 * Gene Roddenberry

[4]: https://packagist.org/packages/trismegiste/wam-prolog