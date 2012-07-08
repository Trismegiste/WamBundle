# WamBundle

## A Warren's Abstract Machine for PHP
by Stefan Buettcher (original java version)
and Florent Genette (ported to PHP)

David Warren wrote in "WAM - A tutorial reconstruction" :
<blockquote><p>The WAM is an abstract machine consisting of a memory architecture and instruction
set tailored to Prolog. It can be realised efficiently on a wide range of
hardware, and serves as a target for portable Prolog compilers. It has now become
accepted as a standard basis for implementing Prolog.</p></blockquote>

Wikipedia says :
<blockquote><p>Prolog has its roots in first-order logic, a formal logic, and unlike many
other programming languages, Prolog is declarative: the program logic is
expressed in terms of relations, represented as facts and rules. A computation
is initiated by running a query over these relations.</p></blockquote>

As you can see, Prolog has nearly no instructions nor loops nor ifs nor gotos.
The only thing you have to do is to enounce absolute truths. 
At that point, Mr Spock says : "Fascinating"

Stop the chatty chat, I recommand wikipedia and the excellent book by 
HASSAN AÏT-KACI in the doc directory.

## Is it usefull ?
For example, to implement some business intelligence algorithms, you can avoid
big boring sequences of if-else-switch or a big bunch of Chain of Responsability 
in PHP with a limited (and readable) set of rules and predicates in Prolog.
Then, if you have a lots of business rules and if they are changing every week,
this can be VERY usefull unless you love debugging long lists of switch & if.
