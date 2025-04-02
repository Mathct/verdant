<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * verdant implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Priol> <camertwo@hotmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\verdant;

use BgaUserException;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

include('Pending.php'); // ATTENTION
include('PendingSolo.php'); // ATTENTION

class Game extends \Table
{
    public array $_ITEM_TYPES;
    public array $_POT_TYPES; // ATTENTION
    public array $_NURTURE_TYPES;
    public array $_LIGHTNING_TYPES;
    public array $_PLANT_TYPES;
    public array $_PLANT_GOAL_CARDS;
    public array $_ITEM_GOAL_CARDS;
    public array $_ROOM_GOAL_CARDS;
    public array $_PLANT_CARDS;
    public array $_ITEM_CARDS;
    public array $_ROOM_CARDS;

    public static $instance = null; //ATTENTION

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        require 'material.inc.php';

        $this->initGameStateLabels([

            "game_mode" => 100,
            "plant_goal" => 10,
            "room_goal" => 11,
            "item_goal" => 12,
        ]);

        self::$instance = $this; // ATTENTION

        $this->plant = self::getNew("module.common.deck");
        $this->plant->init("plant");
        $this->room = self::getNew("module.common.deck");
        $this->room->init("room");
        $this->tile = self::getNew("module.common.deck");
        $this->tile->init("tile");
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "verdant";
    }


    /////////////////////////////////////////////////////////////////////////////////  
    //       _____                        _____       _ _   _       _ _          _   _             
    //      / ____|                      |_   _|     (_) | (_)     | (_)        | | (_)            
    //     | |  __  __ _ _ __ ___   ___    | |  _ __  _| |_ _  __ _| |_ ______ _| |_ _  ___  _ __  
    //     | | |_ |/ _` | '_ ` _ \ / _ \   | | | '_ \| | __| |/ _` | | |_  / _` | __| |/ _ \| '_ \ 
    //     | |__| | (_| | | | | | |  __/  _| |_| | | | | |_| | (_| | | |/ / (_| | |_| | (_) | | | |
    //      \_____|\__,_|_| |_| |_|\___| |_____|_| |_|_|\__|_|\__,_|_|_/___\__,_|\__|_|\___/|_| |_|
    //                                                                                               
    /////////////////////////////////////////////////////////////////////////////////    


    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        //$this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        /*Init Stats */

        self::initStat('table', 'turns_number', 0);

        self::initStat('player', 'turns_number', 0);
        self::initStat('player', 'completed_plants', 0);
        self::initStat('player', 'extra_verdancy', 0);
        self::initStat('player', 'pot_bonus', 0);
        self::initStat('player', 'room_bonus', 0);
        self::initStat('player', 'furniture_pets_number', 0);
        self::initStat('player', 'plant_collector_bonus', 0);
        self::initStat('player', 'room_collector_bonus', 0);
        self::initStat('player', 'plant_goal', 0);
        self::initStat('player', 'item_goal', 0);
        self::initStat('player', 'room_goal', 0);
        self::initStat('player', 'fertilizer_used', 0);
        self::initStat('player', 'hand_trowel_used', 0);
        self::initStat('player', 'watering_can', 0);



        /* Init Objectifs */

        if ($this->getGameStateValue('game_mode') == 2) {
            $rand1 = bga_rand(1, 10);
            $this->setGameStateInitialValue('plant_goal', $rand1);
            $rand2 = bga_rand(1, 10);
            $this->setGameStateInitialValue('room_goal', $rand2);
            $rand3 = bga_rand(1, 10);
            $this->setGameStateInitialValue('item_goal', $rand3);
        }


        $nbreplayers = count($players);

        /* init Plant et Room */

        for ($i = 1; $i <= 60; $i++) {

            $card[] = array('type' => $i, 'type_arg' => 0, 'nbr' => 1);
        }

        $this->plant->createCards($card, 'deck');
        $this->plant->shuffle('deck');

        $this->room->createCards($card, 'deck');
        $this->room->shuffle('deck');

        /* init Tile*/

        for ($i = 11; $i <= 19; $i++) {

            $tile[] = array('type' => $i, 'type_arg' => 0, 'nbr' => 1);
        }
        for ($i = 21; $i <= 29; $i++) {

            $tile[] = array('type' => $i, 'type_arg' => 0, 'nbr' => 1);
        }
        for ($i = 31; $i <= 39; $i++) {

            $tile[] = array('type' => $i, 'type_arg' => 0, 'nbr' => 1);
        }
        for ($i = 41; $i <= 49; $i++) {

            $tile[] = array('type' => $i, 'type_arg' => 0, 'nbr' => 1);
        }
        for ($i = 51; $i <= 59; $i++) {

            $tile[] = array('type' => $i, 'type_arg' => 0, 'nbr' => 1);
        }

        for ($i = 61; $i <= 63; $i++) {

            $tile[] = array('type' => $i, 'type_arg' => 0, 'nbr' => 15);
        }

        $this->tile->createCards($tile, 'deck');
        $this->tile->shuffle('deck');


        /* init Pot*/

        if ($nbreplayers == 1) {

            $position_market = 1;

            for ($i = 3; $i >= 1; $i--) {
                for ($j = 1; $j <= 4; $j++) {

                    if ($i == 3) {
                        self::DbQuery("INSERT INTO pot (card_type, card_location, card_location_arg) VALUES ($i, 'market', $position_market)");
                        $position_market++;
                    } else {
                        self::DbQuery("INSERT INTO pot (card_type, card_location) VALUES ($i, 'deck')");
                    }
                }
            }
        }

        if ($nbreplayers == 2) {
            for ($i = 3; $i >= 1; $i--) {
                for ($j = 1; $j <= 3; $j++) {
                    self::DbQuery("INSERT INTO pot (card_type, card_location) VALUES ($i, 'deck')");
                }
            }
        }

        if ($nbreplayers == 3) {
            for ($i = 3; $i >= 1; $i--) {
                for ($j = 1; $j <= 4; $j++) {
                    self::DbQuery("INSERT INTO pot (card_type, card_location) VALUES ($i, 'deck')");
                }
            }
        }

        if ($nbreplayers == 4) {
            for ($i = 3; $i >= 1; $i--) {
                for ($j = 1; $j <= 5; $j++) {
                    self::DbQuery("INSERT INTO pot (card_type, card_location) VALUES ($i, 'deck')");
                }
            }
        }

        if ($nbreplayers == 5) {
            for ($i = 3; $i >= 1; $i--) {
                for ($j = 1; $j <= 6; $j++) {
                    self::DbQuery("INSERT INTO pot (card_type, card_location) VALUES ($i, 'deck')");
                }
            }
        }

        for ($i = 1; $i <= 18; $i++) {
            self::DbQuery("INSERT INTO pot (card_type, card_location) VALUES (0, 'deck')");
        }


        /* init Thumb */

        if ($nbreplayers == 1) {
            self::DbQuery("UPDATE player set player_thumb = 2 WHERE player_no = 1");
        }

        if ($nbreplayers >= 2) {
            foreach ($players as $player_id => $player) {

                $noplayer = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id={$player_id}");

                if ($noplayer == $nbreplayers) {
                    self::DbQuery("UPDATE player set player_thumb = 2 WHERE player_id = {$player_id}");
                }
                if (($noplayer != 1) && ($noplayer != $nbreplayers)) {
                    self::DbQuery("UPDATE player set player_thumb = 1 WHERE player_id = {$player_id}");
                }
            }
        }

        /* init market */

        for ($i = 1; $i <= 4; $i++) {
            $this->plant->pickCardForLocation('deck', 'market', $i);
            $this->room->pickCardForLocation('deck', 'market', $i);
            $this->tile->pickCardForLocation('deck', 'market', $i);
        }



        /* init First Turn */

        foreach ($players as $player_id => $player) {

            $this->room->pickCardForLocation('deck', $player_id, 35);
            $this->plant->pickCardForLocation('deck', $player_id, 99);
        }
        //il faut donner au premier joueur la plante la plus haute
        if ($nbreplayers >= 2) {
            $first_player_id = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = 1");
            $type_plant_first_player = self::getUniqueValueFromDB("SELECT card_type FROM plant WHERE card_location = '{$first_player_id}' AND card_location_arg = 99");

            $all_plants_99 = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location_arg = 99", true);
            $verdancy = 0;
            $type_plant_max = 0;
            $player_with_max_plant = $first_player_id;

            foreach ($all_plants_99 as $plant) {
                $valeur = $this->_PLANT_CARDS[$plant]['verdancy'];
                if ($valeur >= $verdancy) {
                    $verdancy = $valeur;
                    $type_plant_max = $plant;
                    $player_with_max_plant = self::getUniqueValueFromDB("SELECT card_location FROM plant WHERE card_type = '{$plant}'");
                }
            }

            if ($type_plant_max != $type_plant_first_player) {
                self::DbQuery("UPDATE plant set card_location = '{$first_player_id}' WHERE card_type = '{$type_plant_max}'");
                self::DbQuery("UPDATE plant set card_location = '{$player_with_max_plant}' WHERE card_type = '{$type_plant_first_player}'");
            }
        }








        /************ Init Pending *****/


        foreach ($players as $player_id => $player) {
            $this->addPendingFirst($player_id, "NormalTurn");
        }
    }

    /////////////////////////////////////////////////////////////////////////////////  
    //               _            _ _ _____        _            
    //              | |     /\   | | |  __ \      | |           
    //     __ _  ___| |_   /  \  | | | |  | | __ _| |_ __ _ ___ 
    //    / _` |/ _ \ __| / /\ \ | | | |  | |/ _` | __/ _` / __|
    //   | (_| |  __/ |_ / ____ \| | | |__| | (_| | || (_| \__ \
    //    \__, |\___|\__/_/    \_\_|_|_____/ \__,_|\__\__,_|___/
    //     __/ |                                                
    //    |___/                                                 
    /////////////////////////////////////////////////////////////////////////////////  

    protected function getAllDatas(): array
    {
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $current_player_id = (int) $this->getCurrentPlayerId();

        $sql = "SELECT player_no no FROM player WHERE player_id = $current_player_id";
        $current_player_no = $this->getUniqueValueFromDb($sql);
        if (is_null($current_player_no)) {
            $current_player_no = 0;
        }

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $result["players"] = $this->getCollectionFromDb(
            "SELECT `player_id` `id`, `player_score` `score`, `player_thumb` `thumb` FROM `player`"
        );

        // Players ordered with current player first
        $sql = "SELECT player_no no, player_id id, player_score score, player_name name, player_color color, player_thumb thumb 
            FROM player ";
        $sql .= " ORDER BY (player_no >= $current_player_no) DESC, player_no ASC";
        $result['players_ordered'] = $this->getObjectListFromDB($sql);

        $result['game_mode'] = $this->getGameStateValue('game_mode');

        $result["plants"] = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_location NOT IN ('deck', 'discard')");
        $result["rooms"] = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM room WHERE card_location NOT IN ('deck', 'discard')");
        $result["tiles"] = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_location NOT IN ('deck', 'discard')");
        $result["pots"] = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM pot WHERE card_location != 'deck'");

        $result["plant_goal"] = self::getGameStateValue('plant_goal');
        $result["item_goal"] = self::getGameStateValue('item_goal');
        $result["room_goal"] = self::getGameStateValue('room_goal');


        $result["nb_pots_in_market"] = $this->getCollectionFromDB("SELECT card_type, COUNT(*) AS count FROM pot WHERE card_location = 'deck' GROUP BY card_type ORDER BY card_type", true);
        $result["nb_pots_in_discard"] = $this->getCollectionFromDB("SELECT card_type, COUNT(*) AS count FROM pot WHERE card_location = 'discard' GROUP BY card_type ORDER BY card_type", true);
        $result["pots_in_market"] = $this->getObjectListFromDB("SELECT card_id id, card_type type, card_location_arg location FROM pot WHERE card_location = 'market'");


        $result["plants_deck"] = self::getUniqueValuefromDB("SELECT COUNT(card_id) FROM plant WHERE card_location = 'deck'");
        $result["rooms_deck"] = self::getUniqueValuefromDB("SELECT COUNT(card_id) FROM room WHERE card_location = 'deck'");

        $result["item_types"] = $this->_ITEM_TYPES;
        $result["nurture_types"] = $this->_NURTURE_TYPES;
        $result["lightning_types"] = $this->_LIGHTNING_TYPES;
        $result["plant_types"] = $this->_PLANT_TYPES;
        $result["pot_types"] = $this->_POT_TYPES;


        $result["plant_goal_cards"] = $this->_PLANT_GOAL_CARDS;
        $result["item_goal_cards"] = $this->_ITEM_GOAL_CARDS;
        $result["room_goal_cards"] = $this->_ROOM_GOAL_CARDS;

        $result["plant_cards"] = $this->_PLANT_CARDS;
        $result["room_cards"] = $this->_ROOM_CARDS;

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        return $result;
    }



    /////////////////////////////////////////////////////////////////////////////////  
    //     _____                      _____                                   _             
    //    / ____|                    |  __ \                                 (_)            
    //   | |  __  __ _ _ __ ___   ___| |__) | __ ___   __ _ _ __ ___  ___ ___ _  ___  _ __  
    //   | | |_ |/ _` | '_ ` _ \ / _ \  ___/ '__/ _ \ / _` | '__/ _ \/ __/ __| |/ _ \| '_ \ 
    //   | |__| | (_| | | | | | |  __/ |   | | | (_) | (_| | | |  __/\__ \__ \ | (_) | | | |
    //    \_____|\__,_|_| |_| |_|\___|_|   |_|  \___/ \__, |_|  \___||___/___/_|\___/|_| |_|
    //                                                 __/ |                                
    //                                                |___/                                 
    /////////////////////////////////////////////////////////////////////////////////  

    public function getGameProgression()
    {
        $players = self::getObjectListFromDB("SELECT player_id FROM player", true);
        $nb_player = count($players);
        $nb_cards = 0;


        foreach ($players as $player) {
            $nb_cards += count(self::getObjectListFromDB("SELECT card_id id FROM plant WHERE card_location = '{$player}'", true));
            $nb_cards += count(self::getObjectListFromDB("SELECT card_id id FROM room WHERE card_location = '{$player}'", true));
        }


        return floor(($nb_cards * 100) / ($nb_player * 15));
    }


    /////////////////////////////////////////////////////////////////////////////////  
    //     _    _ _   _ _ _ _            __                  _   _                 
    //    | |  | | | (_) (_) |          / _|                | | (_)                
    //    | |  | | |_ _| |_| |_ _   _  | |_ _   _ _ __   ___| |_ _  ___  _ __  ___ 
    //    | |  | | __| | | | __| | | | |  _| | | | '_ \ / __| __| |/ _ \| '_ \/ __|
    //    | |__| | |_| | | | |_| |_| | | | | |_| | | | | (__| |_| | (_) | | | \__ \
    //     \____/ \__|_|_|_|\__|\__, | |_|  \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
    //                           __/ |                                             
    //                          |___/                                              
    /////////////////////////////////////////////////////////////////////////////////  

    function addPending($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL)
    {
        $sql = "INSERT INTO pending (player_id, function, arg, arg2, arg3, arg4) VALUES (" . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "', '" . $arg3 . "', '" . $arg4 . "')";
        self::DbQuery($sql);
    }

    function addPendingFirst($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL)
    {
        $minid = self::getUniqueValueFromDB("select min(id) from pending") - 1;
        $sql = "INSERT INTO pending (id, player_id, function, arg, arg2) VALUES (" . $minid . "," . $player_id . ", '" . $function . "', '" . $arg . "', '" . $arg2 . "')";
        self::DbQuery($sql);
    }

    function checkArgs($arg1)
    {
        $ret = self::argPlayerTurn();

        if (!in_array($arg1, $ret['selectable']) && !in_array($arg1, $ret['buttons'])) {
            throw new BgaUserException("Not a valid selection");
        }
    }


    function PossiblePosition($player_id, $card)
    {
        $result = array();

        $explode = explode('_', $card);

        $all_cards_plant = self::getObjectListFromDB("SELECT card_location_arg FROM plant WHERE card_location = '{$player_id}'", true);
        $all_cards_room = self::getObjectListFromDB("SELECT card_location_arg FROM room WHERE card_location = '{$player_id}'", true);

        $all_cards = array_merge($all_cards_plant, $all_cards_room);
        $all_dizaine = array();
        $all_unite = array();

        foreach ($all_cards as $nombre) {
            $all_unite[] = intval($nombre) % 10;   // Extraction des unités
            $all_dizaine[] = intdiv(intval($nombre), 10); // Extraction des dizaines
        }


        $valeurs_uniques_unite = array_unique($all_unite);
        $largeur_maison = count($valeurs_uniques_unite);
        $valeurs_uniques_dizaine = array_unique($all_dizaine);
        $hauteur_maison = count($valeurs_uniques_dizaine);



        if ($explode[0] == 'plant') {
            foreach ($all_cards_room as $room) {



                if ($hauteur_maison < 3) {
                    $tests = [$room - 10, $room + 10];

                    foreach ($tests as $test) {

                        if ((!in_array($test, $all_cards_plant)) && (!in_array('grid_' . $test . '_' . $player_id, $result))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }

                if ($largeur_maison < 5) {
                    $tests = [$room - 1, $room + 1];

                    foreach ($tests as $test) {

                        if ((!in_array($test, $all_cards_plant)) && (!in_array('grid_' . $test . '_' . $player_id, $result))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }

                if ($hauteur_maison == 3) {
                    $tests = [$room - 10, $room + 10];

                    foreach ($tests as $test) {

                        $dizaine = intdiv(intval($test), 10);

                        if ((!in_array($test, $all_cards_plant)) && (!in_array('grid_' . $test . '_' . $player_id, $result)) && (in_array($dizaine, $valeurs_uniques_dizaine))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }

                if ($largeur_maison == 5) {
                    $tests = [$room - 1, $room + 1];

                    foreach ($tests as $test) {

                        $unite = intval($test) % 10;

                        if ((!in_array($test, $all_cards_plant)) && (!in_array('grid_' . $test . '_' . $player_id, $result)) && (in_array($unite, $valeurs_uniques_unite))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }
            }
        }

        if ($explode[0] == 'room') {
            foreach ($all_cards_plant as $plant) {



                if ($hauteur_maison < 3) {
                    $tests = [$plant - 10, $plant + 10];

                    foreach ($tests as $test) {

                        if ((!in_array($test, $all_cards_room)) && (!in_array('grid_' . $test . '_' . $player_id, $result))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }

                if ($largeur_maison < 5) {
                    $tests = [$plant - 1, $plant + 1];

                    foreach ($tests as $test) {

                        if ((!in_array($test, $all_cards_room)) && (!in_array('grid_' . $test . '_' . $player_id, $result))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }

                if ($hauteur_maison == 3) {
                    $tests = [$plant - 10, $plant + 10];

                    foreach ($tests as $test) {

                        $dizaine = intdiv(intval($test), 10);

                        if ((!in_array($test, $all_cards_room)) && (!in_array('grid_' . $test . '_' . $player_id, $result)) && (in_array($dizaine, $valeurs_uniques_dizaine))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }

                if ($largeur_maison == 5) {
                    $tests = [$plant - 1, $plant + 1];

                    foreach ($tests as $test) {

                        $unite = intval($test) % 10;

                        if ((!in_array($test, $all_cards_room)) && (!in_array('grid_' . $test . '_' . $player_id, $result)) && (in_array($unite, $valeurs_uniques_unite))) {
                            $result[] = 'grid_' . $test . '_' . $player_id;
                        }
                    }
                }
            }
        }




        return $result;
    }


    function TestVerdoyance($player_id, $genre, $card_type, $position)
    {
        $player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$player_id}");

        if ($genre == 'plant') {
            $gain = 0;
            $before_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$card_type}'");

            $conditions_plant = $this->_PLANT_CARDS[$card_type]['lightning'];
            $tests = [$position - 1, $position + 1, $position - 10, $position + 10];
            foreach ($tests as $test) {
                $type_room = self::getUniqueValueFromDB("SELECT card_type FROM room WHERE card_location='{$player_id}' AND card_location_arg = '{$test}'");
                if ($type_room != null) {


                    $conditions_room = $this->_ROOM_CARDS[$type_room]['lightning'];


                    if (($test == $position - 1) && (in_array($conditions_room["east"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                    if (($test == $position + 1) && (in_array($conditions_room["west"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                    if (($test == $position + 10) && (in_array($conditions_room["north"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                    if (($test == $position - 10) && (in_array($conditions_room["south"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                }
            }

            if ($gain >= 1) {
                $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$card_type}'");
                $max_verdoiement = $this->_PLANT_CARDS[$card_type]['verdancy'];

                if ($total_verdoiement < $max_verdoiement) {
                    $valeur_pot = -1;
                    $delta = $gain;
                } else {

                    $delta = $max_verdoiement - $before_verdoiement;

                    $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'deck' ORDER BY card_type DESC LIMIT 1");
                    $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE pot SET card_location = $player_id, card_location_arg = $card_type WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$card_type}'");
                }

                game::$instance->notifyAllPlayers(
                    'addVerdancy',
                    '',
                    array(
                        'player_name' => $player_name,
                        'player_id' => $player_id,
                        'plant_type' => $card_type,
                        'verdancy_added' => $delta,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => false,


                    )
                );
            }
        }

        if ($genre == 'room') {

            $conditions_room = $this->_ROOM_CARDS[$card_type]['lightning'];


            $tests = [$position - 1, $position + 1, $position - 10, $position + 10];
            foreach ($tests as $test) {
                $type_plant = self::getUniqueValueFromDB("SELECT card_type FROM plant WHERE card_location='{$player_id}' AND card_location_arg = '{$test}'");
                $test_pot = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location='{$player_id}' AND card_location_arg = '{$type_plant}'");

                if (($type_plant != null) && ($test_pot == null)) {

                    $conditions_plant = $this->_PLANT_CARDS[$type_plant]['lightning'];
                    $gain = 0;


                    if (($test == $position - 1) && (in_array($conditions_room["west"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }
                    if (($test == $position + 1) && (in_array($conditions_room["east"], $conditions_plant))) {

                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }
                    if (($test == $position + 10) && (in_array($conditions_room["south"], $conditions_plant))) {

                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }
                    if (($test == $position - 10) && (in_array($conditions_room["north"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }

                    if ($gain == 1) {
                        $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$type_plant}'");
                        $max_verdoiement = $this->_PLANT_CARDS[$type_plant]['verdancy'];

                        if ($total_verdoiement < $max_verdoiement) {
                            $valeur_pot = -1;
                        } else {
                            $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'deck' ORDER BY card_type DESC LIMIT 1");
                            $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                            self::DbQuery("UPDATE pot SET card_location = $player_id, card_location_arg = $type_plant WHERE card_id = '{$pot_id}'");

                            self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$type_plant}'");
                        }

                        game::$instance->notifyAllPlayers(
                            'addVerdancy',
                            '',
                            array(
                                'player_name' => $player_name,
                                'player_id' => $player_id,
                                'plant_type' => $type_plant,
                                'verdancy_added' => 1,
                                'pot_value' => $valeur_pot,
                                'thumbs_used' => false,

                            )
                        );
                    }
                }
            }
        }
    }

    function TestVerdoyanceSolo($player_id, $genre, $card_type, $position)
    {
        $player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$player_id}");

        if ($genre == 'plant') {
            $gain = 0;
            $before_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$card_type}'");

            $conditions_plant = $this->_PLANT_CARDS[$card_type]['lightning'];
            $tests = [$position - 1, $position + 1, $position - 10, $position + 10];
            foreach ($tests as $test) {
                $type_room = self::getUniqueValueFromDB("SELECT card_type FROM room WHERE card_location='{$player_id}' AND card_location_arg = '{$test}'");
                if ($type_room != null) {


                    $conditions_room = $this->_ROOM_CARDS[$type_room]['lightning'];


                    if (($test == $position - 1) && (in_array($conditions_room["east"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                    if (($test == $position + 1) && (in_array($conditions_room["west"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                    if (($test == $position + 10) && (in_array($conditions_room["north"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                    if (($test == $position - 10) && (in_array($conditions_room["south"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$card_type}'");
                        $gain++;
                    }
                }
            }

            if ($gain >= 1) {

                $nbre_pot_market = count(self::getObjectListFromDB("SELECT card_id FROM pot WHERE card_location = 'market'", true));
                $pot_origin = 0;

                $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$card_type}'");
                $max_verdoiement = $this->_PLANT_CARDS[$card_type]['verdancy'];

                if ($total_verdoiement < $max_verdoiement) {
                    $valeur_pot = -1;
                    $delta = $gain;
                } else {

                    $delta = $max_verdoiement - $before_verdoiement;

                    if ($nbre_pot_market == 4) {
                        $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'market' AND card_location_arg =4");
                        $pot_origin = 'market_pot_4';
                    } else {
                        $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'discard' ORDER BY card_type ASC LIMIT 1");
                        $pot_origin = 'pot_discard_' . $pot_id;
                    }

                    $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE pot SET card_location = $player_id, card_location_arg = $card_type WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$card_type}'");
                }

                game::$instance->notifyAllPlayers(
                    'addVerdancySolo',
                    '',
                    array(
                        'player_name' => $player_name,
                        'player_id' => $player_id,
                        'plant_type' => $card_type,
                        'verdancy_added' => $delta,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => false,
                        'pot_origin' => $pot_origin


                    )
                );
            }
        }

        if ($genre == 'room') {

            $conditions_room = $this->_ROOM_CARDS[$card_type]['lightning'];


            $tests = [$position - 1, $position + 1, $position - 10, $position + 10];
            foreach ($tests as $test) {
                $type_plant = self::getUniqueValueFromDB("SELECT card_type FROM plant WHERE card_location='{$player_id}' AND card_location_arg = '{$test}'");
                $test_pot = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location='{$player_id}' AND card_location_arg = '{$type_plant}'");

                if (($type_plant != null) && ($test_pot == null)) {

                    $conditions_plant = $this->_PLANT_CARDS[$type_plant]['lightning'];
                    $gain = 0;


                    if (($test == $position - 1) && (in_array($conditions_room["west"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }
                    if (($test == $position + 1) && (in_array($conditions_room["east"], $conditions_plant))) {

                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }
                    if (($test == $position + 10) && (in_array($conditions_room["south"], $conditions_plant))) {

                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }
                    if (($test == $position - 10) && (in_array($conditions_room["north"], $conditions_plant))) {
                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $gain = 1;
                    }

                    if ($gain == 1) {

                        $nbre_pot_market = count(self::getObjectListFromDB("SELECT card_id FROM pot WHERE card_location = 'market'", true));
                        $pot_origin = 0;

                        $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$type_plant}'");
                        $max_verdoiement = $this->_PLANT_CARDS[$type_plant]['verdancy'];

                        if ($total_verdoiement < $max_verdoiement) {
                            $valeur_pot = -1;
                        } else {

                            $delta = $max_verdoiement - $before_verdoiement;

                            if ($nbre_pot_market == 4) {
                                $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'market' AND card_location_arg =4");
                                $pot_origin = 'market_pot_4';
                            } else {
                                $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'discard' ORDER BY card_type ASC LIMIT 1");
                                $pot_origin = 'pot_discard_' . $pot_id;
                            }

                            $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                            self::DbQuery("UPDATE pot SET card_location = $player_id, card_location_arg = $type_plant WHERE card_id = '{$pot_id}'");

                            self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$type_plant}'");
                        }

                        game::$instance->notifyAllPlayers(
                            'addVerdancySolo',
                            '',
                            array(
                                'player_name' => $player_name,
                                'player_id' => $player_id,
                                'plant_type' => $type_plant,
                                'verdancy_added' => 1,
                                'pot_value' => $valeur_pot,
                                'thumbs_used' => false,
                                'pot_origin' => $pot_origin

                            )
                        );
                    }
                }
            }
        }
    }


    public function getFinalResults()
    {
        $final_scores = [];
        $players = self::getObjectListFromDB("SELECT player_id FROM player", true);

        // INIT DES SCORES//
        foreach ($players as $player) {
            $final_scores[$player]['completed_plants'] = 0;
            $final_scores[$player]['extra_verdancy'] = 0;
            $final_scores[$player]['pot_bonus'] = 0;
            $final_scores[$player]['room_bonus'] = 0;
            $final_scores[$player]['furniture_pets'] = 0;
            $final_scores[$player]['plant_collector_bonus'] = 0;
            $final_scores[$player]['room_collector_bonus'] = 0;
        }


        $plants = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM plant WHERE card_location NOT IN ('deck', 'market', 'discard')");
        $rooms = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM room WHERE card_location NOT IN ('deck', 'market', 'discard')");
        $tiles = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_location NOT IN ('deck', 'discard', 'market')");
        $pots = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM pot WHERE card_location NOT IN ('deck', 'discard', 'market')");

        // completed plants AND // extra verdancy on incomplete plants ( halved down)

        foreach ($plants as $plant) {
            $player_id = $plant['location'];
            $verdancy = $plant['type_arg'];

            $plant_infos = $this->_PLANT_CARDS[$plant['type']];

            $max_verdancy = $plant_infos['verdancy'];

            if ($max_verdancy == $verdancy) {
                $final_scores[$player_id]['completed_plants'] += $plant_infos['points'];
            } else {
                $final_scores[$player_id]['extra_verdancy'] += $verdancy;
            }
        }

        foreach ($players as $player) {

            $final_scores[$player]['extra_verdancy'] = floor($final_scores[$player]['extra_verdancy'] / 2);
        }




        // bonus pot tokens

        foreach ($pots as $pot) {
            $player_id = $pot['location'];


            $final_scores[$player_id]['pot_bonus'] += $pot['type'];
        }

        // room bonus (same color adjacent + x2 if same colored item)

        foreach ($rooms as $room) {
            $player_id = $room['location'];
            $type = $room['type'];
            $room_infos = $this->_ROOM_CARDS[$room['type']];
            $position = intval($room['location_arg']);

            $score_room = 0;    // pour objectif room 13
            $scores_rooms = array(); // pour objectif room 13

            $double = false;

            $type_tile = intval(self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='{$player_id}' AND card_location_arg = '{$type}'"));
            if ($type_tile != null) {
                $dizaine = intdiv($type_tile, 10);
                if ($dizaine == $room_infos['type']) {
                    $double = true;
                }
            }

            $tests = [$position + 1, $position - 1, $position + 10, $position - 10];

            foreach ($tests as $test) {
                $plant_type = self::getUniqueValueFromDB("SELECT card_type FROM plant WHERE card_location='{$player_id}' AND card_location_arg = '{$test}'");
                if ($plant_type != null) {
                    if ($this->_PLANT_CARDS[$plant_type]['type'] == $room_infos['type']) {
                        if ($double == false) {
                            $final_scores[$player_id]['room_bonus'] += 1;
                            $score_room += 1; // pour objectif room 13
                        }

                        if ($double == true) {
                            $final_scores[$player_id]['room_bonus'] += 2;
                            $score_room += 2; // pour objectif room 13
                        }
                    }
                }
            }


            $scores_rooms[$player_id][] = $score_room; // pour objectif room 13

        }

        // pour objectif room 13
        if (($this->getGameStateValue('game_mode') == 2) && ($this->getGameStateValue('room_goal') == 13)) {
            foreach ($players as $player) {
                $max = max($scores_rooms[$player]);
                $somme = 0;

                foreach ($scores_rooms[$player] as $valeur) {
                    $somme += $valeur;
                }

                $final_scores[$player]['room_bonus'] = $somme + $max;
            }
        }






        // furniture and pets 1-3-6-9-12-16-20-25

        foreach ($players as $player) {

            $list_token = self::getObjectListFromDB("SELECT card_type FROM tile WHERE card_location='{$player}' AND card_location_arg != 99", true);
            $unitesArray = array_map(fn($n) => $n % 10, $list_token); // nomveau tableau avec que les untités (type)

            $unitesUniques = array_unique($unitesArray);
            $nombreUnitesDifferentes = count($unitesUniques);


            if ($nombreUnitesDifferentes == 1) {
                $final_scores[$player]['furniture_pets'] = 1;
            }
            if ($nombreUnitesDifferentes == 2) {
                $final_scores[$player]['furniture_pets'] = 3;
            }
            if ($nombreUnitesDifferentes == 3) {
                $final_scores[$player]['furniture_pets'] = 6;
            }
            if ($nombreUnitesDifferentes == 4) {
                $final_scores[$player]['furniture_pets'] = 9;
            }
            if ($nombreUnitesDifferentes == 5) {
                $final_scores[$player]['furniture_pets'] = 12;
            }
            if ($nombreUnitesDifferentes == 6) {
                $final_scores[$player]['furniture_pets'] = 16;
            }
            if ($nombreUnitesDifferentes == 7) {
                $final_scores[$player]['furniture_pets'] = 20;
            }
            if ($nombreUnitesDifferentes == 8) {
                $final_scores[$player]['furniture_pets'] = 25;
            }
        }

        // plant collector bonus (3 per 5-color set on plants)

        foreach ($players as $player) {

            $type_plants = array();
            $list_plants = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location='{$player}'", true);
            foreach ($list_plants as $plant) {
                $type_plant[] = $this->_PLANT_CARDS[$plant]['type'];
            }

            $TypeUnique = array_unique($type_plants);
            $nombreTypeUnique = count($TypeUnique);

            if ($nombreTypeUnique == 5) {
                $final_scores[$player]['plant_collector_bonus'] = 3;
            }
        }


        // decorator bonus ( 3 per 5-color set on rooms)

        foreach ($players as $player) {

            $type_rooms = array();
            $list_rooms = self::getObjectListFromDB("SELECT card_type FROM room WHERE card_location='{$player}'", true);
            foreach ($list_rooms as $room) {
                $type_rooms[] = $this->_ROOM_CARDS[$room]['type'];
            }

            $TypeUnique = array_unique($type_rooms);
            $nombreTypeUnique = count($TypeUnique);

            if ($nombreTypeUnique == 5) {
                $final_scores[$player]['room_collector_bonus'] = 3;
            }
        }



        ///// SET STATS///
        foreach ($players as $player) {

            game::$instance->setStat($final_scores[$player]['completed_plants'], 'completed_plants', $player);
            game::$instance->setStat($final_scores[$player]['extra_verdancy'], 'extra_verdancy', $player);
            game::$instance->setStat($final_scores[$player]['pot_bonus'], 'pot_bonus', $player);
            game::$instance->setStat($final_scores[$player]['room_bonus'], 'room_bonus', $player);
            game::$instance->setStat($final_scores[$player]['furniture_pets'], 'furniture_pets_number', $player);
            game::$instance->setStat($final_scores[$player]['plant_collector_bonus'], 'plant_collector_bonus', $player);
            game::$instance->setStat($final_scores[$player]['room_collector_bonus'], 'room_collector_bonus', $player);
        }

        /// MODE AVANCE ////

        if ($this->getGameStateValue('game_mode') == 2) {
            // plant goal
            $result_plant_goal = $this->getPlantGoalBonus($this->getGameStateValue('plant_goal'));

            // item goal
            $result_item_goal = $this->getItemGoalBonus($this->getGameStateValue('item_goal'));

            // room goal
            $result_room_goal = $this->getRoomGoalBonus($this->getGameStateValue('room_goal'));

            foreach ($players as $player) {
                $final_scores[$player]['plant_goal'] = $result_plant_goal[$player];
                $final_scores[$player]['item_goal'] = $result_item_goal[$player];
                $final_scores[$player]['room_goal'] = $result_room_goal[$player];

                game::$instance->setStat($final_scores[$player]['plant_goal'], 'plant_goal', $player);
                game::$instance->setStat($final_scores[$player]['item_goal'], 'item_goal', $player);
                game::$instance->setStat($final_scores[$player]['room_goal'], 'room_goal', $player);
            }
        }


        game::$instance->notifyAllPlayers(
            'showFinalScores',
            '',
            array(
                'final_scores' => $final_scores,

            )
        );
    }

    public function getPlantGoalBonus($type)
    {
        $player_ids =  array_keys($this->loadPlayersBasicInfos());
        foreach ($player_ids as $player_id) {
            $plant_goal_bonus[$player_id] = 0;
        }

        if ($type == 1) // Apartment Living
        {
            $pots = self::getPlayersPots();

            foreach ($pots as $pot) {
                if ($this->_PLANT_CARDS[$pot['plant_type']]['verdancy'] <= 4) {
                    $plant_goal_bonus[$pot['player_id']] += 2;
                }
            }
        } else if ($type == 2) // Going Big
        {
            $pots = self::getPlayersPots();

            foreach ($pots as $pot) {
                if ($this->_PLANT_CARDS[$pot['plant_type']]['verdancy'] >= 7) {
                    $plant_goal_bonus[$pot['player_id']] += 2;
                }
            }
        } else if ($type == 3) // On Vacation
        {
            $players_match = self::getCollectionFromDB("
                SELECT card_location AS player_id, COUNT(*) AS pot_count 
                FROM plant WHERE card_location NOT IN ('deck', 'market') and card_type_arg <= 2
                GROUP BY card_location
            ", true);
            foreach ($player_ids as $player_id) {
                if (isset($players_match[$player_id])) {
                    $plant_goal_bonus[$player_id] += 2 * $players_match[$player_id];
                }
            }
        } else if ($type == 4) // Coordination Alignment
        {
            $plants = self::getPlayersPlants();

            $rows = []; // Stocke les types réels de plantes par joueur et par ligne

            foreach ($plants as $plant) {
                $player_id = $plant['player_id'];
                $row = floor($plant['coord'] / 10); // On regroupe par ligne

                // Récupération du vrai type de plante depuis $this->_PLANT_CARDS
                $plant_color = $this->_PLANT_CARDS[$plant['type']]['type'];

                // Ajoute la plante à la ligne correspondante du joueur
                $rows[$player_id][$row][] = $plant_color;
            }

            // Vérification des lignes homogènes
            foreach ($rows as $player_id => $player_rows) {
                foreach ($player_rows as $row) {
                    if (count(array_unique($row)) === 1) { // Toutes les plantes ont la même couleur
                        $plant_goal_bonus[$player_id] += 3;
                    }
                }
            }
        } else if ($type == 5) // Picking Favorites
        {
            $plants = self::getPlayersPlants();

            // Initialiser les compteurs
            foreach ($player_ids as $player_id) {
                $owned_types[$player_id] = [];
            }

            // Remplir les types possédés par chaque joueur
            foreach ($plants as $plant) {
                $player_id = $plant['player_id'];
                $plant_color = $this->_PLANT_CARDS[$plant['card_type']]['type'];
                $owned_types[$player_id][$plant_color] = true;
            }

            // Calculer les types manquants et attribuer les points
            foreach ($player_ids as $player_id) {
                $missing_count = 5 - count($owned_types[$player_id]);
                $plant_goal_bonus[$player_id] += $missing_count * 2;
            }
        } else if ($type == 6) // Mixing it up
        {
            $plants = self::getPlayersPlants();

            // 2. Initialiser un tableau pour stocker les plantes par ligne
            $rows = [];

            // 3. Organiser les plantes par ligne
            foreach ($plants as $plant) {
                $player_id = $plant['player_id'];
                $row = floor($plant['coord'] / 10); // Déterminer la ligne à partir des coordonnées
                $plant_color = $this->_PLANT_CARDS[$plant['card_type']]['type']; // Obtenir le type réel de la plante

                // Si la ligne n'existe pas encore, l'initialiser
                if (!isset($rows[$player_id][$row])) {
                    $rows[$player_id][$row] = [];
                }

                // Ajouter le type de plante à la ligne correspondante
                $rows[$player_id][$row][] = $plant_color;
            }

            // 4. Vérifier les lignes pour des types uniques et attribuer des points
            foreach ($player_ids as $player_id) {
                foreach ($rows[$player_id] as $row => $types) {
                    // Vérifier si les types dans la ligne sont uniques
                    if (count($types) === count(array_unique($types))) {
                        // Si tous les types sont uniques, ajouter 2 points pour cette ligne
                        $plant_goal_bonus[$player_id] += 2;
                    }
                }
            }
        } else if ($type == 7) // Perfect Conditions
        {
            $plants = self::getPlayersPlants();

            // Remplir les types possédés par chaque joueur
            foreach ($plants as $plant) {
                $gain = true;  // Initialiser à true, et le rendre false si une condition échoue

                $card_type = $plant['card_type'];  // Définir le type de plante
                $conditions_plant = $this->_PLANT_CARDS[$card_type]['lightning'];  // Conditions de lightning de la plante
                $position = $plant['coord'];  // Position de la plante

                // Définir les positions des rooms adjacentes (gauche, droite, haut, bas)
                $tests = [$position - 1, $position + 1, $position - 10, $position + 10];

                // Vérifier chaque room adjacente
                foreach ($tests as $test) {
                    $type_room = self::getUniqueValueFromDB("SELECT card_type FROM room WHERE card_location='{$plant['player_id']}' AND card_location_arg = '{$test}'");

                    // Si la room existe (pas nulle)
                    if ($type_room != null) {
                        // Récupérer les conditions de lightning de la room
                        $conditions_room = $this->_ROOM_CARDS[$type_room]['lightning'];

                        // Vérifier si la room adjacente a un lightning favorable pour la plante
                        // Si l'une des rooms ne correspond pas aux conditions, on annule le gain
                        if ($test == $position - 1 && !in_array($conditions_room["east"], $conditions_plant)) {
                            $gain = false;
                            break;
                        }
                        if ($test == $position + 1 && !in_array($conditions_room["west"], $conditions_plant)) {
                            $gain = false;
                            break;
                        }
                        if ($test == $position + 10 && !in_array($conditions_room["north"], $conditions_plant)) {
                            $gain = false;
                            break;
                        }
                        if ($test == $position - 10 && !in_array($conditions_room["south"], $conditions_plant)) {
                            $gain = false;
                            break;
                        }
                    }
                }

                // Si toutes les conditions ont été satisfaites, on attribue le bonus
                if ($gain) {
                    $plant_goal_bonus[$plant['player_id']] += 1;
                }
            }
        } else if ($type == 8) // Competitive Collections
        {
            // 1. Récupérer les plantes et leur emplacement
            $plants = self::getPlayersPlants();

            // 2. Initialiser un tableau pour compter les plantes de chaque type par joueur
            $player_plants_count = [];

            // 3. Compter le nombre de plantes de chaque type pour chaque joueur
            foreach ($plants as $plant) {
                $player_id = $plant['player_id'];
                $plant_color = $this->_PLANT_CARDS[$plant['card_type']]['type']; // Obtenir le type réel de la plante

                // Si le joueur n'existe pas encore dans le tableau, l'initialiser
                if (!isset($player_plants_count[$player_id])) {
                    $player_plants_count[$player_id] = [];
                }

                // Ajouter au compteur de plantes de ce type
                if (!isset($player_plants_count[$player_id][$plant_color])) {
                    $player_plants_count[$player_id][$plant_color] = 0;
                }
                $player_plants_count[$player_id][$plant_color]++;
            }

            // 4. Calculer les points pour chaque type de plante
            foreach ($player_plants_count as $player_id => $types) {
                foreach ($types as $type => $count) {
                    // Trouver le maximum de plantes d'un type parmi tous les joueurs
                    $max_plants = 0;
                    foreach ($player_plants_count as $other_player_id => $other_types) {
                        if (isset($other_types[$type])) {
                            $max_plants = max($max_plants, $other_types[$type]);
                        }
                    }

                    // Ajouter 2 points pour le joueur(s) ayant le plus de plantes de ce type
                    if ($count === $max_plants) {
                        $plant_goal_bonus[$player_id] += 2;
                    }
                }
            }
        } else if ($type == 9) // Every Shade of Green
        {
            // 1. Récupérer les plantes et leur emplacement avec verdancy
            $plants = self::getPlayersPlants();

            // 2. Initialiser un tableau pour stocker les verdancy uniques de chaque joueur
            $player_verdancy = [];

            // 3. Remplir les verdancy uniques pour chaque joueur
            foreach ($plants as $plant) {
                $player_id = $plant['player_id'];
                $verdancy = $this->_PLANT_CARDS[$plant['card_type']]['verdancy']; // Obtenir la verdancy de la plante

                // Si le joueur n'existe pas encore dans le tableau, l'initialiser
                if (!isset($player_verdancy[$player_id])) {
                    $player_verdancy[$player_id] = [];
                }

                // Ajouter le verdancy à la liste des verdancies uniques pour ce joueur
                $player_verdancy[$player_id][$verdancy] = true;
            }

            // 4. Calculer les points pour chaque joueur en fonction des verdancy distincts
            foreach ($player_verdancy as $player_id => $verdancies) {
                // Le nombre de verdancy distincts pour ce joueur
                $distinct_verdancies = count($verdancies);

                // Ajouter 1 point pour chaque verdancy distinct
                $plant_goal_bonus[$player_id] += $distinct_verdancies;
            }
        } else if ($type == 10) // Against all Odds
        {
            $plants = self::getPlayersPlants();

            // Remplir les types possédés par chaque joueur
            foreach ($plants as $plant) {
                $gain = true;  // Initialiser à true, et le rendre false si une condition échoue
                $has_favorable_lightning = false;  // Variable pour vérifier si au moins une room satisfait les conditions

                $card_type = $plant['card_type'];  // Définir le type de plante
                $conditions_plant = $this->_PLANT_CARDS[$card_type]['lightning'];  // Conditions de lightning de la plante
                $position = $plant['coord'];  // Position de la plante

                // Définir les positions des rooms adjacentes (gauche, droite, haut, bas)
                $tests = [$position - 1, $position + 1, $position - 10, $position + 10];

                // Vérifier chaque room adjacente
                foreach ($tests as $test) {
                    $type_room = self::getUniqueValueFromDB("SELECT card_type FROM room WHERE card_location='{$plant['player_id']}' AND card_location_arg = '{$test}'");

                    // Si la room existe (pas nulle)
                    if ($type_room != null) {
                        // Récupérer les conditions de lightning de la room
                        $conditions_room = $this->_ROOM_CARDS[$type_room]['lightning'];

                        // Vérifier si la room adjacente a un lightning favorable pour la plante
                        if ($test == $position - 1 && in_array($conditions_room["east"], $conditions_plant)) {
                            $has_favorable_lightning = true;
                            break;  // Quitter la boucle dès qu'un lightning favorable est trouvé
                        }
                        if ($test == $position + 1 && in_array($conditions_room["west"], $conditions_plant)) {
                            $has_favorable_lightning = true;
                            break;  // Quitter la boucle dès qu'un lightning favorable est trouvé
                        }
                        if ($test == $position + 10 && in_array($conditions_room["north"], $conditions_plant)) {
                            $has_favorable_lightning = true;
                            break;  // Quitter la boucle dès qu'un lightning favorable est trouvé
                        }
                        if ($test == $position - 10 && in_array($conditions_room["south"], $conditions_plant)) {
                            $has_favorable_lightning = true;
                            break;  // Quitter la boucle dès qu'un lightning favorable est trouvé
                        }
                    }
                }

                // Si aucune room adjacente n'a un lightning favorable, on attribue 2 points
                if (!$has_favorable_lightning) {
                    $plant_goal_bonus[$plant['player_id']] += 2;
                }
            }
        } else if ($type == 11) // Loved lines
        {
            $plants = self::getPlayersPlants();

            $pots = self::getPlayersPots();

            // Organiser les pots par player_id et plant_type
            $pots_by_player = [];
            foreach ($pots as $pot) {
                $pots_by_player[$pot['player_id']][$pot['plant_type']] = true;
            }


            // Organiser les plantes par ligne et par player_id
            $plants_by_line = [];
            foreach ($plants as $plant) {
                $row = floor($plant['coord'] / 10); // Calculer la ligne
                $plants_by_line[$row][$plant['player_id']][] = $plant['card_type']; // Ajouter la plante à la ligne et au joueur
            }

            // Vérifier les lignes complètes
            foreach ($plants_by_line as $line => $players_plants) {
                foreach ($players_plants as $player_id => $plant_types) {
                    // Vérifier si toutes les plantes de cette ligne ont un pot associé
                    $all_plants_completed = true;
                    foreach ($plant_types as $type) {
                        if (!isset($pots_by_player[$player_id][$type])) {
                            $all_plants_completed = false;
                            break;
                        }
                    }
                    // Si toutes les plantes sont complètes, ajouter 2 points à ce joueur
                    if ($all_plants_completed) {
                        $plant_goal_bonus[$player_id] += 2;
                    }
                }
            }
        } else if ($type == 12) // Narrow necessities
        {
            $plants = self::getPlayersPlants();

            // Remplir les types possédés par chaque joueur
            foreach ($plants as $plant) {
                // Vérifier si la plante est complétée (c'est-à-dire qu'elle a un pot)
                $card_type = $plant['card_type'];
                $conditions_plant = $this->_PLANT_CARDS[$card_type]['lightning'];  // Récupérer les conditions de lightning de la plante

                // Vérifier si la plante n'a qu'un seul lightning préféré
                if (count($conditions_plant) == 1) {
                    // Si la plante a un seul lightning préféré et est complétée, on attribue 1 point
                    $plant_goal_bonus[$plant['player_id']] += 1;
                }
            }
        } else if ($type == 13) // One true Love
        {
            $plants = self::getPlayersPlants();

            // Initialiser un tableau pour compter les plantes par joueur et par type réel
            $player_plants_count = [];

            // Organiser les plantes par joueur et type réel
            foreach ($plants as $plant) {
                $player_id = $plant['player_id'];
                $plant_color = $this->_PLANT_CARDS[$plant['card_type']]['type'];  // Récupérer le type réel à partir de _PLANT_CARDS

                if (!isset($player_plants_count[$player_id][$plant_color])) {
                    $player_plants_count[$player_id][$plant_color] = 0;
                }

                // Incrémenter le compteur pour ce type de plante et ce joueur
                $player_plants_count[$player_id][$plant_color]++;
            }

            // Initialiser un tableau pour les points des joueurs
            $plant_goal_bonus = [];

            // Calculer les points pour chaque joueur
            foreach ($player_plants_count as $player_id => $types) {
                // Ajouter des points en fonction du nombre de plantes de ce type
                $plant_goal_bonus[$player_id] = max($types);
            }
        }

        return $plant_goal_bonus;
    }

    public function getItemGoalBonus($type)
    {
        $player_ids =  array_keys($this->loadPlayersBasicInfos());
        foreach ($player_ids as $player_id) {
            $item_goal_bonus[$player_id] = 0;
        }

        if ($type == 1) // Pot Pairs 
        {
            $pots = self::getPlayersPots();

            // Initialiser un tableau pour stocker les types de pots pour chaque joueur
            $pots_by_player = [];

            // Remplir les pots pour chaque joueur
            foreach ($pots as $pot) {
                $player_id = $pot['player_id'];
                $pot_type = $pot['card_type'];

                // Ajouter le type de pot à la liste du joueur
                $pots_by_player[$player_id][] = $pot_type;
            }

            // Calculer les paires de pots identiques et attribuer les points
            foreach ($pots_by_player as $player_id => $pots) {
                // Compter combien de fois chaque type de pot apparaît
                $pot_counts = array_count_values($pots);

                // Calculer le nombre de paires pour chaque type de pot
                foreach ($pot_counts as $pot_type => $count) {
                    if ($count > 1) {
                        // Nombre de paires de pots identiques
                        $pairs = floor($count / 2);
                        $item_goal_bonus[$player_id] += $pairs * 2;  // Ajouter 2 points pour chaque paire
                    }
                }
            }
        }
        if ($type == 2) // Thumbs Up 
        {
            // Récupérer le nombre de pouces restants pour chaque joueur
            $thumbs = self::getObjectListFromDB("
                SELECT player_id, player_thumb 
                FROM player
            ");

            // Attribuer des points en fonction des pouces restants
            foreach ($thumbs as $thumb) {
                $player_id = $thumb['player_id'];

                // Ajouter 1 point pour chaque pouce restant
                $item_goal_bonus[$player_id] += $thumb['player_thumb'];
            }
        }
        if ($type == 3) // Picky Potter 
        {
            // Récupérer les types de pots déjà présents pour chaque joueur
            $pots = self::getPlayersPots();

            // Initialiser un tableau pour stocker les types de pots présents pour chaque joueur
            $players_pot_types = [];
            foreach ($pots as $pot) {
                $players_pot_types[$pot['player_id']][] = $pot['card_type'];
            }

            // Vérifier pour chaque joueur les types de pots manquants et attribuer les points
            foreach ($player_ids as $player_id) {
                // Créer un tableau pour suivre les types de pots manquants (0 à 3)
                $missing_pots = [0, 1, 2, 3];

                // Retirer les types de pots déjà possédés par le joueur
                if (isset($players_pot_types[$player_id])) {
                    $owned_types = $players_pot_types[$player_id];
                    $missing_pots = array_diff($missing_pots, $owned_types); // Enlever les types présents
                }

                // Attribuer 2 points pour chaque type de pot manquant
                $item_goal_bonus[$player_id] += count($missing_pots) * 2;
            }
        }
        if ($type == 4) // Delayed Gratification
        {
            $pots = self::getPlayersPots();

            // Ajouter 2 points pour chaque pot de type 0
            foreach ($pots as $pot) {
                if ($pot['card_type'] == 0) {
                    $item_goal_bonus[$pot['player_id']] += 2;
                }
            }
        }
        if ($type == 5) // Color Pairs 
        {
            $items = self::getPlayersItems();

            // Initialiser un tableau pour compter les items de chaque couleur pour chaque joueur
            $player_color_counts = [];

            foreach ($items as $item) {
                // Extraire la couleur (dizaines de card_type)
                $color = floor($item['card_type'] / 10);  // La couleur est dans les dizaines de card_type

                // Compter les items de chaque couleur pour chaque joueur
                $player_color_counts[$item['player_id']][$color][] = $item['card_type'];
            }

            // Vérifier chaque joueur et attribuer les points en fonction des paires de couleurs
            foreach ($player_color_counts as $player_id => $colors) {
                foreach ($colors as $color => $items) {
                    // Calculer le nombre de paires pour cette couleur
                    $item_count = count($items);
                    $pair_count = floor($item_count / 2);  // Nombre de paires

                    // Attribuer 2 points pour chaque paire
                    if ($pair_count > 0) {
                        $item_goal_bonus[$player_id] += $pair_count * 2;  // 2 points par paire
                    }
                }
            }
        }
        if ($type == 6) // The Spice of Life 
        {
            // Récupérer les pots de chaque joueur avec leur type
            $pots = self::getPlayersPots();

            // Initialiser un tableau pour compter les pots de chaque type pour chaque joueur
            $player_pot_counts = [];

            foreach ($pots as $pot) {
                $player_pot_counts[$pot['player_id']][$pot['card_type']][] = $pot['card_type'];
            }

            // Vérifier chaque joueur et attribuer les points en fonction du nombre de pots
            foreach ($player_pot_counts as $player_id => $pots) {
                // Compter le nombre de pots de chaque type
                $min_pots = [];

                // On calcule le minimum pour chaque type de pot de 0 à 3
                for ($i = 0; $i <= 3; $i++) {
                    // Compter combien de pots de type $i le joueur possède
                    $min_pots[$i] = isset($pots[$i]) ? count($pots[$i]) : 0;
                }

                // Calculer le minimum parmi tous les types de pots
                $min_value = min($min_pots);

                // Si le joueur a au moins 1 pot de chaque type, on attribue les points
                if ($min_value > 0) {
                    $item_goal_bonus[$player_id] += $min_value * 4;  // 4 points par exemplaire de chaque pot
                }
            }
        }
        if ($type == 7) // Creature Comforts 
        {
            $items = self::getPlayersItems();

            // Initialiser un tableau pour compter le nombre d'animaux par joueur
            $player_animals_count = [];

            foreach ($items as $item) {
                // Vérifier si l'item est un animal (le chiffre des unités est entre 6 et 9)
                $animal_type = $item['card_type'] % 10;  // Le chiffre des unités représente l'animal
                if ($animal_type >= 6 && $animal_type <= 9) {
                    // Compter le nombre d'animaux pour chaque joueur
                    if (!isset($player_animals_count[$item['player_id']])) {
                        $player_animals_count[$item['player_id']] = 0;
                    }
                    $player_animals_count[$item['player_id']]++;
                }
            }

            // Trouver le joueur(s) ayant le plus d'animaux
            $max_animals = max($player_animals_count);

            // Attribuer 4 points aux joueurs ayant le maximum d'animaux
            foreach ($player_animals_count as $player_id => $animal_count) {
                if ($animal_count == $max_animals) {
                    $item_goal_bonus[$player_id] += 4;  // 4 points pour le joueur avec le plus d'animaux
                }
            }
        }
        if ($type == 8) // Furniture Aficionado 
        {
            // Récupérer les items de chaque joueur avec leur type (card_type) depuis la table 'tile'
            $items = self::getPlayersItems();

            // Initialiser un tableau pour compter le nombre de meubles par joueur
            $player_furnitures_count = [];

            foreach ($items as $item) {
                // Vérifier si l'item est un meuble (le chiffre des unités est entre 1 et 5)
                $furniture_type = $item['card_type'] % 10;  // Le chiffre des unités représente le meuble
                if ($furniture_type >= 1 && $furniture_type <= 5) {
                    // Compter le nombre de meubles pour chaque joueur
                    if (!isset($player_furnitures_count[$item['player_id']])) {
                        $player_furnitures_count[$item['player_id']] = 0;
                    }
                    $player_furnitures_count[$item['player_id']]++;
                }
            }

            // Trouver le joueur(s) ayant le plus de meubles
            $max_furnitures = max($player_furnitures_count);

            // Attribuer 4 points aux joueurs ayant le maximum de meubles
            foreach ($player_furnitures_count as $player_id => $furniture_count) {
                if ($furniture_count == $max_furnitures) {
                    $item_goal_bonus[$player_id] += 4;  // 4 points pour le joueur avec le plus de meubles
                }
            }
        }
        if ($type == 9) // Backup Plan 
        {
            // Récupérer les items de chaque joueur avec leur type (card_type) et location_arg depuis la table 'tile'
            $items = self::getObjectListFromDB("
                SELECT card_location AS player_id, card_type, card_location_arg 
                FROM tile
                WHERE card_location NOT IN ('deck', 'market', 'discard') AND card_location_arg = 99
            ");

            // Vérifier si un joueur possède un item de couleur 6 avec location_arg égal à 99
            foreach ($items as $item) {
                // Vérifier si l'item est de couleur 6 (les dizaines du card_type sont 6) et si le location_arg est 99
                if (floor($item['card_type'] / 10) == 6) {
                    // Ajouter 4 points au joueur
                    $item_goal_bonus[$item['player_id']] += 4;
                }
            }
        }
        if ($type == 10) // Clear the Way 
        {
            $rooms = self::getPlayersRooms();

            $items = self::getPlayersItems();

            // 2. Organiser les items par room
            $items_by_room = [];

            // Remplir le tableau avec les items par position de room
            foreach ($items as $item) {
                $items_by_room[$item['room_type']] = $item; // Un seul item par room, donc on écrase l'élément si nécessaire
            }

            // 3. Organiser les rooms par ligne
            $rooms_by_line = [];

            // Remplir le tableau avec les rooms par ligne et par joueur
            foreach ($rooms as $room) {
                $row = floor($room['coord'] / 10); // Calculer la ligne
                $rooms_by_line[$row][$room['player_id']][] = $room['coord'];
            }

            // 4. Vérifier chaque ligne pour voir si toutes les rooms sont vides
            foreach ($rooms_by_line as $row => $player_rooms) {
                foreach ($player_rooms as $player_id => $room_coords) {
                    $is_empty_line = true; // Supposer que la ligne est vide

                    // Vérifier si une des rooms de la ligne contient un item
                    foreach ($room_coords as $coord) {
                        if (isset($items_by_room[$coord])) {
                            $is_empty_line = false; // La ligne n'est pas vide, car un item a été trouvé
                            break;
                        }
                    }

                    // Si toutes les rooms de la ligne sont vides, ajouter 1 point
                    if ($is_empty_line) {
                        $item_goal_bonus[$player_id] += 1;
                    }
                }
            }
        }
        if ($type == 11) // Three of a kind 
        {
            // Récupérer tous les items des joueurs
            $items = self::getPlayersItems();

            // Initialiser un tableau pour compter les items par type pour chaque joueur
            $item_counts = [];

            // Compter le nombre d’items de chaque type pour chaque joueur
            foreach ($items as $item) {
                $player_id = $item['player_id'];
                $type = floor($item['card_type'] / 10);

                if (!isset($item_counts[$player_id][$type])) {
                    $item_counts[$player_id][$type] = 0;
                }
                $item_counts[$player_id][$type]++;
            }

            // Vérifier si un joueur a au moins 3 items du même type
            foreach ($item_counts as $player_id => $types) {
                foreach ($types as $type => $count) {
                    if ($count >= 3) {
                        $item_goal_bonus[$player_id] += 10;
                    }
                }
            }
        }
        if ($type == 12) // Nobody to impress 
        {
            // 1. Récupérer les rooms et les items associés aux joueurs
            $rooms = self::getPlayersRooms();

            $items = self::getPlayersItems();

            // 2. Organiser les items par room
            $items_by_room = [];

            // Remplir le tableau avec les items par position de room
            foreach ($items as $item) {
                $items_by_room[$item['room_type']] = $item; // Un seul item par room
            }

            // 3. Récupérer les plantes et leurs positions
            $plants = self::getPlayersPlants();

            // 4. Organiser les plantes par position
            $plants_by_coord = [];

            foreach ($plants as $plant) {
                $plants_by_coord[$plant['coord']] = $plant; // Associer la plante à sa position
            }

            // 5. Vérifier les rooms et les plantes adjacentes
            foreach ($rooms as $room) {
                $player_id = $room['player_id'];
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type']; // Type de la room
                $room_coord = $room['coord'];

                // Vérifier si la room contient un item de la même couleur
                if (isset($items_by_room[$room_coord])) {
                    $item_color = $this->_ITEM_CARDS[$items_by_room[$room_coord]['card_type']]['type']; // Type de l'item dans la room

                    // Vérifier si l'item dans la room a la même couleur que la room
                    if ($room_color === $item_color) {
                        // Calculer les positions adjacentes de la room
                        $adjacent_coords = [
                            $room_coord - 1,
                            $room_coord + 1,  // Positions à gauche et à droite
                            $room_coord - 10,
                            $room_coord + 10 // Positions au-dessus et en dessous
                        ];

                        $has_adjacent_plant_of_same_color = false;

                        // Vérifier si une plante adjacente a la même couleur que l'item
                        foreach ($adjacent_coords as $adjacent_coord) {
                            if (isset($plants_by_coord[$adjacent_coord])) {
                                $plant = $plants_by_coord[$adjacent_coord];
                                $plant_color = $this->_PLANT_CARDS[$plant['card_type']]['type']; // Type de la plante

                                // Si la plante adjacente a la même couleur que l'item, marquer comme trouvé
                                if ($plant_color === $item_color) {
                                    $has_adjacent_plant_of_same_color = true;
                                    break; // Pas besoin de vérifier plus
                                }
                            }
                        }

                        // Si la room contient un item de la même couleur et n'a aucune plante adjacente de la même couleur, ajouter 3 points
                        if (!$has_adjacent_plant_of_same_color) {
                            $item_goal_bonus[$player_id] += 3;
                        }
                    }
                }
            }
        }
        if ($type == 13) // Implement of Choice
        {
            // utiliser les stats pour trouver celui qui est le plus utilisé
            $player_ids =  array_keys($this->loadPlayersBasicInfos());
            foreach ($player_ids as $player_id) {
                $bonus = max(
                    $this->getStat('fertilizer_used', $player_id),
                    $this->getStat('hand_trowel', $player_id),
                    $this->getStat('watering_can', $player_id)
                );
                $item_goal_bonus[$player_id] = $bonus;
            }
        }
        return $item_goal_bonus;
    }


    public function getRoomGoalBonus($type)
    {
        $player_ids =  array_keys($this->loadPlayersBasicInfos());
        foreach ($player_ids as $player_id) {
            $room_goal_bonus[$player_id] = 0;
        }

        if ($type == 1) // Triple Treatment 
        {
            $rooms = self::getPlayersRooms();

            // Initialiser un tableau pour compter les plantes par joueur et par type réel
            $player_rooms_count = [];

            // Organiser les plantes par joueur et type réel
            foreach ($rooms as $room) {
                $player_id = $room['player_id'];
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type'];

                if (!isset($player_rooms_count[$player_id][$room_color])) {
                    $player_rooms_count[$player_id][$room_color] = 0;
                }

                // Incrémenter le compteur pour ce type de plante et ce joueur
                $player_rooms_count[$player_id][$room_color]++;
            }

            // Vérifier si un joueur a au moins 3 exemplaires d'un même type de room
            foreach ($player_rooms_count as $player_id => $room_types) {
                foreach ($room_types as $room_color => $count) {
                    if ($count >= 3) {
                        $room_goal_bonus[$player_id] += 3;
                    }
                }
            }
        }
        if ($type == 2) // Matchy Matchy 
        {
            $items = self::getPlayersItems();

            foreach ($items as $item) {
                $player_id = $item['player_id'];
                $item_color = floor($item['card_type'] / 10);

                $room_color = $this->_ROOM_CARDS[$item['room_type']]['type'];

                if ($item_color == $room_color) {
                    $room_goal_bonus[$player_id] += 1;
                }
            }
        }
        if ($type == 3) // Double Duty 
        {
            $plants = self::getPlayersPlants();

            // 2. Organiser les plantes par leur position (coord)
            $plants_by_position = [];
            foreach ($plants as $plant) {
                $plants_by_position[$plant['coord']] = [
                    'player_id' => $plant['player_id'],
                    'card_type' => $plant['card_type'],
                    'coord' => $plant['coord']
                ];
            }

            $rooms = self::getPlayersRooms();

            // Initialiser un tableau pour stocker les points des rooms
            $room_goal_bonus = [];

            foreach ($rooms as $room) {
                $room_coord = $room['coord'];
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type']; // Obtenir la couleur de la room
                $adjacent_plants = 0;

                // 4. Vérifier les positions adjacentes de la room
                $tests = [$room_coord - 1, $room_coord + 1, $room_coord - 10, $room_coord + 10];

                // Compter les plantes adjacentes de la même couleur
                foreach ($tests as $test_position) {
                    if (isset($plants_by_position[$test_position])) {
                        $adjacent_plant = $plants_by_position[$test_position];
                        $plant_color = $this->_PLANT_CARDS[$adjacent_plant['card_type']]['type']; // Couleur de la plante
                        if ($plant_color == $room_color) {
                            $adjacent_plants++;
                        }
                    }
                }

                // 5. Si deux ou plus de plantes adjacentes sont de la même couleur, attribuer un point à la room
                if ($adjacent_plants >= 2) {
                    $room_goal_bonus[$room['player_id']] += 1;
                }
            }
        }
        if ($type == 4) // Color Minimalist 
        {
            $rooms = self::getPlayersRooms();

            // Initialiser un tableau pour stocker les types de rooms présents pour chaque joueur
            $players_room_types = [];

            foreach ($rooms as $room) {
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type']; // Type entre 1 et 5
                $players_room_types[$room['player_id']][] = $room_color;
            }

            // Vérifier pour chaque joueur les types de rooms manquants et attribuer les points
            foreach ($player_ids as $player_id) {
                // Liste des types possibles (1 à 5)
                $missing_rooms = [1, 2, 3, 4, 5];

                // Retirer les types de rooms déjà possédés par le joueur
                if (isset($players_room_types[$player_id])) {
                    $owned_types = $players_room_types[$player_id];
                    $missing_rooms = array_diff($missing_rooms, $owned_types); // Enlever les types présents
                }

                // Attribuer 2 points pour chaque type de room manquant
                $room_goal_bonus[$player_id] += count($missing_rooms) * 2;
            }
        }
        if ($type == 5) // Coordinated Corridors 
        {
            $rooms = self::getPlayersRooms();

            // Organiser les rooms par ligne et par joueur
            $rooms_by_line = [];
            foreach ($rooms as $room) {
                $row = floor($room['coord'] / 10); // Calculer la ligne
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type']; // Récupérer le type réel de la room
                $rooms_by_line[$row][$room['player_id']][] = $room_color; // Ajouter la room à la ligne et au joueur
            }

            // Vérifier chaque ligne pour voir si elle contient uniquement des rooms de la même couleur
            foreach ($rooms_by_line as $row => $players_rooms) {
                foreach ($players_rooms as $player_id => $room_types) {
                    // Vérifier si toutes les rooms d'une ligne sont du même type
                    if (count(array_unique($room_types)) == 1) {
                        // Si la ligne contient uniquement des rooms de la même couleur, ajouter 3 points
                        $room_goal_bonus[$player_id] += 3;
                    }
                }
            }
        }
        if ($type == 6) // Diversified Designer
        {
            $rooms = self::getPlayersRooms();

            // Organiser les rooms par ligne et par joueur
            $rooms_by_line = [];

            foreach ($rooms as $room) {
                $row = floor($room['coord'] / 10); // Calculer la ligne
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type']; // Récupérer le type réel de la room
                $rooms_by_line[$row][$room['player_id']][] = $room_color; // Ajouter la room à la ligne et au joueur
            }

            // Vérifier chaque ligne pour voir si elle contient un seul exemplaire de chaque type de room
            foreach ($rooms_by_line as $row => $players_rooms) {
                foreach ($players_rooms as $player_id => $room_types) {
                    // Vérifier si toutes les rooms sont en un seul exemplaire (pas de duplication)
                    if (count($room_types) == count(array_unique($room_types))) {
                        // Si chaque type de room est unique dans cette ligne, ajouter 2 points
                        $room_goal_bonus[$player_id] += 2;
                    }
                }
            }
        }
        if ($type == 7) // Perfect Ambiance 
        {
            $rooms = self::getPlayersRooms();

            $plants = self::getPlayersPlants();

            // Organiser les plantes par position pour une recherche rapide
            $plants_by_position = [];

            foreach ($plants as $plant) {
                $plants_by_position[$plant['position']] = $plant;  // Associer chaque plante à sa position
            }

            // 3. Vérifier les rooms et leurs conditions de lightning
            foreach ($rooms as $room) {
                $room_position = $room['position'];
                $room_type = $room['card_type'];  // Type de la room
                $room_conditions = $this->_ROOM_CARDS[$room_type]['lightning'];  // Conditions de lightning de la room

                // Définir les positions adjacentes de la room (gauche, droite, haut, bas)
                $adjacent_positions = [
                    $room_position - 1,  // Position à gauche
                    $room_position + 1,  // Position à droite
                    $room_position - 10, // Position au-dessus
                    $room_position + 10  // Position en dessous
                ];

                $gain = true;  // Initialiser à true, et le rendre false si une condition échoue

                // Vérifier les plantes adjacentes
                foreach ($adjacent_positions as $adjacent_position) {
                    // Si une plante existe à cette position
                    if (isset($plants_by_position[$adjacent_position])) {
                        $plant = $plants_by_position[$adjacent_position];
                        $plant_type = $plant['card_type'];  // Type de la plante
                        $plant_conditions = $this->_PLANT_CARDS[$plant_type]['lightning'];  // Conditions de lightning de la plante

                        // Vérifier si les conditions de lightning de la room correspondent à la plante adjacente
                        // Comparer les conditions pour chaque direction (est, ouest, nord, sud)
                        if ($adjacent_position == $room_position - 1 && !in_array($room_conditions["east"], $plant_conditions)) {
                            $gain = false;
                            break;
                        }
                        if ($adjacent_position == $room_position + 1 && !in_array($room_conditions["west"], $plant_conditions)) {
                            $gain = false;
                            break;
                        }
                        if ($adjacent_position == $room_position + 10 && !in_array($room_conditions["north"], $plant_conditions)) {
                            $gain = false;
                            break;
                        }
                        if ($adjacent_position == $room_position - 10 && !in_array($room_conditions["south"], $plant_conditions)) {
                            $gain = false;
                            break;
                        }
                    }
                }

                // Si toutes les conditions sont remplies, attribuer un point à la room
                if ($gain) {
                    if (!isset($room_goal_bonus[$room['player_id']])) {
                        $room_goal_bonus[$room['player_id']] = 0;
                    }
                    $room_goal_bonus[$room['player_id']] += 1;
                }
            }
        }
        if ($type == 8) // Colorful Competition
        {
            $rooms = self::getPlayersRooms();

            // 2. Initialiser un tableau pour compter les rooms de chaque type par joueur
            $player_rooms_count = [];

            // 3. Compter le nombre de rooms de chaque type pour chaque joueur
            foreach ($rooms as $room) {
                $player_id = $room['player_id'];
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type']; // Obtenir le type réel de la room

                // Si le joueur n'existe pas encore dans le tableau, l'initialiser
                if (!isset($player_rooms_count[$player_id])) {
                    $player_rooms_count[$player_id] = [];
                }

                // Ajouter au compteur de rooms de ce type
                if (!isset($player_rooms_count[$player_id][$room_color])) {
                    $player_rooms_count[$player_id][$room_color] = 0;
                }
                $player_rooms_count[$player_id][$room_color]++;
            }

            // 4. Calculer les points pour chaque type de room
            foreach ($player_rooms_count as $player_id => $types) {
                foreach ($types as $type => $count) {
                    // Trouver le maximum de rooms d'un type parmi tous les joueurs
                    $max_rooms = 0;
                    foreach ($player_rooms_count as $other_player_id => $other_types) {
                        if (isset($other_types[$type])) {
                            $max_rooms = max($max_rooms, $other_types[$type]);
                        }
                    }

                    // Ajouter 2 points pour le joueur(s) ayant le plus de rooms de ce type
                    if ($count === $max_rooms) {
                        $room_goal_bonus[$player_id] += 2;
                    }
                }
            }
        }
        if ($type == 9) // Chaotic Coordinator 
        {
            $plants = self::getPlayersPlants();

            // 2. Organiser les plantes par leur position (coord)
            $plants_by_position = [];
            foreach ($plants as $plant) {
                $plants_by_position[$plant['coord']] = [
                    'player_id' => $plant['player_id'],
                    'card_type' => $plant['card_type'],
                    'coord' => $plant['coord']
                ];
            }

            $rooms = self::getPlayersRooms();

            // Initialiser un tableau pour stocker les points des rooms
            $room_goal_bonus = [];

            foreach ($rooms as $room) {
                $room_coord = $room['coord'];
                $room_color = $this->_ROOM_CARDS[$room['card_type']]['type']; // Obtenir la couleur de la room
                $adjacent_plants = 0;

                // 4. Vérifier les positions adjacentes de la room
                $tests = [$room_coord - 1, $room_coord + 1, $room_coord - 10, $room_coord + 10];

                // Vérifier si une plante adjacente a la même couleur
                foreach ($tests as $test_position) {
                    if (isset($plants_by_position[$test_position])) {
                        $adjacent_plant = $plants_by_position[$test_position];
                        $plant_color = $this->_PLANT_CARDS[$adjacent_plant['card_type']]['type']; // Couleur de la plante
                        if ($plant_color == $room_color) {
                            $adjacent_plants++;
                            break; // Si on trouve une plante adjacente de la même couleur, on arrête la recherche
                        }
                    }
                }

                // 5. Si aucune plante adjacente n'a la même couleur, attribuer 2 points à la room
                if ($adjacent_plants == 0) {
                    $room_goal_bonus[$room['player_id']] += 2;
                }
            }
        }
        if ($type == 10) // Four Corners 
        {
            // 1. Récupérer les cartes des joueurs aux positions 11, 15, 31, et 35
            $rooms = self::getObjectListFromDB("
                SELECT card_location AS player_id, card_type, card_location_arg AS coord
                FROM room
                WHERE card_location NOT IN ('deck', 'market')
                AND card_location_arg IN (11, 15, 31, 35)
            ");

            // 2. Organiser les cartes par joueur
            $player_room_types = [];

            // 3. Remplir le tableau avec les types de rooms pour chaque joueur
            foreach ($rooms as $room) {
                $player_room_types[$room['player_id']][] = $this->_ROOM_CARDS[$room['card_type']]['type'];
            }

            // 4. Vérifier que chaque joueur a bien 4 cartes et attribuer les points si les conditions sont remplies
            foreach ($player_room_types as $player_id => $room_types) {
                // Vérifier que le joueur possède bien 4 cartes aux positions spécifiées
                if (count($room_types) == 4) {

                    // Vérifier si toutes les rooms du joueur sont du même type ou de types différents
                    if (count(array_unique($room_types)) == 1 || count(array_unique($room_types)) == 4) {
                        $room_goal_bonus[$player_id] += 4;
                    }
                }
            }
        }
        if ($type == 11) // Balancing Act 
        {
            // 1. Récupérer les rooms et les plantes associées aux joueurs
            $rooms = self::getPlayersRooms();

            $plants = self::getPlayersPlants();

            // 2. Organiser les plantes par joueur et par position
            $player_plants = [];

            // Remplir le tableau avec les plantes par joueur et par coordonnée
            foreach ($plants as $plant) {
                $player_plants[$plant['player_id']][$plant['coord']] = $this->_PLANT_CARDS[$plant['card_type']]['type'];
            }

            // 3. Organiser les rooms par joueur
            $player_rooms = [];

            // Remplir le tableau avec les rooms par joueur et par coordonnée
            foreach ($rooms as $room) {
                $player_rooms[$room['player_id']][$room['coord']] = $this->_ROOM_CARDS[$room['card_type']]['type'];
            }

            // 4. Vérifier les adjacences pour chaque room de chaque joueur
            foreach ($player_rooms as $player_id => $rooms) {
                $points_earned = true;

                // 5. Parcourir chaque room du joueur
                foreach ($rooms as $room_coord => $room_type) {
                    $adjacent_found = false;

                    // 6. Vérifier les positions adjacentes de la room (gauche, droite, haut, bas)
                    $tests = [$room_coord - 1, $room_coord + 1, $room_coord - 10, $room_coord + 10];

                    foreach ($tests as $adjacent_coord) {
                        // Vérifier si cette coordonnée est une plante et si sa couleur correspond à la room
                        if (isset($player_plants[$player_id][$adjacent_coord])) {
                            $plant_type = $player_plants[$player_id][$adjacent_coord];
                            if ($room_type == $plant_type) {
                                $adjacent_found = true;
                                break;
                            }
                        }
                    }

                    // Si une room n'a pas de plante adjacente du même type, marquer l'échec
                    if (!$adjacent_found) {
                        $points_earned = false;
                        break; // Sortir de la boucle dès qu'une room ne remplit pas la condition
                    }
                }

                // 7. Si toutes les rooms du joueur ont au moins une plante adjacente du même type, donner 4 points
                if ($points_earned) {
                    if (!isset($room_goal_bonus[$player_id])) {
                        $room_goal_bonus[$player_id] = 0;
                    }
                    $room_goal_bonus[$player_id] += 4;
                }
            }
        }
        if ($type == 12) // Match Three 
        {
            // 1. Récupérer les rooms et les plantes associées aux joueurs
            $rooms = self::getPlayersRooms();

            $plants = self::getPlayersPlants();

            // 2. Organiser les plantes par joueur et par position
            $player_plants = [];

            // Remplir le tableau avec les plantes par joueur et par coordonnée
            foreach ($plants as $plant) {
                $player_plants[$plant['player_id']][$plant['coord']] = $this->_PLANT_CARDS[$plant['card_type']]['type'];
            }

            // 3. Organiser les rooms par joueur
            $player_rooms = [];

            // Remplir le tableau avec les rooms par joueur et par coordonnée
            foreach ($rooms as $room) {
                $player_rooms[$room['player_id']][$room['coord']] = $this->_ROOM_CARDS[$room['card_type']]['type'];
            }

            // 4. Vérifier les adjacences pour chaque room de chaque joueur
            foreach ($player_rooms as $player_id => $rooms) {
                // 5. Parcourir chaque room du joueur
                foreach ($rooms as $room_coord => $room_type) {
                    $adjacent_plants_count = 0;

                    // 6. Vérifier les positions adjacentes de la room (gauche, droite, haut, bas)
                    $tests = [$room_coord - 1, $room_coord + 1, $room_coord - 10, $room_coord + 10];

                    foreach ($tests as $adjacent_coord) {
                        // Vérifier si cette coordonnée est une plante et si sa couleur correspond à la room
                        if (isset($player_plants[$player_id][$adjacent_coord])) {
                            $plant_type = $player_plants[$player_id][$adjacent_coord];
                            if ($room_type == $plant_type) {
                                $adjacent_plants_count++;
                            }
                        }
                    }

                    // Si la room a 3 ou plus de plantes adjacentes du même type, lui donner 3 points
                    if ($adjacent_plants_count >= 3) {
                        if (!isset($room_goal_bonus[$player_id])) {
                            $room_goal_bonus[$player_id] = 0;
                        }
                        $room_goal_bonus[$player_id] += 3;
                    }
                }
            }
        }
        if ($type == 13) // My Happy Place
        {
            // calculé dans la fonction final scores
        }
    }

    function getPlayersRooms()
    {
        return self::getObjectListFromDB("
            SELECT card_type, card_location AS player_id, card_location_arg AS coord
            FROM room
            WHERE card_location NOT IN ('deck', 'market', 'discard')
        ");
    }

    function getPlayersItems()
    {
        return self::getObjectListFromDB("
            SELECT card_location AS player_id, card_type, card_location_arg AS room_type 
            FROM tile
            WHERE card_location NOT IN ('deck', 'market', 'discard') AND card_location_arg < 99
        ");
    }

    function getPlayersPlants()
    {
        return self::getObjectListFromDB("
            SELECT card_type, card_location AS player_id, card_location_arg AS coord
            FROM plant
            WHERE card_location NOT IN ('deck', 'market', 'discard')
        ");
    }

    function getPlayersPots()
    {
        return self::getObjectListFromDB("
            SELECT card_type, card_location AS player_id, card_location_arg AS plant_type 
            FROM pot WHERE card_location NOT IN ('deck', 'discard', 'market')
        ");
    }

    // Stats turns

    function updateNbTurns()
    {
        $player_id = self::getActivePlayerId();
        $this->incStat(1, 'turns_number', $player_id);
        if (self::getPlayerNoById($player_id) == 1) {
            $this->incStat(1, 'turns_number');
        }
    }


    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____  _                                    _   _                 
    //    |  __ \| |                                  | | (_)                
    //    | |__) | | __ _ _   _  ___ _ __    __ _  ___| |_ _  ___  _ __  ___ 
    //    |  ___/| |/ _` | | | |/ _ \ '__|  / _` |/ __| __| |/ _ \| '_ \/ __|
    //    | |    | | (_| | |_| |  __/ |    | (_| | (__| |_| | (_) | | | \__ \
    //    |_|    |_|\__,_|\__, |\___|_|     \__,_|\___|\__|_|\___/|_| |_|___/
    //                     __/ |                                             
    //                    |___/                                              
    /////////////////////////////////////////////////////////////////////////////////


    public function actSelect(string $arg1)
    {
        if ($this->gamestate->state()['name'] == "playerTurnMulti") {
            $explode = explode('_', $arg1);
            $player_id = $this->getCurrentPlayerId(); // CURRENT!!! not active
            $player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$player_id}");

            $card_firstplant_type = self::getUniqueValueFromDB("SELECT card_type FROM plant WHERE card_location={$player_id} AND card_location_arg = 99");
            $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_type = {$card_firstplant_type}");
            $card_before['genre'] = 'plant';
            self::DbQuery("UPDATE plant set card_location_arg = $explode[1] WHERE card_type = {$card_firstplant_type}");

            $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_type = {$card_firstplant_type}");

            game::$instance->notifyAllPlayers(
                'moveCardToHouse',
                clienttranslate('${player_name} places a plant in the house'),
                array(
                    'player_name' => $player_name,
                    'card_before' => $card_before,
                    'card_after' => $card_after
                )
            );

            game::$instance->TestVerdoyance($player_id, 'plant', $card_firstplant_type, $explode[1]);

            $this->giveExtraTime($this->getCurrentPlayerId());
            $this->gamestate->setPlayerNonMultiactive($player_id, 'next'); // desactivation player et redirection vers next quand tous les joueurs seront desactivés
        } else {

            self::checkArgs($arg1);

            $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
            $this->callPending($pending, true, $arg1);
            self::DbQuery("delete from pending where id=" . $pending['id']);
            //$this->giveExtraTime(self::getActivePlayerId());
            $this->gamestate->nextState('next');
        }
    }

    public function actButton(string $arg1)
    {

        self::checkArgs($arg1);

        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=" . $pending['id']);
        //$this->giveExtraTime(self::getActivePlayerId());
        $this->gamestate->nextState('next');
    }

    public function actValidatemultiHandtrowel(string $arg1)
    {
        $pot_origin = 0;
        $nbre_players = count(self::getObjectListFromDB("SELECT player_id FROM player", true));
        $explode = explode('_', $arg1);
        $player_id = self::getActivePlayerId();
        $player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$player_id}");

        foreach ($explode as $plant_type) {

            self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$plant_type}'");
            $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$plant_type}'");
            $max_verdoiement = game::$instance->_PLANT_CARDS[$plant_type]['verdancy'];

            if ($total_verdoiement < $max_verdoiement) {
                $valeur_pot = -1;
            } else {

                if ($nbre_players >= 2) {

                    $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'deck' ORDER BY card_type DESC LIMIT 1");
                    $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE pot set card_location = {$player_id} WHERE card_id = '{$pot_id}'");
                    self::DbQuery("UPDATE pot set card_location_arg = $plant_type WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant set card_type_arg = 0 WHERE card_type = '{$plant_type}'");
                } else {
                    $nbre_pot_market = count(self::getObjectListFromDB("SELECT card_id FROM pot WHERE card_location = 'market'", true));


                    if ($nbre_pot_market == 4) {
                        $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'market' AND card_location_arg =4");
                        $pot_origin = 'market_pot_4';
                    } else {
                        $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'discard' ORDER BY card_type ASC LIMIT 1");
                        $pot_origin = 'pot_discard_' . $pot_id;
                    }

                    $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE pot SET card_location = {$player_id}, card_location_arg = $plant_type WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$plant_type}'");
                }
            }

            if ($nbre_players >= 2) {
                game::$instance->notifyAllPlayers(
                    'addVerdancy',
                    '',
                    array(
                        'player_name' => $player_name,
                        'player_id' => $player_id,
                        'plant_type' => $plant_type,
                        'verdancy_added' => 1,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => false,

                    )
                );
            } else {
                game::$instance->notifyAllPlayers(
                    'addVerdancySolo',
                    '',
                    array(
                        'player_name' => $player_name,
                        'player_id' => $player_id,
                        'plant_type' => $plant_type,
                        'verdancy_added' => 1,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => false,
                        'pot_origin' => $pot_origin

                    )
                );
            }
        }



        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=" . $pending['id']);
        $this->gamestate->nextState('next');
    }


    public function actValidateThumb(string $arg1)
    {

        $player_id = self::getActivePlayerId();
        $player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$player_id}");
        $explode = explode(';', $arg1);
        $info = array_slice($explode, 1);

        if ($explode[0] == "validate_tokens") {
            $old_tiles = array();
            $new_tiles = array();

            $new_position = -1;


            foreach ($info as $tiles) {
                $tile = explode('_', $tiles);
                $info_before_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_id= '{$tile[1]}'");
                $before_tile_id = $tile[1];

                game::$instance->tile->moveCard($before_tile_id, 'deck', $new_position);
                game::$instance->tile->pickCardForLocation('deck', 'market', $info_before_tile['location_arg']);

                $info_after_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_location = 'market' AND card_location_arg = '{$info_before_tile['location_arg']}'");

                $old_tiles[] = $info_before_tile;
                $new_tiles[] = $info_after_tile;

                $new_position--;
            }

            $this->tile->shuffle('deck');

            game::$instance->notifyAllPlayers(
                'resetTiles',
                '',
                array(
                    'player_name' => $player_name,
                    'player_id' => $player_id,
                    'old_tiles' => $old_tiles,
                    'new_tiles' => $new_tiles,

                )
            );

            self::DbQuery("UPDATE player set player_thumb = player_thumb - 2 WHERE player_id = {$player_id}");

            $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
            $this->callPending($pending, true, $arg1);
            self::DbQuery("delete from pending where id=" . $pending['id']);
            $this->gamestate->nextState('next');
        }

        if ($explode[0] == "validate_cards") {
            $old_cards = array();
            $new_cards = array();

            $room_new_position = self::getUniqueValueFromDB("SELECT MIN(card_location_arg) FROM room WHERE card_location = 'deck'") - 1;
            $plant_new_position = self::getUniqueValueFromDB("SELECT MIN(card_location_arg) FROM plant WHERE card_location = 'deck'") - 1;

            foreach ($info as $cards) {
                $card = explode('_', $cards);
                $info_before_card = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM {$card[0]} WHERE card_type = '{$card[1]}'");
                $info_before_card['genre'] = $card[0];
                if ($card[0] == 'room') {

                    game::$instance->room->moveCard($info_before_card['id'], 'deck', $room_new_position);
                    game::$instance->room->pickCardForLocation('deck', 'market', $info_before_card['location_arg']);
                    $room_new_position--;
                } else {

                    game::$instance->plant->moveCard($info_before_card['id'], 'deck', $plant_new_position);
                    game::$instance->plant->pickCardForLocation('deck', 'market', $info_before_card['location_arg']);
                    $plant_new_position--;
                }



                $info_after_card = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM {$card[0]} WHERE card_location = 'market' AND card_location_arg = '{$info_before_card['location_arg']}'");
                $info_after_card['genre'] = $card[0];

                $old_cards[] = $info_before_card;
                $new_cards[] = $info_after_card;
            }



            game::$instance->notifyAllPlayers(
                'resetCards',
                '',
                array(
                    'player_name' => $player_name,
                    'player_id' => $player_id,
                    'old_cards' => $old_cards,
                    'new_cards' => $new_cards,

                )
            );

            self::DbQuery("UPDATE player set player_thumb = player_thumb - 2 WHERE player_id = {$player_id}");

            $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
            $this->callPending($pending, true, $arg1);
            self::DbQuery("delete from pending where id=" . $pending['id']);
            $this->gamestate->nextState('next');
        }


        if ($explode[0] == "validate_verdancy") {

            $nbre_players = count(self::getObjectListFromDB("SELECT player_id FROM player", true));
            $explode = explode('_', $explode[1]);

            $before_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$explode[1]}'");
            self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$explode[1]}'");
            $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$explode[1]}'");
            $max_verdoiement = game::$instance->_PLANT_CARDS[$explode[1]]['verdancy'];

            $pot_origin = 0;

            if ($total_verdoiement < $max_verdoiement) {
                $valeur_pot = -1;
                $delta = 1;
            } else {

                if ($nbre_players >= 2) {
                    $delta = $max_verdoiement - $before_verdoiement;

                    $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'deck' ORDER BY card_type DESC LIMIT 1");
                    $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE pot set card_location = {$player_id} WHERE card_id = '{$pot_id}'");
                    self::DbQuery("UPDATE pot set card_location_arg = $explode[1] WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant set card_type_arg = 0 WHERE card_type = '{$explode[1]}'");
                } else {
                    $nbre_pot_market = count(self::getObjectListFromDB("SELECT card_id FROM pot WHERE card_location = 'market'", true));


                    if ($nbre_pot_market == 4) {
                        $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'market' AND card_location_arg =4");
                        $pot_origin = 'market_pot_4';
                    } else {
                        $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'discard' ORDER BY card_type ASC LIMIT 1");
                        $pot_origin = 'pot_discard_' . $pot_id;
                    }

                    $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE pot SET card_location = {$player_id}, card_location_arg = $explode[1] WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$explode[1]}'");
                }
            }

            if ($nbre_players >= 2) {
                game::$instance->notifyAllPlayers(
                    'addVerdancy',
                    '',
                    array(
                        'player_name' => $player_name,
                        'player_id' => $player_id,
                        'plant_type' => $explode[1],
                        'verdancy_added' => $delta,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => true,

                    )
                );
            } else {
                game::$instance->notifyAllPlayers(
                    'addVerdancySolo',
                    '',
                    array(
                        'player_name' => $player_name,
                        'player_id' => $player_id,
                        'plant_type' => $explode[1],
                        'verdancy_added' => $delta,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => true,
                        'pot_origin' => $pot_origin

                    )
                );
            }


            self::DbQuery("UPDATE player set player_thumb = player_thumb - 2 WHERE player_id = {$player_id}");

            $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
            $this->callPending($pending, true, $arg1);
            self::DbQuery("delete from pending where id=" . $pending['id']);
            $this->gamestate->nextState('next');
        }


        if ($explode[0] == "validate_mixed") {

            $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
            self::DbQuery("delete from pending where id=" . $pending['id']);

            $explode_first = explode('_', $info[0]);
            if ($explode_first[0] == 'tile') {
                game::$instance->addPending($player_id, "NormalTurnStep2Thumb", $info[1], $info[0]);
            } else {
                game::$instance->addPending($player_id, "NormalTurnStep2Thumb", $info[0], $info[1]);
            }


            $this->gamestate->nextState('next');
        }
    }


    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____                             _        _                                                    _       
    //    / ____|                           | |      | |                                                  | |      
    //    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _ _ __ __ _ _   _ _ __ ___   ___ _ __ | |_ ___ 
    //    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` | '__/ _` | | | | '_ ` _ \ / _ \ '_ \| __/ __|
    //    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | | | (_| | |_| | | | | | |  __/ | | | |_\__ \
    //     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|_|  \__, |\__,_|_| |_| |_|\___|_| |_|\__|___/
    //                                                                    __/ |                                   
    //                                                                   |___/                                    
    ///////////////////////////////////////////////////////////////////////////////// 

    public function argPlayerTurnMulti()
    {
        $args = array();

        $players = self::getObjectListFromDB("SELECT player_id FROM player", true);

        foreach ($players as $player) {

            $card_firstplant_type = self::getUniqueValueFromDB("SELECT card_type FROM plant WHERE card_location={$player} AND card_location_arg = 99");

            $args["selectable"][$player][] = 'grid_34_' . $player;
            $args["selectable"][$player][] = 'grid_25_' . $player;
            $args["selectable"][$player][] = 'grid_36_' . $player;
            $args["selectable"][$player][] = 'grid_45_' . $player;

            $args["card"][$player][] = 'plant_' . $card_firstplant_type;
        }

        return $args;
    }


    public function argPlayerTurn()
    {
        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $arg = $this->callPending($pending, false);

        return $arg;
    }


    ///////////////////////////////////////////////////////////////////////////////// 
    //      _____                            _        _                    _   _                 
    //     / ____|                          | |      | |                  | | (_)                
    //    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _  ___| |_ _  ___  _ __  ___ 
    //    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` |/ __| __| |/ _ \| '_ \/ __|
    //    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | (__| |_| | (_) | | | \__ \
    //     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|\___|\__|_|\___/|_| |_|___/
    //                                                                                       
    /////////////////////////////////////////////////////////////////////////////////     


    public function callPending($pending, $execute, $arg1 = null, $arg2 = null)
    {
        $nb_players = count(self::getObjectListFromDB("SELECT player_id FROM player", true));

        $obj = $this;
        if ($pending['player_id'] != null) {
            if ($nb_players >= 2) {
                $obj = new Pending($pending['player_id']);
            } else {
                $obj = new PendingSolo($pending['player_id']);
            }
        }

        $fname = "";
        if (!$execute) {
            $fname .= "arg";
        }
        $fname .= $pending['function'];

        $ret = null;
        if (method_exists($obj, $fname)) {
            $ret = $obj->$fname($pending['arg'], $pending['arg2'], $arg1, $arg2);
        }

        return $ret;
    }


    public function stPending()
    {

        $pending =  self::getObjectFromDB("SELECT * FROM pending order by id desc limit 1");
        if ($pending == null) {
            $this->gamestate->nextState('end');
        } else {
            $args = $this->callPending($pending, false);

            ////////////// attention changement car si on donne la main a un autre joueur sans arg l'id de l active player ne change pas 
            if ($pending['player_id'] != self::getActivePlayerId()) {


                //change active player      
                $this->gamestate->changeActivePlayer($pending['player_id']);
                $this->gamestate->nextState('same');
            } else if ($args == null || (count($args['selectable']) == 0 && count($args['buttons']) == 0)) {
                //no args required, execute
                $this->callPending($pending, true);
                self::DbQuery("delete from pending where id=" . $pending['id']);
                $this->gamestate->nextState('same');
            } else {


                $this->gamestate->nextState('player');
            }
        }
    }


    public function st_MultiPlayerActivation()
    {


        game::$instance->gamestate->setAllPlayersMultiactive();
    }


    ///////////////////////////////////////////////////////////////////////////////// 
    //     _____  ____                                    _      
    //    |  __ \|  _ \                                  | |     
    //    | |  | | |_) |  _   _ _ __   __ _ _ __ __ _  __| | ___ 
    //    | |  | |  _ <  | | | | '_ \ / _` | '__/ _` |/ _` |/ _ \
    //    | |__| | |_) | | |_| | |_) | (_| | | | (_| | (_| |  __/
    //    |_____/|____/   \__,_| .__/ \__, |_|  \__,_|\__,_|\___|
    //                         | |     __/ |                     
    //                         |_|    |___/                      
    /////////////////////////////////////////////////////////////////////////////////  


    public function upgradeTableDb($from_version) {}




    /////////////////////////////////////////////////////////////////////////////////
    //    ______               _     _      
    //   |___  /              | |   (_)     
    //      / / ___  _ __ ___ | |__  _  ___ 
    //     / / / _ \| '_ ` _ \| '_ \| |/ _ \
    //    / /_| (_) | | | | | | |_) | |  __/
    //   /_____\___/|_| |_| |_|_.__/|_|\___|
    //                                   
    /////////////////////////////////////////////////////////////////////////////////     

    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default: {
                        $player_id = $this->getActivePlayerId();
                        self::DbQuery("delete from pending where player_id = {$player_id}");
                        $this->gamestate->nextState("zombiePass");
                        break;
                    }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
}
