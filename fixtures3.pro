male(luke).
female(leia).
father(anakin, luke).

potentiel(X) :- assert(X), fail.
remove(X) :- retract(X), fail.
potentiel(X).

unif(F, P) :- call(F(P)).
unif(F, P1, P2) :- call(F(P1, P2)).
