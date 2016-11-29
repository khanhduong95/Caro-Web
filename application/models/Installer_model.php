<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Installer_model extends CI_Model
{
        public function __construct()
        {
                parent::__construct();
        }

        public function install()
        {

                $this->db->query("CREATE TABLE IF NOT EXISTS `".CARO_DB_PREFIX."players`(
                        `id` int(255) NOT NULL AUTO_INCREMENT,
                        `firstName` varchar(256) NOT NULL,
                        `lastName` varchar(256) NOT NULL,
                        `email` varchar(256) NOT NULL,
                        `password` varchar(256) NOT NULL,
                        `avatar` varchar(256),
                        `dob` date NOT NULL,
                        `played` int(255) DEFAULT '0' NOT NULL,
                        `wins` int(255) DEFAULT '0' NOT NULL,
                        `draws` int(255) DEFAULT '0' NOT NULL,
                        `points` int(255) DEFAULT '0' NOT NULL,
                        `money` int(255) DEFAULT '10000' NOT NULL,
                        PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

                $this->db->query("CREATE TABLE IF NOT EXISTS `".CARO_DB_PREFIX."games` (
                        `gameId` tinyint(1) NOT NULL AUTO_INCREMENT,
                        `player1Id` int(255) NOT NULL,
                        `player2Id` int(255),
                        `player1Ready` tinyint(1) DEFAULT '0' NOT NULL,
                        `player2Ready` tinyint(1) DEFAULT '0' NOT NULL,
                        `player1Wins` int(10) DEFAULT '0' NOT NULL,
                        `player2Wins` int(10) DEFAULT '0' NOT NULL,
                        `draws` int(10) DEFAULT '0' NOT NULL,
                        `temp_turn` tinyint(1) DEFAULT '1' NOT NULL,
                        `init_turn` tinyint(1) DEFAULT '1' NOT NULL,
                        `moves` varchar(256) NOT NULL,
                        `countdown` tinyint(2) DEFAULT '".CARO_COUNTDOWN."' NOT NULL,
                        `timeout` tinyint(2) DEFAULT '".CARO_TIMEOUT."' NOT NULL,
                        `status` tinyint(1) NOT NULL,
                        `startTime` datetime,
                        `bet` int(10) DEFAULT '0' NOT NULL,
                        PRIMARY KEY (`gameId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

                if (CARO_SAVE_GAME_HISTORY)
                        $this->db->query("CREATE TABLE IF NOT EXISTS `".CARO_DB_PREFIX."game_records` (
                                `recordId` tinyint(1) NOT NULL AUTO_INCREMENT,
                                `player1Id` int(255) NOT NULL,
                                `player2Id` int(255) NOT NULL,
                                `winner` tinyint(1) NOT NULL,
                                `turn` tinyint(1) NOT NULL,
                                `moves` varchar(256) NOT NULL,
                                `bet` int(255) DEFAULT '0' NOT NULL,
                                `startTime` datetime NOT NULL,
                                `endTime` datetime NOT NULL,
                                PRIMARY KEY (`recordId`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }

        public function uninstall(){
                $this->db->query("DROP TABLE IF EXISTS `".CARO_DB_PREFIX."players`;");
                $this->db->query("DROP TABLE IF EXISTS `".CARO_DB_PREFIX."games`;");
                $this->db->query("DROP TABLE IF EXISTS `".CARO_DB_PREFIX."game_records`;");
        }

}
