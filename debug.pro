pere(anakin, luke).
pere(medichlorian, anakin).
mere(shmi, anakin).
mere(padme, luke).
mere(ruwee, padme).
pere(jobal, padme).
mere(padme, leia).
pere(anakin, leia).

grandpere(X, Y) :- pere(X, Z) , pere(Z,Y).
grandpere(X, Y) :- pere(X, Z) , mere(Z,Y).

grandmere(X, Y) :- mere(X, Z) , mere(Z,Y).
grandmere(X, Y) :- mere(X, Z) , pere(Z,Y).

parent(X, Y) :- pere(X, Y).
parent(X, Y) :- mere(X, Y).

frere(X, Y) :- parent(Z, X) , parent(Z, Y), X != Y.

equal(X, X).

not(Call) :- call(Call), !, fail.
not(Call).