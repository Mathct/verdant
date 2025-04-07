<?php

$this->_ITEM_TYPES = [
     1 => ["name" => clienttranslate('Table')],
     2 => ["name" => clienttranslate('Chair')],
     3 => ["name" => clienttranslate('Sofa')],
     4 => ["name" => clienttranslate('Lamp')],
     5 => ["name" => clienttranslate('Shelf')],
     6 => ["name" => clienttranslate('Fish')],
     7 => ["name" => clienttranslate('Bird')],
     8 => ["name" => clienttranslate('Cat')],
     9 => ["name" => clienttranslate('Dog')]
];

$this->_POT_TYPES = [
     1 => ["name" => clienttranslate('Concrete')],
     2 => ["name" => clienttranslate('Wood')],
     3 => ["name" => clienttranslate('Ceramic')],
     4 => ["name" => clienttranslate('Terra Cotta')]
];

$this->_NURTURE_TYPES = [
     1 => [
          "name" => clienttranslate('Fertilizer'),
          "description" => clienttranslate('Add 3 verdancy to any 1 plant. All 3 verdancy must be added to a single plant. If the plant would be completed with fewer than 3 verdancy, than the remaining verdancy is not added, and is lost.')
     ],
     2 => [
          "name" => clienttranslate('Hand Trowel'),
          "description" => clienttranslate('Add 1 verdancy to up to 3 plants. You may add verdancy to any 3 plants, but you may not add more than 1 verdancy to any single plant. If a home has fewer than 3 incomplete plants, then any remaining verdancy is not added, and is lost.')
     ],
     3 => [
          "name" => clienttranslate('Watering Can'),
          "description" => clienttranslate('Add 1 verdancy to all plants surrounding a single room. You must choose which room you wish to use the watering can from, and add 1 verdancy to any incomplete plants surrounding that room.')
     ]
];

$this->_LIGHTNING_TYPES = [
     1 => ["name" => clienttranslate('Full Sun')],
     2 => ["name" => clienttranslate('semi-Shade')],
     3 => ["name" => clienttranslate('Shade')]
];


$this->_PLANT_TYPES = [
     1 => [
          "name" => clienttranslate('Succulent'),
          "color" => clienttranslate('purple')
     ],
     2 => [
          "name" => clienttranslate('Flowering'),
          "color" => clienttranslate('yellow')
     ],
     3 => [
          "name" => clienttranslate('Foliage'),
          "color" => clienttranslate('blue')
     ],
     4 => [
          "name" => clienttranslate('Vining'),
          "color" => clienttranslate('cyan')
     ],
     5 => [
          "name" => clienttranslate('Unusual'),
          "color" => clienttranslate('orange')
     ]
];


$this->_PLANT_GOAL_CARDS = [
     1 => [
          "name" => clienttranslate('Apartment Living'),
          "points" => 2,
          "description" => clienttranslate('for each completed plant with verdancy requirementof 4 or fewer')
     ],
     2 => [
          "name" => clienttranslate('Going Big'),
          "points" => 2,
          "description" => clienttranslate('for each completed plant with verdancy requirement of 7 or more')
     ],
     3 => [
          "name" => clienttranslate('On Vacation'),
          "points" => 2,
          "description" => clienttranslate('for each plant with 2 or fewer verdancy achieved')
     ],
     4 => [
          "name" => clienttranslate('Coordinated Alignment'),
          "points" => 3,
          "description" => clienttranslate('for each row with all the same plant type')
     ],
     5 => [
          "name" => clienttranslate('Picking Favorites'),
          "points" => 2,
          "description" => clienttranslate('for each missing plant type')
     ],
     6 => [
          "name" => clienttranslate('Mixing It Up'),
          "points" => 2,
          "description" => clienttranslate('for each row with unique plant types')
     ],
     7 => [
          "name" => clienttranslate('Perfect Conditions'),
          "points" => 1,
          "description" => clienttranslate('for each plant with perfectly matching lighting conditions')
     ],
     8 => [
          "name" => clienttranslate('Competitive Collections'),
          "points" => 2,
          "description" => clienttranslate('for most of any plant type (friendly ties)')
     ],
     9 => [
          "name" => clienttranslate('Every Shade Of Green'),
          "points" => 1,
          "description" => clienttranslate('for each different verdancy requirement')
     ],
     10 => [
          "name" => clienttranslate('Against All Odds'),
          "points" => 2,
          "description" => clienttranslate('for each plant that has no matching lighting conditions')
     ],
     11 => [
          "name" => clienttranslate('Loved Lines'),
          "points" => 2,
          "description" => clienttranslate('for each row with all plants completed')
     ],
     12 => [
          "name" => clienttranslate('Narrow Necessities'),
          "points" => 1,
          "description" => clienttranslate('for each completed plant that prefers only a single lighting condition')
     ],
     13 => [
          "name" => clienttranslate('One True Love'),
          "points" => 1,
          "description" => clienttranslate('for each plant of a type of your choice')
     ],
];


