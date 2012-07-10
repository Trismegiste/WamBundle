price(wow, 70).
price(starwars_box, 60).
price(ultrabook, 1200).
price(bike, 650).
price(geforce, 550).
price(tyre, 25).

gift(bike, tyre).
gift(starwars_box, lightsaber).

total([], 0).
total([P|Basket], T) :- price(P, PP), total(Basket, ST), T is ST + PP.

discount(B, 30) :- includes(B, [geforce, wow]).
discount(B, 5) :- total(B, X), X > 100.

contains([S|SS], S).
contains([S|SS], I) :- contains(SS, I).

includes(S, []).
includes(S, [A|B]) :- contains(S, A), includes(S, B).