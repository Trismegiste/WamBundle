male(john).
male(thomas).

not(Call) :- call(Call), !, fail.
not(Call).

female(X) :- not(male(X)).

concatenate([], X, X).
concatenate([X|L1], L2, [X|L3]) :- concatenate(L1, L2, L3).

faculty(X, 0) :- X < 0, !.

dec(X, Y) :- Y is X - 1.

determinant(la).
determinant(le).
nom(souris).
nom(chat).
adjectif(blanc).
adjectif(rouge).
adjectif(blanche).
genre(la,feminin).
genre(le,masculin).
genre(souris,feminin).
genre(chat,masculin).
genre(blanc,masculin).
genre(blanche,feminin).
genre(rouge,_).

accord(X, Y) :- genre(X,Z), genre(Y, Z).
sn(X, Y) :- determinant(X), nom(Y), accord(X, Y).
sn(X, Y) :- nom(X), adjectif(Y), accord(X, Y).
p(snm(determinant(X), nom(Y), G), X, Y) :- sn(X, Y), genre(X, G).

factorial(0, 1).
factorial(N, X) :- N > 0, N1 is N - 1, factorial(N1, P), X is N * P.