$this->_ITEM_GOAL_CARDS = [
     1 => [
          "name" => clienttranslate('Pot Pairs'),
          "points" => 2,
          "description" => clienttranslate('for each matching pair of pots')
     ],
     2 => [
          "name" => clienttranslate('Thumbs Up'),
          "points" => 1,
          "description" => clienttranslate('for each unspent green thumb')
     ],
     3 => [
          "name" => clienttranslate('Picky Potter'),
          "points" => 2,
          "description" => clienttranslate('for each missing pot type')
     ],
     4 => [
          "name" => clienttranslate('Delayed Gratification'),
          "points" => 2,
          "description" => clienttranslate('for each terra cotta pot')
     ],
     5 => [
          "name" => clienttranslate('Color Pairs'),
          "points" => 2,
          "description" => clienttranslate('for each pair of items of the same color')
     ],
     6 => [
          "name" => clienttranslate('The Spice Of Life'),
          "points" => 4,
          "description" => clienttranslate('for each set of all four pot types')
     ],
     7 => [
          "name" => clienttranslate('Creature Comforts'),
          "points" => 4,
          "description" => clienttranslate('for most pets (friendly ties)')
     ],
     8 => [
          "name" => clienttranslate('Furniture Aficionado'),
          "points" => 4,
          "description" => clienttranslate('for most furniture (friendly ties)')
     ],
     9 => [
          "name" => clienttranslate('Backup Plan'),
          "points" => 4,
          "description" => clienttranslate('for ending the game with an unused nurture token')
     ],
     10 => [
          "name" => clienttranslate('Clear The Way'),
          "points" => 4,
          "description" => clienttranslate('for each row with all rooms empty')
     ],
     11 => [
          "name" => clienttranslate('Three of a Kind'),
          "points" => 10,
          "description" => clienttranslate('for having three of the same item in your home')
     ],
     12 => [
          "name" => clienttranslate('Nobody to Impress'),
          "points" => 3,
          "description" => clienttranslate('for each matching item in a room that i not adjacent to any matching plants')
     ],
     13 => [
          "name" => clienttranslate('Implement of Choice'),
          "points" => 1,
          "description" => clienttranslate('for each nurture token used of a type of your choice')
     ],

];


$this->_ROOM_GOAL_CARDS = [
     1 => [
          "name" => clienttranslate('Triple Treatment'),
          "points" => 3,
          "description" => clienttranslate('for each room type with 3 or more rooms')
     ],
     2 => [
          "name" => clienttranslate('Matchy Matchy'),
          "points" => 1,
          "description" => clienttranslate('for each room with a matching item')
     ],
     3 => [
          "name" => clienttranslate('Double Duty'),
          "points" => 1,
          "description" => clienttranslate('for each room with two or more plant type matches')
     ],
     4 => [
          "name" => clienttranslate('Color Minimalist'),
          "points" => 2,
          "description" => clienttranslate('for each missing room type')
     ],
     5 => [
          "name" => clienttranslate('Coordinated Corridors'),
          "points" => 3,
          "description" => clienttranslate('for each row with all the same room type')
     ],
     6 => [
          "name" => clienttranslate('Diversified Designer'),
          "points" => 2,
          "description" => clienttranslate('for each row with unique room types')
     ],
     7 => [
          "name" => clienttranslate('Perfect Ambiance'),
          "points" => 1,
          "description" => clienttranslate('for each room with lightning conditions perfectly matched to all adjacent plants')
     ],
     8 => [
          "name" => clienttranslate('Colorful Competition'),
          "points" => 2,
          "description" => clienttranslate('for most of any room type (friendly ties)')
     ],
     9 => [
          "name" => clienttranslate('Chaotic Coordinator'),
          "points" => 2,
          "description" => clienttranslate('for each room with no plant type matches')
     ],
     10 => [
          "name" => clienttranslate('Four Corners'),
          "points" => 4,
          "description" => clienttranslate('for having the outer-most rooms in the four corners of your home be all the same type or all different types')
     ],
     11 => [
          "name" => clienttranslate('Balancing Act'),
          "points" => 4,
          "description" => clienttranslate('for having all rooms with at least one plant type match')
     ],
     12 => [
          "name" => clienttranslate('Match Three'),
          "points" => 3,
          "description" => clienttranslate('for each room with three or more plant type matches')
     ],
     13 => [
          "name" => clienttranslate('My Happy Place'),
          "points" => 'x2',
          "description" => clienttranslate('double the points from a single room of your choice')
     ],

];


