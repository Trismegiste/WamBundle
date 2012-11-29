# Features

I have to cut it down to maintain readability and performance.

You can pass predicate as parameter, example :
```prolog
equal(X, X).
equal(molecule(carbon, oxygen), molecule(carbon, X)).
```

There also are some metalogic like :

* call
* assert
* retract (limited on last predicate)
* write/read

There are no no findall or bagof or other shiny metalogic. Mainly for two reasons :

* Since it is a embedded DSL in PHP, I don't think it is really usefull
* I would have to break some speed optimizations in the WAM compiler
