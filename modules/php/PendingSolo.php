<?php

namespace Bga\Games\verdant;   // ATTENTION NOM DU JEU
use APP_GameClass;

//require_once 'actions/Actions.php'; // Inclure le fichier contenant les fonctions

class PendingSolo extends APP_GameClass
{
    //use ActionsTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        $this->player_color = $p['player_color'];

        /// PREFERENCE DE CONFIRMATION

        $this->player_pref_confirm = self::getUniqueValueFromDB("SELECT pgp_value FROM bga_user_preferences WHERE pgp_player='{$this->player_id}' AND pgp_preference_id = 100");
    }


    function argNormalTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');
        $ret['titleyou'] = array();

        $thumb = self::getUniqueValueFromDB("SELECT player_thumb FROM player WHERE player_id={$this->player_id}");

        $possible_plant = game::$instance->PossiblePosition($this->player_id, 'plant_0');
        $possible_room = game::$instance->PossiblePosition($this->player_id, 'room_0');

        if (($possible_plant != null) || ($possible_room != null)) {
            if ($thumb >= 2) {
                $ret['titleyou'] = clienttranslate('${you} must choose a market card or use');
                $ret['buttons'][] = 'thumb';
            } else {
                $ret['titleyou'] = clienttranslate('${you} must choose a market card');
            }

            $plants = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location = 'market'", true);
            $rooms = self::getObjectListFromDB("SELECT card_type FROM room WHERE card_location = 'market'", true);


            foreach ($plants as $plant) {
                $ret["selectable"][] = 'plant_' . $plant;
            }
            foreach ($rooms as $room) {
                $ret["selectable"][] = 'room_' . $room;
            }
        }



        return $ret;
    }

    function NormalTurn($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == null) {
            game::$instance->addPending($this->player_id, "EndOfGame");
        } elseif ($varg1 == 'thumb') {
            game::$instance->addPending($this->player_id, "UseThumb", 1);
        } else {
            game::$instance->addPending($this->player_id, "NormalTurnStep2", $varg1);
        }
    }


    function argNormalTurnStep2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');
        $ret['titleyou'] = clienttranslate('${you} must must choose a location');

        $ret["selected"][] = $parg1;

        $ret["card"][] = $parg1;

        $ret["selectable"] = game::$instance->PossiblePosition($this->player_id, $parg1);

        $ret['buttons'][] = 'cancel';

        return $ret;
    }

    function NormalTurnStep2($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'cancel') {
            game::$instance->addPending($this->player_id, "NormalTurn");
        } else {
            $explode_market = explode('_', $parg1);
            $explode_position = explode('_', $varg1);

            if ($explode_market[0] == 'plant') {
                $place_market = self::getUniqueValueFromDB("SELECT card_location_arg FROM plant WHERE card_type = '{$explode_market[1]}'");

                $card_id = self::getUniqueValueFromDB("SELECT card_id FROM plant WHERE card_type = '{$explode_market[1]}'");
                $card_type = $explode_market[1];

                $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_id = {$card_id}");
                $card_before['genre'] = 'plant';


                $thumb = self::getUniqueValueFromDB("SELECT card_thumb FROM plant WHERE card_type = '{$explode_market[1]}'");
                self::DbQuery("UPDATE plant set card_thumb = 0 WHERE card_id = '{$card_id}'");
                self::DbQuery("UPDATE player set player_thumb = player_thumb + $thumb WHERE player_id = '{$this->player_id}'");
                game::$instance->plant->moveCard($card_id, $this->player_id, $explode_position[1]);

                $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_id = {$card_id}");


                game::$instance->notifyAllPlayers(
                    'moveCardToHouse',
                    clienttranslate('${player_name} places a plant in the house'),
                    array(
                        'player_name' => $this->player_name,
                        'card_before' => $card_before,
                        'card_after' => $card_after


                    )
                );
            }
            if ($explode_market[0] == 'room') {
                $place_market = self::getUniqueValueFromDB("SELECT card_location_arg FROM room WHERE card_type = '{$explode_market[1]}'");

                $card_id = self::getUniqueValueFromDB("SELECT card_id FROM room WHERE card_type = '{$explode_market[1]}'");
                $card_type = $explode_market[1];

                $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM room WHERE card_id = {$card_id}");
                $card_before['genre'] = 'room';

                $thumb = self::getUniqueValueFromDB("SELECT card_thumb FROM room WHERE card_type = '{$explode_market[1]}'");
                self::DbQuery("UPDATE room set card_thumb = 0 WHERE card_id = '{$card_id}'");
                self::DbQuery("UPDATE player set player_thumb = player_thumb + $thumb WHERE player_id = '{$this->player_id}'");
                game::$instance->room->moveCard($card_id, $this->player_id, $explode_position[1]);

                $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM room WHERE card_id = {$card_id}");

                game::$instance->notifyAllPlayers(
                    'moveCardToHousee',
                    clienttranslate('${player_name} places a room in the house'),
                    array(
                        'player_name' => $this->player_name,
                        'card_before' => $card_before,
                        'card_after' => $card_after


                    )
                );
            }

            game::$instance->TestVerdoyanceSolo($this->player_id, $explode_market[0], $card_type, $explode_position[1]);


            game::$instance->addPending($this->player_id, "NormalTurnStep3", $place_market);
        }
    }

    function argNormalTurnStep3($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');
        $ret['titleyou'] = clienttranslate('${you} can use');




        $tile_market_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");
        $tile_market_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");

        if ($tile_market_type != null) {
            $ret["selectable"][] = 'tile_' . $tile_market_id;
            $ret['buttons'][] = 'tilebt_' . $tile_market_type;
        }

        $tile_reserve_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");
        $tile_reserve_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");

        if ($tile_reserve_type != null) {
            $ret["selectable"][] = 'tile_' . $tile_reserve_id;
            $ret['buttons'][] = 'tilebt_' . $tile_reserve_type;
        }



        if (($tile_market_type != null) || ($tile_reserve_type != null)) {

            $thumb = self::getUniqueValueFromDB("SELECT player_thumb FROM player WHERE player_id={$this->player_id}");
            $all_plants = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location = '{$this->player_id}'", true);
            $all_pots = self::getObjectListFromDB("SELECT card_location_arg FROM pot WHERE card_location = '{$this->player_id}'", true);
            $plants_without_pots = array_diff($all_plants, $all_pots);

            if ((!empty($plants_without_pots)) && ($thumb >= 2)) {
                $ret['buttons'][] = 'thumb';
            }



            $ret['buttons'][] = 'pass';
        }



        return $ret;
    }

    function NormalTurnStep3($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'thumb') {
            game::$instance->addPending($this->player_id, "UseThumb", 2, "NormalTurnStep3_" . $parg1);
        } elseif ($varg1 == 'pass') {

            game::$instance->addPending($this->player_id, "ChooseReserve", $parg1);
        } elseif ($varg1 == null) {
            game::$instance->addPending($this->player_id, "FinalTurn");
        } else {
            $tile_market_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");
            $tile_market_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");

            $tile_reserve_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");
            $tile_reserve_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");

            if (($varg1 == "tile_" . $tile_market_id) || ($varg1 == "tilebt_" . $tile_market_type)) {


                if ($tile_market_type < 60) {
                    game::$instance->addPending($this->player_id, "PlaceObjet", $tile_market_id, $parg1);
                } else {
                    game::$instance->addPending($this->player_id, "UseTool", $tile_market_id, $parg1);
                }
            }


            if (($varg1 == "tile_" . $tile_reserve_id) || ($varg1 == "tilebt_" . $tile_reserve_type)) {


                if ($tile_reserve_type < 60) {
                    game::$instance->addPending($this->player_id, "PlaceObjet", $tile_reserve_id, $parg1);
                } else {
                    game::$instance->addPending($this->player_id, "UseTool", $tile_reserve_id, $parg1);
                }
            }
        }
    }



    function argPlaceObjet($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');

        $type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_id = '{$parg1}'");

        $x = $type % 10 - 1;
        $y = floor($type / 10) - 1;

        if ($y == 5) {
            $tool_y = $x;
            $ret['icon'] = '<span class="item_bt" style="background-position: -900% -' . $tool_y . '00% ;"></span>';
        } else {
            $ret['icon'] = '<span class="item_bt" style="background-position: -' . $x . '00% -' . $y . '00% ;"></span>';
        }


        $count_card_with_token = count(self::getObjectListFromDB("SELECT card_location_arg FROM tile WHERE card_location = '{$this->player_id}' AND card_location_arg != 99", true));
        $count_card_room = count(self::getObjectListFromDB("SELECT card_type FROM room WHERE card_location = '{$this->player_id}'", true));

        if ($count_card_room > $count_card_with_token) {
            $ret['titleyou'] = clienttranslate('${you} must choose a room for #icon#');
        } else {
            $ret['titleyou'] = clienttranslate('${you} cannot place #icon#');
        }



        $ret["selected"][] = 'tile_' . $parg1;


        $all_card_with_token = self::getObjectListFromDB("SELECT card_location_arg FROM tile WHERE card_location = '{$this->player_id}' AND card_location_arg != 99", true);
        $all_card_room = self::getObjectListFromDB("SELECT card_type FROM room WHERE card_location = '{$this->player_id}'", true);

        foreach ($all_card_room as $room) {
            if (!in_array($room, $all_card_with_token)) {

                $ret["selectable"][] = 'room_' . $room;
            }
        }


        $ret['buttons'][] = 'cancel';

        return $ret;
    }

    function PlaceObjet($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'cancel') {
            game::$instance->addPending($this->player_id, "NormalTurnStep3", $parg2);
        } else {
            $explode = explode('_', $varg1);

            $before_emplacement = self::getUniqueValueFromDB("SELECT card_location FROM tile WHERE card_id = '{$parg1}'");

            if ($before_emplacement == 'market') {

                $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg 
                    FROM tile 
                    WHERE card_location = 'market' AND card_location_arg = '{$parg2}'");
            } else {

                $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg 
                    FROM tile 
                    WHERE card_location = '{$this->player_id}' AND card_location_arg = 99");
            }

            $info_card = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg 
                FROM room 
                WHERE card_type= '{$explode[1]}'");

            game::$instance->tile->moveCard($parg1, $this->player_id, $explode[1]);

            game::$instance->notifyAllPlayers(
                'moveTileToHouse',
                clienttranslate('${player_name} places an item in a room'),
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,
                    'tile' => $info_tile,
                    'card' => $info_card,
                )
            );

            game::$instance->addPending($this->player_id, "NormalTurnStep3", $parg2);
        }
    }



    function argUseTool($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');

        $type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_id = '{$parg1}'");
        $location = self::getUniqueValueFromDB("SELECT card_location FROM tile WHERE card_id = '{$parg1}'");
        $location_arg = self::getUniqueValueFromDB("SELECT card_location_arg FROM tile WHERE card_id = '{$parg1}'");

        $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_id = '{$parg1}'");


        $ret["selected"][] = 'tile_' . $info_tile['id'];

        $x = $type % 10 - 1;
        $y = floor($type / 10) - 1;

        if ($y == 5) {
            $tool_y = $x;
            $ret['icon'] = '<span class="item_bt" style="background-position: -900% -' . $tool_y . '00% ;"></span>';
        } else {
            $ret['icon'] = '<span class="item_bt" style="background-position: -' . $x . '00% -' . $y . '00% ;"></span>';
        }

        $all_plants = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location = '{$this->player_id}'", true);
        $all_rooms = self::getObjectListFromDB("SELECT card_type FROM room WHERE card_location = '{$this->player_id}'", true);
        $all_pots = self::getObjectListFromDB("SELECT card_location_arg FROM pot WHERE card_location = '{$this->player_id}'", true);

        if ($type == 61) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} must choose a plant (+3 Verdancy)');
            foreach ($all_plants as $plant) {
                if (!in_array($plant, $all_pots)) {
                    $ret["selectable"][] = 'plant_' . $plant;
                }
            }
        }

        if ($type == 62) {
            $ret['titleyou'] = clienttranslate('#icon# ${you} must choose up to 3 different plants (+1 Verdancy)');

            foreach ($all_plants as $plant) {
                if (!in_array($plant, $all_pots)) {
                    $ret["selectablemulti"][] = 'plant_' . $plant;
                }
            }



            $ret['buttons'][] = 'validatemulti_handtrowel';
        }

        if ($type == 63) {
            foreach ($all_rooms as $room) {

                $ret["selectable"][] = 'room_' . $room;
            }

            $ret['titleyou'] = clienttranslate('#icon# ${you} must choose a room (+1 Verdancy to all adjacent plants)');
        }






        $ret['buttons'][] = 'cancel';

        return $ret;
    }

    function UseTool($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'cancel') {
            game::$instance->addPending($this->player_id, "NormalTurnStep3", $parg2);
        } else {
            $type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_id = '{$parg1}'");
            $nbre_pot_market = count(self::getObjectListFromDB("SELECT card_id FROM pot WHERE card_location = 'market'", true));

            if ($type == 61) {

                $pot_origin = 0;
                $explode = explode('_', $varg1);
                $before_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$explode[1]}'");
                self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +3 WHERE card_type = '{$explode[1]}'");
                $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$explode[1]}'");
                $max_verdoiement = game::$instance->_PLANT_CARDS[$explode[1]]['verdancy'];

                if ($total_verdoiement < $max_verdoiement) {
                    $valeur_pot = -1;
                    $delta = 3;
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

                    self::DbQuery("UPDATE pot SET card_location = {$this->player_id}, card_location_arg = $explode[1] WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$explode[1]}'");
                }

                game::$instance->notifyAllPlayers(
                    'addVerdancySolo',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'plant_type' => $explode[1],
                        'verdancy_added' => $delta,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => false,
                        'pot_origin' => $pot_origin

                    )
                );

                $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_id = '{$parg1}'");

                game::$instance->tile->moveCard($parg1, 'discard');

                game::$instance->notifyAllPlayers(
                    'discardTile',
                    '',
                    array(
                        'player_id' => $this->player_id,
                        'tile' => $info_tile,


                    )
                );
                game::$instance->incStat(1, 'fertilizer_used', $this->player_id);
                game::$instance->addPending($this->player_id, "NormalTurnStep3", $parg2);
            }

            if ($type == 62) {
                $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_id = '{$parg1}'");

                game::$instance->tile->moveCard($parg1, 'discard');

                game::$instance->notifyAllPlayers(
                    'discardTile',
                    '',
                    array(
                        'player_id' => $this->player_id,
                        'tile' => $info_tile,


                    )
                );

                game::$instance->incStat(1, 'hand_trowel_used', $this->player_id);
                game::$instance->addPending($this->player_id, "NormalTurnStep3", $parg2);
            }

            if ($type == 63) {
                $explode = explode('_', $varg1);
                $position_room = self::getUniqueValueFromDB("SELECT card_location_arg FROM room WHERE card_type='{$explode[1]}'");
                $all_pots = self::getObjectListFromDB("SELECT card_location_arg FROM pot WHERE card_location = '{$this->player_id}'", true);

                $tests = [$position_room + 1, $position_room - 1, $position_room + 10, $position_room - 10];

                foreach ($tests as $test) {
                    $pot_origin = 0;
                    $type_plant = self::getUniqueValueFromDB("SELECT card_type FROM plant WHERE card_location ='{$this->player_id}' AND card_location_arg='{$test}'");
                    if (($type_plant != null) && (!in_array($type_plant, $all_pots))) {


                        self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$type_plant}'");
                        $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$type_plant}'");
                        $max_verdoiement = game::$instance->_PLANT_CARDS[$type_plant]['verdancy'];

                        if ($total_verdoiement < $max_verdoiement) {
                            $valeur_pot = -1;
                        } else {

                            if ($nbre_pot_market == 4) {
                                $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'market' AND card_location_arg =4");
                                $pot_origin = 'market_pot_4';
                            } else {
                                $pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'discard' ORDER BY card_type ASC LIMIT 1");
                                $pot_origin = 'pot_discard_' . $pot_id;
                            }


                            $valeur_pot = self::getUniqueValueFromDB("SELECT card_type FROM pot WHERE card_id = '{$pot_id}'");

                            self::DbQuery("UPDATE pot SET card_location = {$this->player_id}, card_location_arg = $explode[1] WHERE card_id = '{$pot_id}'");

                            self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$type_plant}'");
                        }

                        game::$instance->notifyAllPlayers(
                            'addVerdancySolo',
                            '',
                            array(
                                'player_name' => $this->player_name,
                                'player_id' => $this->player_id,
                                'plant_type' => $type_plant,
                                'verdancy_added' => 1,
                                'pot_value' => $valeur_pot,
                                'thumbs_used' => false,
                                'pot_origin' => $pot_origin
                            )
                        );
                    }
                }

                $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_id = '{$parg1}'");

                game::$instance->tile->moveCard($parg1, 'discard');

                game::$instance->notifyAllPlayers(
                    'discardTile',
                    '',
                    array(
                        'player_id' => $this->player_id,
                        'tile' => $info_tile,


                    )
                );

                game::$instance->incStat(1, 'watering_can', $this->player_id);

                game::$instance->addPending($this->player_id, "NormalTurnStep3", $parg2);
            }
        }
    }


    function argChooseReserve($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');
        $ret['titleyou'] = clienttranslate('${you} must choose the item/tool ​​to keep in reserve');

        $tile_market_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");
        $tile_market_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");

        $tile_reserve_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");
        $tile_reserve_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");




        if (($tile_market_type != null) && ($tile_reserve_type != null)) {

            $ret["selectable"][] = 'tile_' . $tile_market_id;
            $ret["selectable"][] = 'tile_' . $tile_reserve_id;

            $ret['buttons'][] = 'tilebt_' . $tile_market_type;
            $ret['buttons'][] = 'tilebt_' . $tile_reserve_type;

            $ret['buttons'][] = 'cancel';
        }






        return $ret;
    }

    function ChooseReserve($parg1, $parg2, $varg1, $varg2)
    {

        if ($varg1 == 'cancel') {
            game::$instance->addPending($this->player_id, "NormalTurnStep3", $parg1);
        } else {
            $tile_market_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");
            $tile_market_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='market' AND card_location_arg = '{$parg1}'");

            $tile_reserve_type = self::getUniqueValueFromDB("SELECT card_type FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");
            $tile_reserve_id = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");

            if (($varg1 == "tile_" . $tile_market_id) || ($varg1 == "tilebt_" . $tile_market_type)) {
                $info_tile_reserve = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_location='{$this->player_id}' AND card_location_arg = 99");

                game::$instance->tile->moveCard($tile_reserve_id, 'discard');

                game::$instance->notifyAllPlayers(
                    'discardTile',
                    '',
                    array(
                        'player_id' => $this->player_id,
                        'tile' => $info_tile_reserve,


                    )
                );





                $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_id='{$tile_market_id}'");
                game::$instance->tile->moveCard($tile_market_id, $this->player_id, 99);

                game::$instance->notifyAllPlayers(
                    'moveTileToReserve',
                    '',
                    array(
                        'player_id' => $this->player_id,
                        'tile' => $info_tile,

                    )
                );
            } elseif (($varg1 == "tile_" . $tile_reserve_id) || ($varg1 == "tilebt_" . $tile_reserve_type)) {
                $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_location = 'market' AND card_location_arg = '{$parg1}'");

                game::$instance->tile->moveCard($tile_market_id, 'discard');

                game::$instance->notifyAllPlayers(
                    'discardTile',
                    '',
                    array(
                        'player_id' => $this->player_id,
                        'tile' => $info_tile,


                    )
                );
            } else {


                if ($tile_market_id != null) {
                    $info_tile = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM tile WHERE card_id='{$tile_market_id}'");

                    game::$instance->tile->moveCard($tile_market_id, $this->player_id, 99);

                    game::$instance->notifyAllPlayers(
                        'moveTileToReserve',
                        '',
                        array(
                            'player_id' => $this->player_id,
                            'tile' => $info_tile,

                        )
                    );
                }
            }


            game::$instance->addPending($this->player_id, "RefillMarket");
        }
    }

    function argFinalTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');

        $thumb = self::getUniqueValueFromDB("SELECT player_thumb FROM player WHERE player_id={$this->player_id}");
        $all_plants = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location = '{$this->player_id}'", true);
        $all_pots = self::getObjectListFromDB("SELECT card_location_arg FROM pot WHERE card_location = '{$this->player_id}'", true);
        $plants_without_pots = array_diff($all_plants, $all_pots);

        if ((!empty($plants_without_pots)) && ($thumb >= 2)) {
            $ret['titleyou'] = clienttranslate('${you} can use');
            $ret['buttons'][] = 'thumb';
            $ret['buttons'][] = 'pass';
        }



        return $ret;
    }

    function FinalTurn($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == "thumb") {
            game::$instance->addPending($this->player_id, "UseThumb", 2, "FinalTurn");
        } else {
            game::$instance->addPending($this->player_id, "RefillMarket");
        }
    }


    function argRefillMarket($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('');
        $ret['titleyou'] = clienttranslate('');


        return $ret;
    }

    function RefillMarket($parg1, $parg2, $varg1, $varg2)
    {
        // ajout d'un thumb sur l'opposite card utilsée pendant le tour

        $tests = [1, 2, 3, 4];
        $plant_market = self::getObjectListFromDB("SELECT card_location_arg FROM plant WHERE card_location = 'market'", true);
        $room_market = self::getObjectListFromDB("SELECT card_location_arg FROM room WHERE card_location = 'market'", true);

        foreach ($tests as $test) {
            if (!in_array($test, $plant_market)) {

                $info_opposite_card = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM room WHERE card_location = 'market' AND card_location_arg = '{$test}'");
                $info_opposite_card['genre'] = 'room';
                self::DbQuery("UPDATE room set card_thumb = card_thumb +1 WHERE card_location = 'market' AND card_location_arg = '{$test}'");
            }

            if (!in_array($test, $room_market)) {

                $info_opposite_card = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_location = 'market' AND card_location_arg = '{$test}'");
                $info_opposite_card['genre'] = 'plant';
                self::DbQuery("UPDATE plant set card_thumb = card_thumb +1 WHERE card_location = 'market' AND card_location_arg = '{$test}'");
            }
        }



        // discard dernière colonne du market et decalage/suppression des pouces

        $plant_discard = self::getUniqueValueFromDB("SELECT card_id FROM plant WHERE card_location ='market' AND card_location_arg = 4");
        if ($plant_discard != null) {
            $valeur_thumb = intval(self::getUniqueValueFromDB("SELECT card_thumb FROM plant WHERE card_location ='market' AND card_location_arg = 4"));
            game::$instance->plant->moveCard($plant_discard, 'discard');

            $plant_dispo = self::getUniqueValueFromDB("SELECT card_id FROM plant WHERE card_location = 'market' ORDER BY card_location_arg DESC LIMIT 1");
            self::DbQuery("UPDATE plant set card_thumb = card_thumb + $valeur_thumb WHERE card_id = {$plant_dispo}");
            $new_thumb = self::getUniqueValueFromDB("SELECT card_thumb FROM plant WHERE card_id ='{$plant_dispo}'");
            if ($new_thumb >= 3) {
                self::DbQuery("UPDATE plant set card_thumb = 0 WHERE card_id = {$plant_dispo}");
            }
        }

        $room_discard = self::getUniqueValueFromDB("SELECT card_id FROM room WHERE card_location ='market' AND card_location_arg = 4");
        if ($room_discard != null) {
            $valeur_thumb = intval(self::getUniqueValueFromDB("SELECT card_thumb FROM room WHERE card_location ='market' AND card_location_arg = 4"));
            game::$instance->room->moveCard($room_discard, 'discard');

            $room_dispo = self::getUniqueValueFromDB("SELECT card_id FROM room WHERE card_location = 'market' ORDER BY card_location_arg DESC LIMIT 1");
            self::DbQuery("UPDATE room set card_thumb = card_thumb + $valeur_thumb WHERE card_id = {$room_dispo}");
            $new_thumb = self::getUniqueValueFromDB("SELECT card_thumb FROM room WHERE card_id ='{$room_dispo}'");
            if ($new_thumb >= 3) {
                self::DbQuery("UPDATE room set card_thumb = 0 WHERE card_id = {$room_dispo}");
            }
        }

        $tile_discard = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location ='market' AND card_location_arg = 4");
        if ($tile_discard != null) {
            game::$instance->tile->moveCard($tile_discard, 'discard');
        }

        $pot_discard = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location ='market' AND card_location_arg = 4");
        if ($pot_discard != null) {
            self::DbQuery("UPDATE pot set card_location = 'discard' WHERE card_id = {$pot_discard}");
        }





        /// Decalage vers la droite de tout le  market

        for ($i = 3; $i >= 1; $i--) {
            $plant_decal = self::getUniqueValueFromDB("SELECT card_id FROM plant WHERE card_location ='market' AND card_location_arg = '{$i}'");
            if ($plant_decal != null) {
                game::$instance->plant->moveCard($plant_decal, 'market', $i + 1);
            }
            $room_decal = self::getUniqueValueFromDB("SELECT card_id FROM room WHERE card_location ='market' AND card_location_arg = '{$i}'");
            if ($room_decal != null) {
                game::$instance->room->moveCard($room_decal, 'market', $i + 1);
            }
            $tile_decal = self::getUniqueValueFromDB("SELECT card_id FROM tile WHERE card_location ='market' AND card_location_arg = '{$i}'");
            if ($tile_decal != null) {
                game::$instance->tile->moveCard($tile_decal, 'market', $i + 1);
            }
            $pot_decal = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location ='market' AND card_location_arg = '{$i}'");
            if ($pot_decal != null) {
                self::DbQuery("UPDATE pot set card_location_arg = card_location_arg +1 WHERE card_id = {$pot_decal}");
            }
        }




        /// remplissage market
        $tests = [1, 2, 3, 4];
        $plant_market = self::getObjectListFromDB("SELECT card_location_arg FROM plant WHERE card_location = 'market'", true);
        $room_market = self::getObjectListFromDB("SELECT card_location_arg FROM room WHERE card_location = 'market'", true);
        $tile_market = self::getObjectListFromDB("SELECT card_location_arg FROM tile WHERE card_location = 'market'", true);
        $tiles_pick = [];
        $cards_pick = [];


        foreach ($tests as $test) {
            if (!in_array($test, $plant_market)) {
                $card_pick = game::$instance->plant->pickCardForLocation('deck', 'market', $test);
                $card_pick['genre'] = 'plant';
                $cards_pick[] = $card_pick;
            }

            if (!in_array($test, $room_market)) {
                $card_pick = game::$instance->room->pickCardForLocation('deck', 'market', $test);
                $card_pick['genre'] = 'room';
                $cards_pick[] = $card_pick;
            }

            if (!in_array($test, $tile_market)) {
                $tile_pick = game::$instance->tile->pickCardForLocation('deck', 'market', $test);
                $tiles_pick[] = $tile_pick;
            }
        }

        $new_pot_id = self::getUniqueValueFromDB("SELECT card_id FROM pot WHERE card_location = 'deck' ORDER BY card_type DESC LIMIT 1");

        $new_pot = self::getObjectFromDb("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg FROM pot WHERE card_id='{$new_pot_id}'");

        self::DbQuery("UPDATE pot SET card_location = 'market', card_location_arg = 1 WHERE card_id = '{$new_pot_id}'");


        game::$instance->notifyAllPlayers(
            'refillMarketSolo',
            '',
            array(

                'card_thumb' => $info_opposite_card,
                'cards_pick' => $cards_pick,
                'tiles_pick' => $tiles_pick,
                'new_pot' => $new_pot

            )
        );


        game::$instance->giveExtraTime($this->player_id);
        game::$instance->updateNbTurns();
        game::$instance->addPendingFirst($this->player_id, "NormalTurn");
    }


    ////////////// THUMBS ///////////////

    function argUseThumb($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["selectablethumb"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();

        $ret['title'] = clienttranslate('${actplayer} must choose a Thumb action');

        $plants_market = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location = 'market'", true);
        $rooms_market = self::getObjectListFromDB("SELECT card_type FROM room WHERE card_location = 'market'", true);
        $tiles_market = self::getObjectListFromDB("SELECT card_id FROM tile WHERE card_location = 'market'", true);

        if ($parg1 == 1) {
            $ret['titleyou'] = clienttranslate('${you} must choose a Thumb action');

            // ACTION A

            foreach ($plants_market as $plant) {
                $ret["selectablethumb"][] = 'plant_' . $plant;
            }

            foreach ($rooms_market as $room) {
                $ret["selectablethumb"][] = 'room_' . $room;
            }

            foreach ($tiles_market as $tile) {
                $ret["selectablethumb"][] = 'tile_' . $tile;
            }

            $ret['buttons'][] = 'validate_tokens';

            // ACTION B
            $ret['buttons'][] = 'validate_cards';

            // ACTION C
            $ret['buttons'][] = 'validate_mixed';
        }

        // ACTION D
        $all_plants = self::getObjectListFromDB("SELECT card_type FROM plant WHERE card_location = '{$this->player_id}'", true);
        $all_pots = self::getObjectListFromDB("SELECT card_location_arg FROM pot WHERE card_location = '{$this->player_id}'", true);
        $plants_without_pots = array_diff($all_plants, $all_pots);

        if (!empty($plants_without_pots)) {
            foreach ($plants_without_pots as $plant) {
                if ($parg1 == 1) {
                    $ret["selectablethumb"][] = 'plant_' . $plant;
                }
                if ($parg1 == 2) {
                    $ret['titleyou'] = clienttranslate('${you} must choose a plant (+1 Verdancy)');
                    $ret["selectable"][] = 'plant_' . $plant;
                }
            }

            if ($parg1 == 1) {
                $ret['buttons'][] = 'validate_verdancy';
            }
        }


        $ret['buttons'][] = 'cancel';



        return $ret;
    }

    function UseThumb($parg1, $parg2, $varg1, $varg2)
    {


        if ($parg1 == 1) {
            game::$instance->addPending($this->player_id, "NormalTurn");
        }

        if ($parg1 == 2) {
            if ($varg1 != "cancel") {

                $pot_origin = 0;
                $explode = explode('_', $varg1);
                $before_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$explode[1]}'");
                self::DbQuery("UPDATE plant set card_type_arg = card_type_arg +1 WHERE card_type = '{$explode[1]}'");
                $total_verdoiement = self::getUniqueValueFromDB("SELECT card_type_arg FROM plant WHERE card_type='{$explode[1]}'");
                $max_verdoiement = game::$instance->_PLANT_CARDS[$explode[1]]['verdancy'];

                if ($total_verdoiement < $max_verdoiement) {
                    $valeur_pot = -1;
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

                    self::DbQuery("UPDATE pot SET card_location = {$this->player_id}, card_location_arg = $explode[1] WHERE card_id = '{$pot_id}'");

                    self::DbQuery("UPDATE plant SET card_type_arg = 0 WHERE card_type = '{$explode[1]}'");
                }

                game::$instance->notifyAllPlayers(
                    'addVerdancySolo',
                    '',
                    array(
                        'player_name' => $this->player_name,
                        'player_id' => $this->player_id,
                        'plant_type' => $explode[1],
                        'verdancy_added' => 1,
                        'pot_value' => $valeur_pot,
                        'thumbs_used' => true,
                        'pot_origin' => $pot_origin
                    )
                );

                self::DbQuery("UPDATE player set player_thumb = player_thumb - 2 WHERE player_id = {$this->player_id}");
            }

            if ($parg2 == "FinalTurn") {
                game::$instance->addPending($this->player_id, "FinalTurn");
            } else {
                $explode = explode('_', $parg2);
                game::$instance->addPending($this->player_id, "NormalTurnStep3", $explode[1]);
            }
        }
    }


    function argNormalTurnStep2Thumb($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');
        $ret['titleyou'] = clienttranslate('${you} must must choose a location');

        $ret["selected"][] = $parg1;
        $ret["selected"][] = $parg2;

        $ret["card"][] = $parg1;

        $ret["selectable"] = game::$instance->PossiblePosition($this->player_id, $parg1);

        $ret['buttons'][] = 'cancel';

        return $ret;
    }

    function NormalTurnStep2Thumb($parg1, $parg2, $varg1, $varg2)
    {
        if ($varg1 == 'cancel') {
            game::$instance->addPending($this->player_id, "NormalTurn");
        } else {
            $explode_market = explode('_', $parg1);
            $explode_tile = explode('_', $parg2);
            $explode_position = explode('_', $varg1);

            $place_market = self::getUniqueValueFromDB("SELECT card_location_arg FROM tile WHERE card_id = '{$explode_tile[1]}'");

            if ($explode_market[0] == 'plant') {



                $card_id = self::getUniqueValueFromDB("SELECT card_id FROM plant WHERE card_type = '{$explode_market[1]}'");
                $card_type = $explode_market[1];

                $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_id = {$card_id}");
                $card_before['genre'] = 'plant';

                $thumb = self::getUniqueValueFromDB("SELECT card_thumb FROM plant WHERE card_type = '{$explode_market[1]}'");
                self::DbQuery("UPDATE plant set card_thumb = 0 WHERE card_id = '{$card_id}'");
                self::DbQuery("UPDATE player set player_thumb = player_thumb + $thumb WHERE player_id = '{$this->player_id}'");
                game::$instance->plant->moveCard($card_id, $this->player_id, $explode_position[1]);

                $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM plant WHERE card_id = {$card_id}");


                game::$instance->notifyAllPlayers(
                    'moveCardToHouse',
                    clienttranslate('${player_name} places a plant in the house'),
                    array(
                        'player_name' => $this->player_name,
                        'card_before' => $card_before,
                        'card_after' => $card_after


                    )
                );
            }
            if ($explode_market[0] == 'room') {


                $card_id = self::getUniqueValueFromDB("SELECT card_id FROM room WHERE card_type = '{$explode_market[1]}'");
                $card_type = $explode_market[1];

                $card_before = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM room WHERE card_id = {$card_id}");
                $card_before['genre'] = 'room';

                $thumb = self::getUniqueValueFromDB("SELECT card_thumb FROM room WHERE card_type = '{$explode_market[1]}'");
                self::DbQuery("UPDATE room set card_thumb = 0 WHERE card_id = '{$card_id}'");
                self::DbQuery("UPDATE player set player_thumb = player_thumb + $thumb WHERE player_id = '{$this->player_id}'");
                game::$instance->room->moveCard($card_id, $this->player_id, $explode_position[1]);

                $card_after = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_location location, card_location_arg location_arg, card_thumb thumb FROM room WHERE card_id = {$card_id}");

                game::$instance->notifyAllPlayers(
                    'moveCardToHouse',
                    clienttranslate('${player_name} places a room in the house'),
                    array(
                        'player_name' => $this->player_name,
                        'card_before' => $card_before,
                        'card_after' => $card_after


                    )
                );
            }

            game::$instance->TestVerdoyanceSolo($this->player_id, $explode_market[0], $card_type, $explode_position[1]);

            self::DbQuery("UPDATE player set player_thumb = player_thumb - 2 WHERE player_id = {$this->player_id}");

            game::$instance->notifyAllPlayers(
                'useThumbs',
                '',
                array(
                    'player_name' => $this->player_name,
                    'player_id' => $this->player_id,

                )
            );

            game::$instance->addPending($this->player_id, "NormalTurnStep3", $place_market);
        }
    }


    /// END OF GAME

    function argEndOfGame($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selectablemulti"] = array();
        $ret["card"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must take an action');
        $ret['titleyou'] = clienttranslate('End of Game');

        $ret['buttons'][] = 'pass';


        return $ret;
    }

    function EndOfGame($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->giveExtraTime($this->player_id);
        game::$instance->addPendingFirst($this->player_id, "NormalTurn");
    }
}
