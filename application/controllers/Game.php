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
defined('BASEPATH') OR exit('No direct script access allowed');

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
}

header('Content-Type: application/json');

class Game extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->model('Game_model');
                $this->load->model('Player_model');
        }

        public function index(){
                echo json_encode(array(
				       'status' => EXIT_SUCCESS,
				       'data' => $this->show_games_list()
				       ));
        }

        public function create_game()
        {
                $playerId = $this->session->userdata('player_id');
                if ($playerId > 0){
                        $gameId = $this->Game_model->createGame($playerId);
                        if ($gameId > 0){
				$this->Player_model->updatePlayer(array('lastActivity' => date("Y-m-d H:i:s")), $this->session->userdata('player_id'));
				
                                echo json_encode(array(
						       'status' => EXIT_SUCCESS,
						       'data' => array(
								       'gameId' => $gameId
								       )
						       ));
			}
                        else
                                echo json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'errorMessage' => "Error while creating game!"
								       )
						       ));
                }
                else
                        echo json_encode(array(
					       'status' => EXIT_ERROR,
					       'data' => array(
							       'errorMessage' => 'You are not logged in!'
							       )
					       ));
        }

	private function show_games_list(){
		$gamesList = $this->Game_model->showGamesList();
		if (!empty($gamesList)){
			foreach ($gamesList as $gameIndex => $game){
				$playerTimeout = $this->Game_model->checkPlayerTimeout($game["gameId"], $game["player1Id"], $game["player2Id"]);
				if (!$playerTimeout)
					unset($gamesList[$gameIndex]);
				else {
					$gamesList[$gameIndex]["player1Id"] = $playerTimeout['player1Id'];
					$gamesList[$gameIndex]["player2Id"] = $playerTimeout['player2Id'];
					$gamesList[$gameIndex]["player1Name"] = $playerTimeout['player1Name'];
					$gamesList[$gameIndex]["player2Name"] = $playerTimeout['player2Name'];

				}
			}
		}
		$gamesListFinal = array_values($gamesList);
		return $gamesListFinal;
	}

        public function join_game($gameId = 0)
        {
                $playerId = $this->session->userdata('player_id');
                $gameId = intval($gameId);
                if ($playerId > 0 && $gameId > 0){

                        if ($this->Game_model->joinGame($gameId, $playerId) > 0){
				$this->Player_model->updatePlayer(array('lastActivity' => date("Y-m-d H:i:s")), $playerId);
				
				echo json_encode(array(
						       'status' => EXIT_SUCCESS,
						       'data' => array()
						       ));
			}
			else 
				echo json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'errorMessage' => "Error while getting player!"
								       )
						       ));
                        
                }
                else
                        echo json_encode(array(
					       'status' => EXIT_ERROR,
					       'data' => array(
							       'errorMessage' => "Game or player is not specified!"
							       ) 
					       ));
        }

        public function ready_game()
        {
                $playerId = $this->session->userdata('player_id');
                if ($playerId > 0){
			$gameId = $this->Game_model->readyGame($playerId);
                        if ($gameId > 0){
                                $this->Game_model->startGame($gameId);

				echo json_encode(array(
						       'status' => EXIT_SUCCESS,
						       'data' => array()
						       ));
                        }
                        else
                                echo json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'errorMessage' => "Error while getting ready!"
								       )
						       ));

                }
                else
                        echo json_encode(array(
					       'status' => EXIT_ERROR,
					       'data' => array(
							       'errorMessage' => "Game or Player not specified!"
							       )
					       ));

        }

        public function quit_game($gameId = 0)
        {
		$gameId = intval($gameId);
                $playerId = $this->session->userdata('player_id');
                if ($playerId > 0 && $gameId > 0)
                        $this->Game_model->removePlayer($gameId, $playerId);

		echo json_encode(array(
				       'status' => EXIT_SUCCESS,
				       'data' => array()
				       ));
        }

        public function refresh_game($gameId = 0, $player1Id = 0, $player2Id = 0, $status = 0, $turn = 0){
		$gameId = intval($gameId);
		if ($gameId <= 0)
			exit(json_encode(array(
					       'status' => EXIT_ERROR,
					       'data' => array(
							       'errorMessage' => "Game is closed!"
							       )
					       )));
		$player1Id = intval($player1Id);
		$player2Id = intval($player2Id);
		$status = intval($status);
		$turn = intval($turn);
                $moves = json_encode($this->input->post('moves'));

                while (1){

                        $gameData = $this->Game_model->getGameData("player1Id, player2Id, moves, turn, lastMove, status", array('gameId' => $gameId));
                        if ($gameData){
				$gameData["player1Id"] = intval($gameData["player1Id"]);
				$gameData["player2Id"] = intval($gameData["player2Id"]);
				$gameData["turn"] = intval($gameData["turn"]);

                                $changeData = array();                               
                                $currentTime = date("Y-m-d H:i:s");
				if ($gameData["status"] == 2){
					$timeElapsed = strtotime($currentTime) - strtotime($gameData['lastMove']);
					if ($timeElapsed >= 20){
						$this->Game_model->updateMove($gameData["player".$gameData["turn"]."Id"]);
						exit(json_encode(array(
								       'status' => EXIT_SUCCESS,
								       'data' => array()
								       )));
					}
					else if (($timeElapsed % 1) < 0.4){
						$changeData['countdown'] = intval(CARO_COUNTDOWN - $timeElapsed);
					}
				}
				$playerTimeout = $this->Game_model->checkPlayerTimeout($gameId, $gameData["player1Id"], $gameData["player2Id"]);
				if (!$playerTimeout)
					exit(json_encode(array(
							       'status' => EXIT_ERROR,
							       'data' => array(
									       'errorMessage' => "Game is closed!"
									       )
							       )));
				
				else {
					if ($player1Id != $playerTimeout['player1Id']){
						$changeData["player1Id"] = $playerTimeout['player1Id'];
						$changeData["player1Name"] = $playerTimeout['player1Name'];
					}
					if ($player2Id != $playerTimeout['player2Id']){
						$changeData["player2Id"] = $playerTimeout['player2Id'];
						$changeData["player2Name"] = $playerTimeout['player2Name'];
					}
				}

				$this->Player_model->updatePlayer(array('lastActivity' => date("Y-m-d H:i:s")), $this->session->userdata('player_id'));
				
				if ($gameData['moves'] != $moves)
					$changeData['moves'] = json_decode($gameData['moves']);
				if ($gameData['turn'] != $turn)
					$changeData['turn'] = $gameData['turn'];
				if ($gameData['status'] != $status)
					$changeData['status'] = $gameData['status'];

                                if (!empty($changeData)){
                                        exit(json_encode(array(
							       'status' => EXIT_SUCCESS,
							       'data' => $changeData
							       )));
                                }

                        }

                        else{
                                exit(json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'errorMessage' => "Game is closed!"
								       )
						       )));
                        }
                }
        }

        public function update_move($moveX = 0, $moveY = 0){
                $moveX = intval($moveX);
                $moveY = intval($moveY);
		$playerId = $this->session->userdata('player_id');
		if ($playerId > 0){
			$result = $this->Game_model->updateMove($playerId, $moveX, $moveY);
			if ($result)
				echo json_encode(array(
						       'status' => EXIT_SUCCESS,
						       'data' => $result
						       ));
			else
				echo json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'errorMessage' => 'Error while making a move!'
								       )
						       ));
		}
		else
			echo json_encode(array(
					       'status' => EXIT_ERROR,
					       'data' => array(
							       'errorMessage' => 'Error while making a move!'
							       )
					       ));

        }

}
