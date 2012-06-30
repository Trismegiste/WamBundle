male(john).
male(thmoas). 

not(Call) :- call(Call), !, fail. 
not(Call). 

female(X) :- not(male(X)).

concatenate([], X, X).
concatenate([X|L1], L2, [X|L3]) :- concatenate(L1, L2, L3).

faculty(X, 5).
