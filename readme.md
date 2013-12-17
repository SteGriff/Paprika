#Paprika

Generates flavour text and natural language from human-readable grammar configuration files.
<http://paprika.me.uk>

## Future applications

 + Tweetbot
 + Procedural generation of NPC monologue
 + Descriptions or flavour text of entities or dungeons

## How to test

To play with Paprika, go to <http://paprika.me.uk>. Learn about the Formula language below. Click Explore on Paprika.me.uk/use to view the grammar.

## Paprika Formula Language

To get output, you send commands in Paprika Formula Language. Here is a tutorial/reference:

String literals are returned in output, identical to their input:

	Hello
	
Produces "Hello"

Expressions in square brackets pick a random definition from that category, as defined in grammar files:

	[animal]
	
May produces "dog" or "cat", etc.

The tags `[a]` and `[an]` are magical. They wait until everthing else has been populated, and then pick correctly between
'a' and 'an', based on the first letter of the following word. They are both supplied, so that the Formula can read naturally:

	[an] [animal]
	
May produce "a dog" or "an otter".  
*N.b. Currently 'aeiou' is considered to be the set of vowels which require 'an'.*

If you use identical tags more than once, they will be populated with the same thing (think of it as a global find+replace):

	My [animal] is great. I love my [animal].
	
May produce "My cow is great. I love my cow."

To generate different results, you can put labels inside tags to differentiate them, using a hash `#`:

	My [animal#1] is great, but I also love my [animal#2]
	
Be warned that this currently doesn't exclude results which have been chosen already,
so you may get the same result in both brackets. However, the randomisation is done seperately for each one.

You can inject the results of a tag into another tag. If the 'simple' tag comes first, this is easy:

	My favourite [category] is [[category]]
	
What happens here is that [category] is parsed first, so the expression becomes (for example):

	My favourite sport is [sport]
	
And then in the next pass, the `[sport]` tag is replaced by a random sport:

	My favourite sport is badminton
	
Equally, the expresion could have randomised to

	My favourite country is Italy
	
If the complex part (with nested brackets) comes first in the Formula, it will fail:

	[[category]] is the best [category]
	 ^-- Error, no such thing as [[category]
	 
So you can make a 'hidden call' to avoid this:

	[H category][[category]] is the best [category]
	
The `H` command means that the result of `[H category]` will be hidden, but replaces all other instances.
The above might produce "brown is the best colour".

Other commands can be used, although they only really work on simple categories (not injections):

 * `U` produces uppercase: `[U sport]`
 * `L` produces lowercase: `[L female name]`
 * `?` gives the marked tag a 50% chance of appearing at all: `a [? colour] car`
 	* Double spaces will be simplified after parsing.
	
If the grammar allows for it, you can trigger output with strong contextual links in this way.
The following example is not defined in my grammars, but is given for example. Assume this grammar:

	*animal
	dog
	cat
	mouse
	
	*did dog thing
	barked
	woofed
	
	*did cat thing
	meowed
	purred
	
	*did mouse thing
	squeaked
	spooked an elephant

Then this formula:

	My [animal] [did [animal] thing]
	
May produce `My dog woofed` or `My mouse spooked an elephant`

----------
I hope you enjoy Paprika!
@SteGriff - <http://stegriff.co.uk>
