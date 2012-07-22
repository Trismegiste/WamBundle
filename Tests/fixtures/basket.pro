total([], 0).
total([P|Basket], T) :- price(P, PP), total(Basket, ST), T is ST + PP.

contains([S|_], S).
contains([S|SS], I) :- contains(SS, I).

includes(S, []).
includes(S, [A|B]) :- contains(S, A), includes(S, B).


price(wow, 60).
price(diablo3, 70).
price(starwars_box, 60).
price(ultrabook, 1200).
price(bike, 650).
price(geforce, 550).
price(tyre, 25).

gift(B, tyre) :- contains(B, bike).
gift(B, lightsaber) :- contains(B, starwars_box).
gift(B, keychain) :- total(B, X), X > 200.
gift(B, life) :- includes(B, [diablo3, wow]).

discount(B, T) :- total(B, X), X > 1500, T is X / 30, !.
discount(B, 50) :- total(B, X), X > 1000, !.
discount(B, 20) :- includes(B, [geforce, wow]), !.
discount(B, 5) :- total(B, X), X > 100.
