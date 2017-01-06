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
				       'data' => $this->Game_model->getGamesList()
				       ));
        }

        public function create_game()
        {
                $playerId = $this->session->userdata('player_id');
                if ($playerId > 0){
                        $gameId = $this->Game_model->createGame($playerId);
                        if ($gameId > 0)
                                echo json_encode(array(
						       'status' => EXIT_SUCCESS,
						       'data' => array(
								       'gameId' => $gameId
								       )
						       ));
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

        public function join_game()
        {
                $playerId = $this->session->userdata('player_id');
                $gameId = $this->input->post('gameId');
                if ($playerId > 0 && $gameId > 0){

                        if ($this->Game_model->joinGame($gameId, $playerId) > 0){
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
                $gameId = $this->input->post('gameId');
                if ($playerId > 0 && $gameId > 0){
                        if ($this->Game_model->readyGame($gameId, $playerId) > 0){
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

        public function quit_game()
        {
                $playerId = $this->session->userdata('player_id');
                $gameId = $this->input->post('gameId');
                if ($playerId > 0 && $gameId > 0)
                        $this->Game_model->removePlayer($gameId, $playerId);

		echo json_encode(array(
				       'status' => EXIT_SUCCESS,
				       'data' => array()
				       ));

        }

        public function refresh_game(){

                $gameId = $this->input->post('gameId');
                $player1Id = $this->input->post('player1Id');
                $player2Id = $this->input->post('player2Id');

                $moves = json_encode($this->input->post('moves'));
                $turn = intval($this->input->post('turn'));

                while (1){

                        $gameData = $this->Game_model->getGameData($gameId, "player1Id, player2Id, moves, turn, lastMove");

			$this->Player_model->updatePlayer(array('lastActivity' => date("Y-m-d H:i:s")), $this->session->userdata('player_id'));

                        if ($gameData){

                                $changeData = array();                               
                                $currentTime = date("Y-m-d H:i:s");

				$timeElapsed = strtotime($currentTime) - strtotime($gameData['lastMove']);
				if ($timeElapsed >= 20){
					$this->Game_model->updateMove($gameId);
					$changeData['countdown'] = CARO_COUNTDOWN;
					exit(json_encode(array(
							       'status' => EXIT_SUCCESS,
							       'data' => array()
							       )));
				}
				else if (($timeElapsed % 1) < 0.4){
					$changeData['countdown'] = intval(CARO_COUNTDOWN - $timeElapsed);
				}

                                $player1Id = $gameData['player1Id'];
                                $player1 = $this->Player_model->getPlayerById($player1Id, "name");

				$this->Game_model->checkPlayerTimeout($gameId, $player1Id, $player2Id);

                                if ($player1Id != $gameData['player1Id']){
                                        if ($gameData['player1Id'] == 0){
                                                if ($gameData['player2Id'] == 0){
                                                        $this->Game_model->deleteGame($gameId);
                                                        exit(json_encode(array(
									       'status' => EXIT_ERROR,
									       'data' => array(
											       'errorMessage' => "Game is closed!"
											       )
									       )));
                                                }
                                                else {

                                                        $player2Name = $this->Player_model->getPlayerById($gameData['player1Id'], "name");
                                                        if ($player2Name){
                                                                $changeData['player1Id'] = $gameData['player2Id'];
                                                                $changeData['player1Name'] = $player2Name['name'];
                                                        }

                                                        $changeData['player2Id'] = 0;
                                                        $changeData['player2Name'] = "";

                                                }
                                        }
                                }

                                else if ($player2Id != $gameData['player2Id']){
                                        $changeData['player2Id'] = $gameData['player2Id'];

                                        if ($gameData['player2Id'] == 0){
                                                $changeData['player2Name'] = "";
                                        }
                                        else {
                                                $player2Name = $this->Player_model->getPlayerById($gameData['player1Id'], "name");
                                                if ($player2Name){
                                                        $changeData['player2Name'] = $player2Name['name'];
                                                }

                                                else {
                                                        $changeData['player2Id'] = 0;
                                                        $changeData['player2Name'] = "";
                                                }

                                        }
                                }

                                else {
                                        if ($gameData['moves'] != $moves)
                                                $changeData['moves'] = json_decode($gameData['moves']);
                                        if ($gameData['turn'] != $turn)
                                                $changeData['turn'] = $gameData['turn'];
                                }

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

        public function update_move($gameId){
                $moveX = (intval($this->input->post('move_x'))) ? intval($this->input->post('move_x')) : 0;
                $moveY = (intval($this->input->post('move_y'))) ? intval($this->input->post('move_y')) : 0;
		$playerId = $this->session->userdata('player_id');
		if ($playerId > 0){
			$result = $this->Game_model->updateMove($gameId, $moveX, $moveY);
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
