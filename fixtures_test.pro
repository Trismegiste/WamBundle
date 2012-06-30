male(john).
male(thmoas). 

not(Call) :- call(Call), !, fail. 
not(Call). 

female(X) :- not(male(X)).