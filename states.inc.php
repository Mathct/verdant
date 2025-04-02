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
 * states.inc.php
 *
 * verdant game states description
 *
 */

$machinestates = [

    // The initial state. Please do not modify.

    1 => [
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => ["" => 4]
    ],


    2 => [
        "name" => "pending",
        "description" => '',
        "type" => "game",
        "action" => "stPending",
        "updateGameProgression" => true,
        "transitions" => ["end" => 99, "player" => 3, "same" => 2]
    ],


    3 => [
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must take an action'),
        "descriptionmyturn" => clienttranslate('${you} must take an action'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => ["actSelect", "actButton", "actValidatemultiHandtrowel", "actValidateThumb"],
        "transitions" => ["next" => 2, "zombiePass" => 2, "end" => 99]
    ],


    4 => [
        "name" => "playerTurnMulti",
        "description" => clienttranslate('The other players must perform their actions'),
        "descriptionmyturn" => clienttranslate('${you} must place the first plant'),
        "type" => "multipleactiveplayer",
        "args" => "argPlayerTurnMulti",
        "action" => 'st_MultiPlayerActivation',
        "possibleactions" => ["actSelect"],
        "transitions" => ["next" => 2, "same" => 4, "zombiePass" => 4, "end" => 99]
    ],


    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],

];
