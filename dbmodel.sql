-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- verdant implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Priol> <camertwo@hotmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----
-- dbmodel.sql
CREATE TABLE IF NOT EXISTS `pending` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `player_id` int(10) NULL,
  `function` varchar(50) NULL,
  `target` varchar(50) NULL,
  `arg` varchar(50) NULL,
  `arg2` varchar(50) NULL,
  `arg3` varchar(50) NULL,
  `arg4` varchar(50) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1000;
CREATE TABLE IF NOT EXISTS `plant` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` int(11) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(50) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  `card_thumb` int(2) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
--  `plant`
--   `card_id` 
--   `card_type`         numéro de carte
--   `card_type_arg`     nbre verdoyant
--   `card_location`     deck, player_id, market
--   `card_location_arg` pour maison(player_id): dizaine=ligne unité=colonne / pour market: position / 99 si dans la main en first turn
CREATE TABLE IF NOT EXISTS `room` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` int(11) NOT NULL,
  `card_type_arg` int(11) NOT NULL DEFAULT 0,
  `card_location` varchar(50) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  `card_thumb` int(2) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
--  `room` 
--   `card_id` 
--   `card_type`         numéro de carte
--   `card_type_arg`     non utilisé
--   `card_location`     deck, player_id, market
--   `card_location_arg` pour maison(player_id): dizaine=ligne unité=colonne / pour market: position
CREATE TABLE IF NOT EXISTS `tile` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` int(11) NOT NULL,
  `card_type_arg` int(11) NOT NULL DEFAULT 0,
  `card_location` varchar(50) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
--  `tile`
--   `card_id` 
--   `card_type`         dizaine=couleur ... 6 est vert / unité = type
--   `card_type_arg`     non utilisé
--   `card_location`     deck, player_id, market
--   `card_location_arg` pour maison(player_id): numero de carte room / pour market: position / 99 si sur stockage
CREATE TABLE IF NOT EXISTS `pot` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` int(2) unsigned NOT NULL DEFAULT 0,
  `card_type_arg` int(11) NOT NULL DEFAULT 0,
  `card_location` varchar(50) NOT NULL,
  `card_location_arg` int(2) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
--  `pot` (
--   `card_id` 
--   `card_type`         score de 3 à 0
--   `card_type_arg`     non utilisé
--   `card_location`     deck, player_id, market(solo)
--   `card_location_arg` pour maison(player_id): numero de carte plant / pour market(en solo): position
ALTER TABLE `player`
ADD `player_thumb` int(2) unsigned NOT NULL DEFAULT 0;