$this->_PLANT_CARDS = [
     1 => [
          "name" => clienttranslate('Pincushion Cactus'),
          "type" => 1,
          "lightning" => [1],
          "verdancy" => 3,
          "points" => 3,
          "latin_name" => clienttranslate('Mammillaria celsiana'),
          "description" => clienttranslate('')
     ],
     2 => [
          "name" => clienttranslate('Mexican Snowball'),
          "type" => 1,
          "lightning" => [1, 2],
          "verdancy" => 3,
          "points" => 2,
          "latin_name" => clienttranslate('Echeveria elegans'),
          "description" => clienttranslate('')
     ],
     3 => [
          "name" => clienttranslate('Zebra Haworthia'),
          "type" => 1,
          "lightning" => [1, 2, 3],
          "verdancy" => 4,
          "points" => 2,
          "latin_name" => clienttranslate('Haworthia attenuata'),
          "description" => clienttranslate('')
     ],
     4 => [
          "name" => clienttranslate('Candelabra Cactus'),
          "type" => 1,
          "lightning" => [1],
          "verdancy" => 8,
          "points" => 10,
          "latin_name" => clienttranslate('Euphorbia ingens'),
          "description" => clienttranslate('')
     ],
     5 => [
          "name" => clienttranslate('Aloe'),
          "type" => 1,
          "lightning" => [1],
          "verdancy" => 5,
          "points" => 6,
          "latin_name" => clienttranslate('Aloe vera'),
          "description" => clienttranslate('')
     ],
     6 => [
          "name" => clienttranslate('Panda Plant'),
          "type" => 1,
          "lightning" => [1, 2],
          "verdancy" => 6,
          "points" => 6,
          "latin_name" => clienttranslate('Kalanchoe tomentosa'),
          "description" => clienttranslate('')
     ],
     7 => [
          "name" => clienttranslate('Ponytail Palm'),
          "type" => 1,
          "lightning" => [1, 2],
          "verdancy" => 7,
          "points" => 8,
          "latin_name" => clienttranslate('Beaucarnea recurvata'),
          "description" => clienttranslate('')
     ],
     8 => [
          "name" => clienttranslate('Jade'),
          "type" => 1,
          "lightning" => [1, 2],
          "verdancy" => 7,
          "points" => 8,
          "latin_name" => clienttranslate('Crassula ovata'),
          "description" => clienttranslate('')
     ],
     9 => [
          "name" => clienttranslate('Hens and Chicks'),
          "type" => 1,
          "lightning" => [1],
          "verdancy" => 3,
          "points" => 3,
          "latin_name" => clienttranslate('Sempervivum tectorum'),
          "description" => clienttranslate('')
     ],
     10 => [
          "name" => clienttranslate('Burro\'s Tail'),
          "type" => 1,
          "lightning" => [2],
          "verdancy" => 4,
          "points" => 4,
          "latin_name" => clienttranslate('Sedum morganianum'),
          "description" => clienttranslate('')
     ],
     11 => [
          "name" => clienttranslate('Christmas Cactus'),
          "type" => 1,
          "lightning" => [2],
          "verdancy" => 4,
          "points" => 4,
          "latin_name" => clienttranslate('Schlumbergera x buckleyi'),
          "description" => clienttranslate('')
     ],
     12 => [
          "name" => clienttranslate('String of Pearls'),
          "type" => 1,
          "lightning" => [1, 2],
          "verdancy" => 6,
          "points" => 6,
          "latin_name" => clienttranslate('Senecio rowleyanus'),
          "description" => clienttranslate('')
     ],
     13 => [
          "name" => clienttranslate('Bird of Paradise'),
          "type" => 2,
          "lightning" => [1, 2],
          "verdancy" => 8,
          "points" => 9,
          "latin_name" => clienttranslate('Strelitzia reginae'),
          "description" => clienttranslate('')
     ],
     14 => [
          "name" => clienttranslate('Amaryllis'),
          "type" => 2,
          "lightning" => [1, 2],
          "verdancy" => 4,
          "points" => 3,
          "latin_name" => clienttranslate('Hippeastrum'),
          "description" => clienttranslate('')
     ],
     15 => [
          "name" => clienttranslate('Florist\'s Cyclamen'),
          "type" => 2,
          "lightning" => [2],
          "verdancy" => 3,
          "points" => 3,
          "latin_name" => clienttranslate('Cyclamen persicum'),
          "description" => clienttranslate('')
     ],
     16 => [
          "name" => clienttranslate('African Violet'),
          "type" => 2,
          "lightning" => [2],
          "verdancy" => 4,
          "points" => 4,
          "latin_name" => clienttranslate('Saintpaulia ionantha'),
          "description" => clienttranslate('')
     ],
     17 => [
          "name" => clienttranslate('Scarlet Star'),
          "type" => 2,
          "lightning" => [2],
          "verdancy" => 5,
          "points" => 6,
          "latin_name" => clienttranslate('Guzmania lingulata'),
          "description" => clienttranslate('')
     ],
     18 => [
          "name" => clienttranslate('Moth Orchid'),
          "type" => 2,
          "lightning" => [2],
          "verdancy" => 6,
          "points" => 7,
          "latin_name" => clienttranslate('Phalaenopsis amabilis'),
          "description" => clienttranslate('')
     ],
     19 => [
          "name" => clienttranslate('Peace Lily'),
          "type" => 2,
          "lightning" => [2, 3],
          "verdancy" => 5,
          "points" => 5,
          "latin_name" => clienttranslate('Spathiphyllum wallisii'),
          "description" => clienttranslate('')
     ],
     20 => [
          "name" => clienttranslate('Angel Wing Begonia'),
          "type" => 2,
          "lightning" => [1, 2, 3],
          "verdancy" => 7,
          "points" => 7,
          "latin_name" => clienttranslate('Begonia coccinea'),
          "description" => clienttranslate('')
     ],
     21 => [
          "name" => clienttranslate('Common Geranium'),
          "type" => 2,
          "lightning" => [1, 2],
          "verdancy" => 5,
          "points" => 5,
          "latin_name" => clienttranslate('Pelargonium x hortorum'),
          "description" => clienttranslate('')
     ],
     22 => [
          "name" => clienttranslate('Bush Lily'),
          "type" => 2,
          "lightning" => [2],
          "verdancy" => 5,
          "points" => 6,
          "latin_name" => clienttranslate('Clivia miniata'),
          "description" => clienttranslate('')
     ],
     23 => [
          "name" => clienttranslate('False Shamrock'),
          "type" => 2,
          "lightning" => [1, 2, 3],
          "verdancy" => 5,
          "points" => 4,
          "latin_name" => clienttranslate('Oxalis triangularis'),
          "description" => clienttranslate('')
     ],
     24 => [
          "name" => clienttranslate('Laceleaf'),
          "type" => 2,
          "lightning" => [2, 3],
          "verdancy" => 5,
          "points" => 5,
          "latin_name" => clienttranslate('Anthurium andraeanum'),
          "description" => clienttranslate('')
     ],
     25 => [
          "name" => clienttranslate('Back-Eyed susan Vine'),
          "type" => 4,
          "lightning" => [1],
          "verdancy" => 7,
          "points" => 9,
          "latin_name" => clienttranslate('Thunbergia alata'),
          "description" => clienttranslate('')
     ],
     26 => [
          "name" => clienttranslate('Inch Plant'),
          "type" => 4,
          "lightning" => [1, 2],
          "verdancy" => 5,
          "points" => 5,
          "latin_name" => clienttranslate('Tradesciantia zebrina'),
          "description" => clienttranslate('')
     ],
     27 => [
          "name" => clienttranslate('Hoya'),
          "type" => 4,
          "lightning" => [1, 2, 3],
          "verdancy" => 8,
          "points" => 8,
          "latin_name" => clienttranslate('Hoya carnosa'),
          "description" => clienttranslate('')
     ],
     28 => [
          "name" => clienttranslate('Jasmine'),
          "type" => 4,
          "lightning" => [1, 2],
          "verdancy" => 9,
          "points" => 11,
          "latin_name" => clienttranslate('Jasminum polyanthum'),
          "description" => clienttranslate('')
     ],
     29 => [
          "name" => clienttranslate('Common Ivy'),
          "type" => 4,
          "lightning" => [1, 2],
          "verdancy" => 9,
          "points" => 11,
          "latin_name" => clienttranslate('Hedera helix'),
          "description" => clienttranslate('')
     ],
     30 => [
          "name" => clienttranslate('Arrowhead Vine'),
          "type" => 4,
          "lightning" => [2, 3],
          "verdancy" => 4,
          "points" => 3,
          "latin_name" => clienttranslate('Syngonium podophyllum'),
          "description" => clienttranslate('')
     ],
     31 => [
          "name" => clienttranslate('Heartleaf Philodendron'),
          "type" => 4,
          "lightning" => [2, 3],
          "verdancy" => 7,
          "points" => 8,
          "latin_name" => clienttranslate('Philodendron hederaceum'),
          "description" => clienttranslate('')
     ],
     32 => [
          "name" => clienttranslate('Devil\'s Ivy'),
          "type" => 4,
          "lightning" => [2, 3],
          "verdancy" => 9,
          "points" => 11,
          "latin_name" => clienttranslate('Epipremnum aureum'),
          "description" => clienttranslate('')
     ],
     33 => [
          "name" => clienttranslate('Creeping Fig'),
          "type" => 4,
          "lightning" => [2, 3],
          "verdancy" => 7,
          "points" => 8,
          "latin_name" => clienttranslate('Ficus pumila'),
          "description" => clienttranslate('')
     ],
     34 => [
          "name" => clienttranslate('Chestnut Vine'),
          "type" => 4,
          "lightning" => [2, 3],
          "verdancy" => 9,
          "points" => 11,
          "latin_name" => clienttranslate('Tetrastigma voinierianum'),
          "description" => clienttranslate('')
     ],
     35 => [
          "name" => clienttranslate('Kangaroo Vine'),
          "type" => 4,
          "lightning" => [1, 2, 3],
          "verdancy" => 8,
          "points" => 8,
          "latin_name" => clienttranslate('Cissus antarctica'),
          "description" => clienttranslate('')
     ],
     36 => [
          "name" => clienttranslate('Betel'),
          "type" => 4,
          "lightning" => [2, 3],
          "verdancy" => 6,
          "points" => 6,
          "latin_name" => clienttranslate('Piper betle'),
          "description" => clienttranslate('')
     ],
     37 => [
          "name" => clienttranslate('Venus Fly Trap'),
          "type" => 5,
          "lightning" => [1],
          "verdancy" => 3,
          "points" => 3,
          "latin_name" => clienttranslate('Dionaea muscipula'),
          "description" => clienttranslate('')
     ],
     38 => [
          "name" => clienttranslate('Coral Cactus'),
          "type" => 5,
          "lightning" => [1],
          "verdancy" => 4,
          "points" => 4,
          "latin_name" => clienttranslate('Euphorbia neriifolia+lactea'),
          "description" => clienttranslate('')
     ],
     39 => [
          "name" => clienttranslate('Nerve Plant'),
          "type" => 5,
          "lightning" => [1, 2, 3],
          "verdancy" => 4,
          "points" => 2,
          "latin_name" => clienttranslate('Fittonia verschaffeltii'),
          "description" => clienttranslate('')
     ],
     40 => [
          "name" => clienttranslate('Ficus Bonsai'),
          "type" => 5,
          "lightning" => [1, 2],
          "verdancy" => 4,
          "points" => 3,
          "latin_name" => clienttranslate('Ficus retusa'),
          "description" => clienttranslate('')
     ],
     41 => [
          "name" => clienttranslate('Rabbit\'s Foot Fern'),
          "type" => 5,
          "lightning" => [2, 3],
          "verdancy" => 5,
          "points" => 5,
          "latin_name" => clienttranslate('Davallia fejeensis'),
          "description" => clienttranslate('')
     ],
     42 => [
          "name" => clienttranslate('Living Stone'),
          "type" => 5,
          "lightning" => [1],
          "verdancy" => 3,
          "points" => 3,
          "latin_name" => clienttranslate('Lithops otzeniana'),
          "description" => clienttranslate('')
     ],
     43 => [
          "name" => clienttranslate('Corkscrew Albuca'),
          "type" => 5,
          "lightning" => [1, 2],
          "verdancy" => 3,
          "points" => 2,
          "latin_name" => clienttranslate('Albuca spiralis'),
          "description" => clienttranslate('')
     ],
     44 => [
          "name" => clienttranslate('Corpse Flower'),
          "type" => 5,
          "lightning" => [2],
          "verdancy" => 8,
          "points" => 10,
          "latin_name" => clienttranslate('Amorphophallus titanum'),
          "description" => clienttranslate('')
     ],
     45 => [
          "name" => clienttranslate('Cushion Moss'),
          "type" => 5,
          "lightning" => [2, 3],
          "verdancy" => 4,
          "points" => 3,
          "latin_name" => clienttranslate('Leucobryum glaucum'),
          "description" => clienttranslate('')
     ],
     46 => [
          "name" => clienttranslate('Chenille Plant'),
          "type" => 5,
          "lightning" => [1, 2],
          "verdancy" => 8,
          "points" => 9,
          "latin_name" => clienttranslate('Acalypha hispida'),
          "description" => clienttranslate('')
     ],
     47 => [
          "name" => clienttranslate('Sensitive Plant'),
          "type" => 5,
          "lightning" => [1],
          "verdancy" => 5,
          "points" => 6,
          "latin_name" => clienttranslate('Mimosa pudica'),
          "description" => clienttranslate('')
     ],
     48 => [
          "name" => clienttranslate('Cooper\'s Haworthia'),
          "type" => 5,
          "lightning" => [1, 2],
          "verdancy" => 4,
          "points" => 3,
          "latin_name" => clienttranslate('Haworthia cooperi'),
          "description" => clienttranslate('')
     ],
     49 => [
          "name" => clienttranslate('Lucky Bamboo'),
          "type" => 3,
          "lightning" => [1, 2],
          "verdancy" => 6,
          "points" => 6,
          "latin_name" => clienttranslate('Dracaena sanderiana'),
          "description" => clienttranslate('')
     ],
     50 => [
          "name" => clienttranslate('Elephant\'s Ear'),
          "type" => 3,
          "lightning" => [1, 2],
          "verdancy" => 8,
          "points" => 9,
          "latin_name" => clienttranslate('Alocasia calidora'),
          "description" => clienttranslate('')
     ],
     51 => [
          "name" => clienttranslate('Spider Plant'),
          "type" => 3,
          "lightning" => [1, 2, 3],
          "verdancy" => 6,
          "points" => 5,
          "latin_name" => clienttranslate('Chlorophytum comosum'),
          "description" => clienttranslate('')
     ],
     52 => [
          "name" => clienttranslate('ZZ Plant'),
          "type" => 3,
          "lightning" => [1, 2, 3],
          "verdancy" => 6,
          "points" => 5,
          "latin_name" => clienttranslate('Zamioculcas zamiifolia'),
          "description" => clienttranslate('')
     ],
     53 => [
          "name" => clienttranslate('Snake Plant'),
          "type" => 3,
          "lightning" => [1, 2, 3],
          "verdancy" => 7,
          "points" => 7,
          "latin_name" => clienttranslate('Sanseveiria trifasciata'),
          "description" => clienttranslate('')
     ],
     54 => [
          "name" => clienttranslate('Coin Plant'),
          "type" => 3,
          "lightning" => [2],
          "verdancy" => 4,
          "points" => 4,
          "latin_name" => clienttranslate('Pilea peperonmioides'),
          "description" => clienttranslate('')
     ],
     55 => [
          "name" => clienttranslate('Norfolk Island Pine'),
          "type" => 3,
          "lightning" => [1, 2],
          "verdancy" => 7,
          "points" => 8,
          "latin_name" => clienttranslate('Araucaria Heterophylla'),
          "description" => clienttranslate('')
     ],
     56 => [
          "name" => clienttranslate('Swiss Cheese Plant'),
          "type" => 3,
          "lightning" => [2, 3],
          "verdancy" => 7,
          "points" => 8,
          "latin_name" => clienttranslate('Monstera deliciosa'),
          "description" => clienttranslate('')
     ],
     57 => [
          "name" => clienttranslate('Fiddle Leaf fig'),
          "type" => 3,
          "lightning" => [1, 2, 3],
          "verdancy" => 8,
          "points" => 8,
          "latin_name" => clienttranslate('Ficus lyrata'),
          "description" => clienttranslate('')
     ],
     58 => [
          "name" => clienttranslate('Player Plant'),
          "type" => 3,
          "lightning" => [2, 3],
          "verdancy" => 4,
          "points" => 3,
          "latin_name" => clienttranslate('Maranta leuconeura'),
          "description" => clienttranslate('')
     ],
     59 => [
          "name" => clienttranslate('Parlor Palm'),
          "type" => 3,
          "lightning" => [1, 2],
          "verdancy" => 7,
          "points" => 8,
          "latin_name" => clienttranslate('Chamaedorea elegans'),
          "description" => clienttranslate('')
     ],
     60 => [
          "name" => clienttranslate('Maidenhair Fern'),
          "type" => 3,
          "lightning" => [2, 3],
          "verdancy" => 5,
          "points" => 5,
          "latin_name" => clienttranslate('Adiantum pedatum'),
          "description" => clienttranslate('')
     ],
     61 => [
          "name" => clienttranslate('Fishbone Cactus'),
          "type" => 1,
          "lightning" => [2, 3],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Disocactus anguliger'),
          "description" => clienttranslate('Named for its jagged leaf shape. Care must be taken when handling this plant as it has tiny haires that can stick into bare skin.')
     ],
     62 => [
          "name" => clienttranslate('Misteltoe Cactus'),
          "type" => 1,
          "lightning" => [2, 3],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Rhipsalis baccifera'),
          "description" => clienttranslate('This tropical rainforest-dwelling caactus hangs from trees in its natural habitat. It is known for its fine texture and trailing form.')
     ],
     63 => [
          "name" => clienttranslate('Air Plant'),
          "type" => 2,
          "lightning" => [1, 2],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Tillandsia aeranthos'),
          "description" => clienttranslate('Tillandsias are part of the Bromeliad family, making them close relatives of pineapples. These plants are able to pull both nutrients ans moisture from the air.')
     ],
     64 => [
          "name" => clienttranslate('Cattleya Orchid'),
          "type" => 2,
          "lightning" => [2, 3],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Cattleya labiata'),
          "description" => clienttranslate('Produces large, flagrant lowers with very showy colors. Its blossoms are often used in corsages. A slow grower, it may take 4-7 years to mature from seed.')
     ],
     65 => [
          "name" => clienttranslate('Staghorn Fern'),
          "type" => 3,
          "lightning" => [2, 3],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Platycerium superbum'),
          "description" => clienttranslate('Some classify these plants as eusocial, since they can grow in colonies where some focus on reproduction while others on gathering shared water and nutrients')
     ],
     66 => [
          "name" => clienttranslate('Fern Kokedama'),
          "type" => 3,
          "lightning" => [2, 3],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Asplenium nidus'),
          "description" => clienttranslate('In this Japanese planting style, a plant is placed into a soil mixture, wrapped in moss, then tied with twine. Ferns are commonly used for this technique.')
     ],
     67 => [
          "name" => clienttranslate('Spanish Moss'),
          "type" => 3,
          "lightning" => [1, 2],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Tillandsia usneoides'),
          "description" => clienttranslate('Found in the southern US and Latin America, this plant often lives on Oak and Cypress trees. It isnt actually a true moss - it\'s a kind of bromeliad.')
     ],
     68 => [
          "name" => clienttranslate('Kangaroo Pocket'),
          "type" => 3,
          "lightning" => [2, 3],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Dischidia vidalil'),
          "description" => clienttranslate('This plant has a symbiotic relationships with ants. Its root-filled pouches provide shelter for ants, who help protect the plant and nourish its roots with their waste.')
     ],
     69 => [
          "name" => clienttranslate('Marimo'),
          "type" => 3,
          "lightning" => [2, 3],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Aegagropila linnaei'),
          "description" => clienttranslate('A type of algae that forms into spherical balls that float during the day and sink at night. They are native to cold, freshwater lakes in N. Europe and Japan')
     ],
     70 => [
          "name" => clienttranslate('Chia Sculpture'),
          "type" => 3,
          "lightning" => [1, 2],
          "verdancy" => 0,
          "points" => 2,
          "latin_name" => clienttranslate('Salvia hispanica'),
          "description" => clienttranslate('These terra cotta sculptures started as a 1970s fad and come into many shapes. Moist chia seeds are spread on top and sprout to becomme the sculptures\'s "hair"')
     ]
];


