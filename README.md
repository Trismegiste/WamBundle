# WamBundle

[![Build Status](https://secure.travis-ci.org/Trismegiste/WamBundle.png?branch=master)](http://travis-ci.org/Trismegiste/WamBundle)

## A Warren's Abstract Machine for Symfony2.3
Original java version by Stefan Büttcher.<br/>
PHP port, PhpUnit tests and bundle for Symfony2.3 by Florent Genette.

Version 1.3

## What ?
A Warren Abstract Machine (WAM) is a virtual machine (like a JVM for Java) for
Prolog. This library is intended to run on PHP 5.3 and preferably on Symfony 2.3.
Prolog is a logic language which solve problems with an inference engine.


David Warren wrote in "WAM - A tutorial reconstruction" :
<blockquote><p>The WAM is an abstract machine consisting of a memory architecture and instruction
set tailored to Prolog. It can be realised efficiently on a wide range of
hardware, and serves as a target for portable Prolog compilers. It has now become
accepted as a standard basis for implementing Prolog.</p></blockquote>

Wikipedia says :
<blockquote><p>Prolog has its roots in first-order logic, a formal logic, and unlike many
other programming languages, Prolog is declarative: the program logic is
expressed in terms of relations, represented as facts and rules. A computation
is initiated by running a query over these relations.</p></blockquote>

As you can see, Prolog has nearly no instructions nor loops nor ifs nor gotos.
The only thing you have to do is to enounce absolute truths.
At that point, Mr Spock says : "Fascinating"

Stop the chatty chat, I recommand wikipedia and the excellent book by
Hassan Aït-Kaci in the doc directory.

## Is it usefull ?
Like any other <a href="http://en.wikipedia.org/wiki/Domain-specific_language">DSL</a>,
Prolog has a very limited scope but sometimes it can simplify some problem like :

 * You need a rule engine
 * You want to avoid big boring sequences of if-else-switch to implement some business intelligence
 * You need a big bunch of Chain of Responsability which changes every week
 * You face a logic problem with non deterministic path

## Is there a release for ZF/CodeIgniter/FatFreeFramework ?
Yes U can: this library can be included in whatever you want.
For example : the console can be run as far as you have PHP 5.3.8.
PhpUnit and TravisCI don't need any framework to be run.

But for convinience, there is also service for symfony 2.3 because it is the
most advanced framework for PHP today
(I mean with features like namespace, ORM, DDD, DI, AOP to name a few )

## Can I haZ example ?
Look at the file basket.pro : it's a set of marketing rules for gift and discount
based on the content of a cart. For functional tests, I have also added many
programs like list operations (see 'append', it is very fun), family trees,
eight queen problem and hanoi problem.

## Why port a forty-year-old language in PHP ?
### (from a ten-year-old release in Java  ?)
Well, first thing it is a matter of taste. Even with 3 hundreds years, JS Bach
is still the best musician in the Multiverse. Second, I am not saying Prolog
can solve anything, no. I think there is one language one can use for one
specific coding problem. As you know : "one language is not enough".
See http://memeagora.blogspot.fr/2006/12/polyglot-programming.html

I also think, like any other language, we, programmers, have some responsability
to keep this knowledge alive, and today the best way is to port this WAM to PHP.
It's not nostalgia, it's just recollection about these pioneers of computer
programming like Von Neumann or Turing.

## Some notes about this port
I went through a lot a trouble thanks the "soft-typing" of PHP and the damned
"===" but even if I still prefer Java and strong typing, the managing of
strings and arrays in PHP is awsum. There's probably much optimizations to do
since the design was not thought for PHP. The good thing is my knowledge of PHP
internals has improved.

## Some notes about perfs
Yes, this WAM is slow compared to SWI-Prolog. If you need a lot of recursions
or a big set of datas, I don't think this piece of software is for you.

As I wrote, this is for specific problems where the imperative programming paradigm
is irrelevant. This is not the only paradigm : Think declarative programming !
And if you like this and want to go further, look at the Clojure language.

## Running test
To run all tests. I have splitted the test suite into two groups to extract lengthy stress tests.
``` bash
phpunit
```

## I want to fork, can I ?
Yes you can ! But don't forget to test new features or changes !

Today, this library has 422 assertions, it took me a lot of time but it
was mandatory before refactoring this to make it as a modern PHP bundle for
Symfony2.

There is still a lot to do : a CLI compiler, a better (abstract) filesystem,
updating the metalogic clause known as 'retract', adding features like 'bagof' and
'findall' but don't forget the tests and documentations because I'll never merge
a PR for new features if it's not tested and commented.

## I found a bug, what can I do ?
 * Fork it with Github !
 * Write a new test in the class IFoundABugTest to show the bug
 * Make a Pull Request for the faulty test
 * I merge the new test
 * Someone (you, me, anyone) fix it and make a Pull Request for the fix
 * Eventually I'll refactor IFoundABugTest class to move the test in the right place

I like TDD for debugging. Sometimes it's annoying but it is always
for the best, specially accross the net. With the help of TravisCI, tracking bugs
is a piece of cake.

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
