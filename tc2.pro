ancestor(X, Y) :- parent(X, Y), !.
ancestor(X, Y) :- parent(Z, Y), ancestor(X, Z).

equal(X, X).

faculty(X, 0) :- X < 0, !.

member(X, [X]).

not(Call) :- call(Call), !, fail.
not(Call).
