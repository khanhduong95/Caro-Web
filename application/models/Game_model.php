<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Game_model extends CI_Model
{
        public function __construct()
        {
                parent::__construct();
        } 

        public function createGame($playerId){
                if (!$this->checkPlayerGameExists($playerId)){
                        $matrix = array();
                        for ($i = 0; $i < 15; $i++){
                                $matrix[$i] = array();
                                for ($j = 0; $j < 15; $j++)
                                        $matrix[$i][$j] = 0;
                        }
                        $this->db->insert(CARO_DB_PREFIX.'games', array('player1Id' => $playerId, 'moves' => json_encode($matrix), 'status' => 0));
                        if ($this->db->insert_id())
                                return $this->db->insert_id();
                }
                return 0;
        }

        public function joinGame($gameId, $playerId){
                if (!$this->checkPlayerGameExists($playerId)){
                        if ($this->db->update(CARO_DB_PREFIX.'games', array('player2Id' => $playerId, 'status' => 1), array('gameId' => $gameId)))
                                return $gameId;
                }
                return 0;
        }

        public function readyGame($gameId, $playerId){
                if (!$this->checkPlayerGameExists($playerId)){
                        $this->db->select('player1Id');
                        if ($this->db->get_where(CARO_DB_PREFIX.'games', array('player1Id' => $playerId, 'player1Ready' => 0))->num_rows() > 0){
                                if ($this->db->update(CARO_DB_PREFIX.'games', array('player1Ready' => 1), array('player1Id' => $playerId))){
                                        return $gameId;
                                }
                                else
                                        return 0;
                        }
                        $this->db->select('player2Id');
                        if ($this->db->get_where(CARO_DB_PREFIX.'games', array('player2Id' => $playerId, 'player2Ready' => 0))->num_rows() > 0){
                                if ($this->db->update(CARO_DB_PREFIX.'games', array('player2Ready' => 1), array('player2Id' => $playerId))){
                                        return $gameId;
                                }
                        }
                        return 0;
                }
                return 0;
        }

        public function startGame($gameId){
                $this->db->select('gameId');
                if ($this->db->get_where(CARO_DB_PREFIX.'games', array('gameId' => $gameId, 'player1Ready' => 1, 'player2Ready' => 1, 'status' => 1))->num_rows() > 0){
                        if ($this->db->update(CARO_DB_PREFIX.'games', array('status' => 2, 'init_turn' => 1, 'temp_turn' => 1), array('gameId' => $gameId)))
                                return 1;
                }
                return 0;
        }

        public function quitGame($gameId, $playerId){
                if ($this->checkPlayerGameExists($playerId)){
                        $this->db->select('player2Id');
                        $query = $this->db->get_where(CARO_DB_PREFIX.'games', array('player1Id' => $playerId));
                        if ($query->num_rows() > 0){
                                $player2Id = $query->row_array()['player2Id'];
                                if ($this->db->update(CARO_DB_PREFIX.'games', array('player1Id' => $player2Id, 'player2Id' => NULL,'player1Ready' => 0, 'player2Ready' => 0), array('player1Id' => $playerId)))
                                        return 1;

                                else
                                        return 0;
                        }
                        $this->db->select('player2Id');
                        if ($this->db->get_where(CARO_DB_PREFIX.'games', array('player2Id' => $playerId))->num_rows() > 0){
                                if ($this->db->update(CARO_DB_PREFIX.'games', array('player2Id' => NULL, 'player1Ready' => 0, 'player2Ready' => 0), array('player2Id' => $playerId)))
                                        return 1;

                        }
                        return 0;
                }
                return 0;               
        }

        public function getGameData($gameId, $items = ""){
                if ($items != "")
                        $this->db->select('*');
                else
                        $this->db->select($items);
                $query = $this->db->get_where(CARO_DB_PREFIX.'games', array('gameId' => $gameId));
                if ($query->num_rows() > 0)
                        return $query->row_array();
                return false;
        }

        public function updateGameData($gameData = array(), $gameId = 0){
                if ($gameId > 0){
                        if ($this->db->update(CARO_DB_PREFIX.'games', $gameData, array('id' => $gameId)))
                                return $gameId;
                }
                else {
                        $this->db->insert(CARO_DB_PREFIX.'games', $gameData);
                        if ($this->db->insert_id())
                                return $this->db->insert_id();
                }
                return 0;
        }

        public function checkPlayerGameExists($playerId){
                $this->db->select('player1Id');
                if ($this->db->get_where(CARO_DB_PREFIX.'games', array('player1Id' => $playerId))->num_rows() > 0)
                        return true;

                $this->db->select('player2Id');
                if ($this->db->get_where(CARO_DB_PREFIX.'games', array('player2Id' => $playerId))->num_rows() > 0)
                        return true;

                return false;

        }

        public function timeout($gameId){
                $this->db->select('timeout');
                $query = $this->db->get_where(CARO_DB_PREFIX.'games', array('gameId' => $gameId));
                if ($query->num_rows() > 0)
                {
                        $timeout = $query->row_array()['timeout'];
                        if ($this->db->update(CARO_DB_PREFIX.'games', array('timeout' => $timeout - 1), array('gameId' => $gameId)))
                                return $timeout - 1;
                }
                return 0;

        }

        public function countdown($gameId){
                $this->db->select('countdown');
                $query = $this->db->get_where(CARO_DB_PREFIX.'games', array('gameId' => $gameId));
                if ($query->num_rows() > 0)
                {
                        $countdown = $query->row_array()['countdown'];
                        if ($this->db->update(CARO_DB_PREFIX.'games', array('countdown' => $countdown - 1), array('gameId' => $gameId)))
                                return $countdown - 1;
                }
                return 0;

        }
}
