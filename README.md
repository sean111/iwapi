iwapi
=====

Unofficial Infinity Wars API

Current Endpoints
-----------------

GET /cards => Returns all the cards

GET /factions => Returns all the factions

GET /card/name/:name => Search for cards like :name

GET /card/set/:set => Search for a card in the set :set

GET /card/factions/:factions+ => Search for a card that's in the factions ( Multiple )

GET /card/:key+ => Search for a card by it's keyname ( Multiple )

NOTE: Multiple arguments can be passed as arg1/arg2/arg3 to fields with the Multiple tag

Technology
----------

Slim PHP Framework: 2.6

Eloquent: Current Version