$this->_ROOM_CARDS = [
     1 => [
          "type" => 1,
          "lightning" => ["north" => 1, "east" => 3, "south" => 2, "west" => 1]
     ],
     2 => [
          "type" => 1,
          "lightning" => ["north" => 2, "east" => 1, "south" => 1, "west" => 3]
     ],
     3 => [
          "type" => 1,
          "lightning" => ["north" => 1, "east" => 1, "south" => 3, "west" => 2]
     ],
     4 => [
          "type" => 1,
          "lightning" => ["north" => 3, "east" => 2, "south" => 1, "west" => 1]
     ],
     5 => [
          "type" => 1,
          "lightning" => ["north" => 1, "east" => 3, "south" => 1, "west" => 2]
     ],
     6 => [
          "type" => 1,
          "lightning" => ["north" => 1, "east" => 2, "south" => 1, "west" => 3]
     ],
     7 => [
          "type" => 1,
          "lightning" => ["north" => 3, "east" => 1, "south" => 2, "west" => 1]
     ],
     8 => [
          "type" => 1,
          "lightning" => ["north" => 2, "east" => 1, "south" => 3, "west" => 1]
     ],
     9 => [
          "type" => 1,
          "lightning" => ["north" => 1, "east" => 2, "south" => 3, "west" => 1]
     ],
     10 => [
          "type" => 1,
          "lightning" => ["north" => 3, "east" => 1, "south" => 1, "west" => 2]
     ],
     11 => [
          "type" => 1,
          "lightning" => ["north" => 2, "east" => 3, "south" => 1, "west" => 1]
     ],
     12 => [
          "type" => 1,
          "lightning" => ["north" => 1, "east" => 1, "south" => 2, "west" => 3]
     ],
     13 => [
          "type" => 2,
          "lightning" => ["north" => 2, "east" => 1, "south" => 2, "west" => 3]
     ],
     14 => [
          "type" => 2,
          "lightning" => ["north" => 2, "east" => 3, "south" => 2, "west" => 1]
     ],
     15 => [
          "type" => 2,
          "lightning" => ["north" => 1, "east" => 2, "south" => 3, "west" => 2]
     ],
     16 => [
          "type" => 2,
          "lightning" => ["north" => 3, "east" => 2, "south" => 1, "west" => 2]
     ],
     17 => [
          "type" => 2,
          "lightning" => ["north" => 2, "east" => 3, "south" => 1, "west" => 2]
     ],
     18 => [
          "type" => 2,
          "lightning" => ["north" => 3, "east" => 2, "south" => 2, "west" => 1]
     ],
     19 => [
          "type" => 2,
          "lightning" => ["north" => 2, "east" => 2, "south" => 3, "west" => 1]
     ],
     20 => [
          "type" => 2,
          "lightning" => ["north" => 3, "east" => 1, "south" => 2, "west" => 2]
     ],
     21 => [
          "type" => 2,
          "lightning" => ["north" => 2, "east" => 2, "south" => 1, "west" => 3]
     ],
     22 => [
          "type" => 2,
          "lightning" => ["north" => 1, "east" => 3, "south" => 2, "west" => 2]
     ],
     23 => [
          "type" => 2,
          "lightning" => ["north" => 1, "east" => 2, "south" => 1, "west" => 3]
     ],
     24 => [
          "type" => 2,
          "lightning" => ["north" => 2, "east" => 1, "south" => 3, "west" => 1]
     ],
     25 => [
          "type" => 3,
          "lightning" => ["north" => 2, "east" => 3, "south" => 2, "west" => 1]
     ],
     26 => [
          "type" => 3,
          "lightning" => ["north" => 2, "east" => 1, "south" => 3, "west" => 2]
     ],
     27 => [
          "type" => 3,
          "lightning" => ["north" => 3, "east" => 2, "south" => 1, "west" => 2]
     ],
     28 => [
          "type" => 3,
          "lightning" => ["north" => 1, "east" => 2, "south" => 2, "west" => 3]
     ],
     29 => [
          "type" => 3,
          "lightning" => ["north" => 1, "east" => 1, "south" => 3, "west" => 2]
     ],
     30 => [
          "type" => 3,
          "lightning" => ["north" => 2, "east" => 3, "south" => 1, "west" => 1]
     ],
     31 => [
          "type" => 3,
          "lightning" => ["north" => 1, "east" => 2, "south" => 3, "west" => 1]
     ],
     32 => [
          "type" => 3,
          "lightning" => ["north" => 2, "east" => 1, "south" => 1, "west" => 3]
     ],
     33 => [
          "type" => 3,
          "lightning" => ["north" => 2, "east" => 2, "south" => 1, "west" => 3]
     ],
     34 => [
          "type" => 3,
          "lightning" => ["north" => 3, "east" => 1, "south" => 2, "west" => 2]
     ],
     35 => [
          "type" => 3,
          "lightning" => ["north" => 2, "east" => 1, "south" => 2, "west" => 3]
     ],
     36 => [
          "type" => 3,
          "lightning" => ["north" => 1, "east" => 2, "south" => 3, "west" => 2]
     ],
     37 => [
          "type" => 4,
          "lightning" => ["north" => 2, "east" => 3, "south" => 2, "west" => 1]
     ],
     38 => [
          "type" => 4,
          "lightning" => ["north" => 2, "east" => 1, "south" => 2, "west" => 3]
     ],
     39 => [
          "type" => 4,
          "lightning" => ["north" => 2, "east" => 2, "south" => 1, "west" => 3]
     ],
     40 => [
          "type" => 4,
          "lightning" => ["north" => 1, "east" => 3, "south" => 2, "west" => 2]
     ],
     41 => [
          "type" => 4,
          "lightning" => ["north" => 3, "east" => 1, "south" => 3, "west" => 2]
     ],
     42 => [
          "type" => 4,
          "lightning" => ["north" => 3, "east" => 2, "south" => 3, "west" => 1]
     ],
     43 => [
          "type" => 4,
          "lightning" => ["north" => 1, "east" => 3, "south" => 2, "west" => 3]
     ],
     44 => [
          "type" => 4,
          "lightning" => ["north" => 2, "east" => 3, "south" => 1, "west" => 3]
     ],
     45 => [
          "type" => 4,
          "lightning" => ["north" => 2, "east" => 1, "south" => 3, "west" => 2]
     ],
     46 => [
          "type" => 4,
          "lightning" => ["north" => 3, "east" => 2, "south" => 1, "west" => 1]
     ],
     47 => [
          "type" => 4,
          "lightning" => ["north" => 3, "east" => 2, "south" => 2, "west" => 1]
     ],
     48 => [
          "type" => 4,
          "lightning" => ["north" => 1, "east" => 1, "south" => 3, "west" => 2]
     ],
     49 => [
          "type" => 5,
          "lightning" => ["north" => 1, "east" => 1, "south" => 2, "west" => 3]
     ],
     50 => [
          "type" => 5,
          "lightning" => ["north" => 1, "east" => 2, "south" => 3, "west" => 1]
     ],
     51 => [
          "type" => 5,
          "lightning" => ["north" => 2, "east" => 3, "south" => 1, "west" => 1]
     ],
     52 => [
          "type" => 5,
          "lightning" => ["north" => 3, "east" => 1, "south" => 1, "west" => 2]
     ],
     53 => [
          "type" => 5,
          "lightning" => ["north" => 3, "east" => 1, "south" => 2, "west" => 2]
     ],
     54 => [
          "type" => 5,
          "lightning" => ["north" => 2, "east" => 2, "south" => 3, "west" => 1]
     ],
     55 => [
          "type" => 5,
          "lightning" => ["north" => 2, "east" => 3, "south" => 1, "west" => 2]
     ],
     56 => [
          "type" => 5,
          "lightning" => ["north" => 1, "east" => 2, "south" => 2, "west" => 3]
     ],
     57 => [
          "type" => 5,
          "lightning" => ["north" => 2, "east" => 1, "south" => 3, "west" => 1]
     ],
     58 => [
          "type" => 5,
          "lightning" => ["north" => 1, "east" => 2, "south" => 1, "west" => 3]
     ],
     59 => [
          "type" => 5,
          "lightning" => ["north" => 3, "east" => 1, "south" => 2, "west" => 1]
     ],
     60 => [
          "type" => 5,
          "lightning" => ["north" => 1, "east" => 3, "south" => 1, "west" => 2]
     ]
];
