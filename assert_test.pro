male(luke).
female(leia).

potentiel(X) :- assert(X), fail.
remove(X) :- retract(X), fail.
potentiel(X).