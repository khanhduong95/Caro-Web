<?php
/*
 *   Copyright (C) 2016 Dang Duong
 *
 *   This file is part of Free Caro Online.
 *
 *   Free Caro Online is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU Affero General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Free Caro Online is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public License
 *   along with Free Caro Online.  If not, see <http://www.gnu.org/licenses/>.
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

Class Game_model extends CI_Model
{
        public function __construct()
        {
                parent::__construct();
        } 

        public function createGame($playerId){
                if ($this->checkPlayerGameExists($playerId) <= 0){

                        $this->db->insert('{CARO_PREFIX}games', array('player1Id' => $playerId));
                        if ($this->db->insert_id())
                                return $this->db->insert_id();
                }
                return 0;
        }

        public function joinGame($gameId, $playerId){
                if ($this->checkPlayerGameExists($playerId) <= 0){
                        if ($this->db->update('{CARO_PREFIX}games', array('player2Id' => $playerId, 'status' => 1), array('gameId' => $gameId, 'player2Id' => 0)))
                                return $gameId;
                }
                return 0;
        }

        public function readyGame($gameId, $playerId){
                if ($this->checkPlayerGameExists($playerId) <= 0){
			$this->db->update('{CARO_PREFIX}games', array('player1Ready' => 1), array('gameId' => $gameId, 'player1Id' => $playerId, 'status' => 1));
			if ($this->db->affected_rows() > 0)
				return $gameId;
                                
			$this->db->update('{CARO_PREFIX}games', array('player2Ready' => 1), array('gameId' => $gameId, 'player2Id' => $playerId, 'status' => 1));
			if ($this->db->affected_rows() > 0)
				return $gameId;
                }
                return 0;
        }

        public function startGame($gameId){
		$matrix = array();
		for ($i = 0; $i < 15; $i++){
			$matrix[$i] = array();
			for ($j = 0; $j < 15; $j++)
				$matrix[$i][$j] = 0;
		}
                $this->db->update('{CARO_PREFIX}games', array('status' => 2, 'moves' => json_encode($matrix)), array('gameId' => $gameId, 'player1Ready' => 1, 'player2Ready' => 1, 'status' => 1));
        }

        public function removePlayer($gameId, $playerId, $otherPlayerId = 0){
		$this->db->update('{CARO_PREFIX}games', array('player2Id' => 0, 'player1Ready' => 0, 'player2Ready' => 0, 'turn' => 1, 'status' => 0), array('gameId' => $gameId, 'player2Id' => $playerId));
		if ($this->db->affected_rows() > 0)
			exit();
		if ($otherPlayerId <= 0){
			$this->db->select('player2Id');
			$query = $this->db->get_where('{CARO_PREFIX}games', array('gameId' => $gameId, 'player1Id' => $playerId));
			if ($query->num_rows() > 0)
				$otherPlayerId = $query->row_array()['player2Id'];
		}
		if ($otherPlayerId <= 0)
			$this->db->delete('{CARO_PREFIX}games', array('gameId' => $gameId, 'player1Id' => $playerId, 'player2Id' => 0));
		else
			$this->db->update('{CARO_PREFIX}games', array('player1Id' => $otherPlayerId, 'player2Id' => 0, 'turn' => 1, 'status' => 0), array('gameId' => $gameId, 'player1Id' => $playerId, 'player2Id' => $otherPlayerId));
        }

        public function getGameData($gameId, $items = "*"){
                $this->db->select($items);
                $query = $this->db->get_where('{CARO_PREFIX}games', array('gameId' => $gameId));
                if ($query->num_rows() > 0)
                        return $query->row_array();
                return false;
        }

        public function getGameDataByPlayer($playerId){
                $this->db->select('player1Id, moves, turn, status');
                $this->db->limit(1);
                $this->db->where(array('player1Id' => $playerId));
                $this->db->or_where(array('player2Id' => $playerId));
                $query = $this->db->get('{CARO_PREFIX}games');
                if ($query->num_rows() > 0){
                        $gameData = $query->row_array();
                        return array(
				     'playerOrder' => ($gameData['player1Id'] == $playerId) ? 1 : 2,
				     'turn' => $gameData['turn'],
				     'moves' => $gameData['moves'],
				     'status' => $gameData['status']
				     );
                }
                return false;
        }

        public function updateMove($gameId, $playerId = 0, $moveX = 0, $moveY = 0){

		$this->db->select('player1Id, player2Id, startTime, lastMove, moves, turn');
		$query = $this->db->get_where('{CARO_PREFIX}games', array('gameId' => $gameId, 'status' => 2));
		if ($query->num_rows() <= 0)
			return false;
		$gameData = $query->row_array();
		$turn = $gameData['turn'];
		if ($playerId <= 0)
			$playerId = $gameData['player'.$turn.'Id'];

		if ($this->checkPlayerTimeout($gameId, $gameData['player1Id'], $gameData['player2Id']) > 0){
			return false;
		}

                $where = array(
			       'gameId' => $gameId,
			       'turn' => $turn,
			       'moves' => $movesJson,
			       'status' => $status
			       );

                $moves = json_decode($gameData['moves'], true);

                if ($moves[$moveX][$moveY] != 0 || $moveX < 0 || $moveX >= 15 || $moveY < 0 || $moveY >= 15){
                        $autoMove = $this->checkEmpty($moves);
                        if ($autoMove){
                                $moveX = $autoMove['x'];
                                $moveY = $autoMove['y'];
                        }
                        else
                                return false;
                }

                $moves[$moveX][$moveY] = $turn;
                $newTurn = ($turn == 1) ? 2 : 1;

                $data = array(
			      'lastMove' => date("Y-m-d H:i:s"),
			      'moves' => json_encode($moves),
			      'turn' => $newTurn
			      );
		$winner = $this->checkWinner($moves);

		if ($winner >= 0){
			$emptyCount = 0;
			for ($i = 0; $i < 15; $i++){
				for ($j = 0; $j < 15; $j++){
					if ($moves[$i][$j] == 0)
						$emptyCount++;
				}
			}
			$newTurn = (($emptyCount % 2) == 0) ? $newTurn : $turn;
			$this->db->insert('{CARO_PREFIX}records', array('player1Id' => $gameData['player1Id'], 'player2Id' => $gameData['player2Id'], 'startTime' => $gameData['startTime'], 'endTime' => strtotime(date("Y-m-d H:i:s"))));
			$data['status'] = 1;
		}

		$this->db->update('{CARO_PREFIX}games', $data, $where);

		if ($this->db->affected_rows() > 0)
			return true;

		return false;
	}

	public function checkPlayerTimeout($gameId, $player1Id, $player2Id){
		$result = 3;

		$this->db->select('lastActivity');
		$query1 = $this->db->get_where('{CARO_PREFIX}players', array('playerId' => $player1Id));
		if ($query1->num_rows() > 0)
			if (strtotime(date("Y-m-d H:i:s")) - strtotime($query1->row_array()['lastActivity']) < CARO_PLAYER_TIMEOUT)
				$result -= 1;

		if ($player2Id > 0){
			$this->db->select('lastActivity');
			$query2 = $this->db->get_where('{CARO_PREFIX}players', array('playerId' => $player2Id));
			if ($query2->num_rows() > 0)
				if (strtotime(date("Y-m-d H:i:s")) - strtotime($query2->row_array()['lastActivity']) < CARO_PLAYER_TIMEOUT)
					$result -= 2;
		}
		if ($result == 1)
			$this->removePlayer($gameId, $player1Id, $player2Id);
		else if ($result == 2 && $player2Id > 0)
			$this->removePlayer($gameId, $player2Id);
		else if ($result == 3)
			$this->db->delete('{CARO_PREFIX}games', array('gameId' => $gameId, 'player1Id' => $player1Id, 'player2Id' => $player2Id));

		return $result;
	}

	private function checkPlayerGameExists($playerId){
		$this->db->select('gameId');
		$this->db->limit(1);
		$queryResult = $this->db->get_where('{CARO_PREFIX}games', "player1Id = ".$playerId." OR player2Id = ".$playerId."")->row_array();
		if (isset($queryResult))
			return $queryResult['id'];

		return 0;
	}

	private function checkEmpty($moves){
		//$moves = json_decode($movesJson, true);
		if (!empty($moves)){
			for ($i = 0; $i < 15; $i++){
				for ($j = 0; $j < 15; $j++){
					if ($moves[$i][$j] == 0)
						return array(
							     'x' => $i,
							     'y' => $j
							     );
				}
			}
		}
		return false;
	}

	private function checkWinner($moves){

		if (!empty($moves)){
			$emptyCount = 0;
			for ($i = 0; $i < 15; $i++){
				for ($j = 0; $j < 15; $j++){
					$move = $moves[$i][$j];
					if ($move == 0){
						$emptyCount++;
					}
					else {
						// horizontal
						if ($i < 11){
							if ($moves[$i + 1][$j] == $move && $moves[$i + 2][$j] == $move && $moves[$i + 3][$j] == $move && $moves[$i + 4][$j] == $move){
								if ($i == 0){
									if ($moves[$i + 5][$j] != $move)
										return $move;
								}
								else if ($i == 10){
									if ($moves[$i - 1][$j] != $move)
										return $move;
								}
								else {
									if ($moves[$i - 1][$j] != $move && $moves[$j + 5][$j] != $move){
										if ($moves[$i - 1][$j] == 0 || $moves[$j + 5][$j] == 0)
											return $move;
									}
								}
							}
						}
						// vertical
						if ($j < 11){
							if ($moves[$i][$j + 1] == $move && $moves[$i][$j + 2] == $move && $moves[$i][$j + 3] == $move && $moves[$i][$j + 4] == $move){
								if ($j == 0){
									if ($moves[$i][$j + 5] != $move)
										return $move;
								}
								else if ($j == 10){
									if ($moves[$i][$j - 1] != $move)
										return $move;
								}
								else {
									if ($moves[$i][$j - 1] != $move && $moves[$j][$j + 5] != $move){
										if ($moves[$i][$j - 1] == 0 || $moves[$j][$j + 5] == 0)
											return $move;
									}
								}
							}
						}
						// diagonal 1
						if ($i < 11 && $j < 11){
							if ($moves[$i + 1][$j + 1] == $move && $moves[$i + 2][$j + 2] == $move && $moves[$i + 3][$j + 3] == $move && $moves[$i + 4][$j + 4] == $move){
								if ($i == 0){
									if ($j == 10)
										return $move;
									else if ($moves[$i + 5][$j + 5] != $move)
										return $move;
								}
								else if ($i == 10){
									if ($j == 0)
										return $move;
									else if ($moves[$i - 1][$j - 1] != $move)
										return $move;
								}
								else {
									if ($moves[$i - 1][$j - 1] != $move && $moves[$j + 5][$j + 5] != $move){
										if ($moves[$i - 1][$j - 1] == 0 || $moves[$j + 5][$j + 5] == 0)
											return $move;
									}
								}
							}
						}
						// diagonal 2
						if ($i < 11 && $j > 3){
							if ($moves[$i + 1][$j - 1] == $move && $moves[$i + 2][$j - 2] == $move && $moves[$i + 3][$j - 3] == $move && $moves[$i + 4][$j - 4] == $move){
								if ($i == 0){
									if ($j == 4)
										return $move;
									else if ($moves[$i + 5][$j - 5] != $move)
										return $move;
								}
								else if ($i == 10){
									if ($j == 14)
										return $move;
									else if ($moves[$i - 1][$j + 1] != $move)
										return $move;
								}
								else {
									if ($moves[$i - 1][$j + 1] != $move && $moves[$j + 5][$j - 5] != $move){
										if ($moves[$i - 1][$j + 1] == 0 || $moves[$j + 5][$j - 5] == 0)
											return $move;
									}
								}
							}
						}
					}
				}
			}
			if ($emptyCount == 0)
				return 0;
		}
		return -1;
	}

}
