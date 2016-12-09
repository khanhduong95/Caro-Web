<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Game extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->model('Game_model');
                $this->load->model('Player_model');
        }

        public function create_game()
        {
                $playerId = $this->session->userdata('player')['id'];
                if ($playerId > 0){
                        $gameId = $this->Game_model->createGame($playerId);
                        if ($gameId > 0)
                                redirect(base_url('index.php/game/display_game/'.$gameId));
                        else
                                redirect(base_url());
                }
                else
                        redirect(base_url());
        }

        public function join_game()
        {
                $playerId = $this->session->userdata('player')['id'];
                $gameId = $this->input->post('gameId');
                if ($playerId > 0 && $gameId > 0){

                        if ($this->Game_model->joinGame($gameId, $playerId) > 0){
                                $this->db->select('firstName, lastName');
                                $query = $this->db->get_where(CARO_DB_PREFIX.'players', array('id' => $playerId));
                                if ($query->num_rows() > 0){
                                        $player = $query->row_array();
                                        echo json_encode(
                                                array(
                                                        'playerFirstName' => $player['firstName'],
                                                        'playerLastName' => $player['lastName']
                                                )
                                        );
                                }
                                else 
                                        echo "Error while getting player!";
                        }
                }
                else
                        echo 0;
        }

        public function ready_game()
        {
                $playerId = $this->session->userdata('player')['id'];
                $gameId = $this->input->post('gameId');
                if ($playerId > 0 && $gameId > 0){
                        if ($this->Game_model->readyGame($gameId, $playerId) > 0){
                                $this->Game_model->startGame($gameId);
                                while (1){
                                        $countdownResult = $this->countdown();
                                        if ($countdownResult > 0)
                                                $this->Game_model->updateGameData(array('temp_turn' => $countdownResult, 'countdown' => 20));
                                        else 
                                                break;
                                }
                                echo "Game is closed!";
                        }
                        else
                                echo "Error while getting ready!";
                }
                else
                        echo "Game or Player not specified!";
        }

        public function quit_game()
        {
                $playerId = $this->session->userdata('player')['id'];
                $gameId = $this->input->post('gameId');
                if ($playerId > 0 && $gameId > 0){
                        if ($this->Game_model->quitGame($gameId, $playerId) > 0)
                                echo 1;
                        else
                                echo 0;
                }
                else
                        echo 0;
        }

        public function display_game($gameId){
                $gameData = $this->Game_model->getGameData($gameId, "player1Id, player2Id, moves, temp_turn");

                $data['gameId'] = $gameId;
                $data['player1'] = $this->Player_model->getPlayerById($gameData['player1Id'], "firstName, lastName");
                $data['player2'] = $this->Player_model->getPlayerById($gameData['player2Id'], "firstName, lastName");
                $data['moves'] = json_decode($gameData['moves'], true);
                $data['temp_turn'] = $gameData['temp_turn'];
                $this->load->view('template/header', $data);
                $this->load->view('game_view', $data);


        }

        public function refresh_game(){

                $gameId = $this->input->post('gameId');
                $player1Id = $this->input->post('player1Id');
                $player2Id = $this->input->post('player2Id');

                $moves = json_encode($this->input->post('moves'));
                $temp_turn = intval($this->input->post('temp_turn'));
                $countdown = intval($this->input->post('countdown'));

                while (1){

                        $gameData = $this->Game_model->getGameData($gameId, "player1Id, player2Id, moves, temp_turn, countdown");

                        if ($gameData){

                                $changeData = array();                               
                                $currentTime = date("Y-m-d H:i:s");

                                $player1LastActivity = $this->Player_model->getPlayerById($gameData['player1Id'], "lastActivity");

                                if (!$player1LastActivity || $currentTime - date("Y-m-d H:i:s", strtotime($player1LastActivity['lastActivity']) ) > CARO_TIMEOUT){
                                        $gameData['player1Id'] = 0;
                                }

                                if ($gameData['player2Id'] > 0){
                                        $player2LastActivity = $this->Player_model->getPlayerById($gameData['player2Id'], "lastActivity");
                                        if (!$player2LastActivity || $currentTime - date("Y-m-d H:i:s", strtotime($player2LastActivity['lastActivity']) ) > CARO_TIMEOUT){
                                                $gameData['player2Id'] = 0;
                                        }
                                }

                                if ($player1Id != $gameData['player1Id']){
                                        if ($gameData['player1Id'] == 0){
                                                if ($gameData['player2Id'] == 0){
                                                        $this->Game_model->deleteGameData($gameId);
                                                        echo "Game is closed!";
                                                        exit();
                                                }
                                                else {

                                                        $player2Name = $this->Player_model->getPlayerById($gameData['player1Id'], "firstName, lastName");
                                                        if ($player2Name){
                                                                $changeData['player1Id'] = $gameData['player2Id'];
                                                                $changeData['player1FirstName'] = $player2Name['firstName'];
                                                                $changeData['player1LastName'] = $player2Name['lastName'];
                                                        }

                                                        $changeData['player2Id'] = 0;
                                                        $changeData['player2FirstName'] = "";
                                                        $changeData['player2LastName'] = "";

                                                }
                                        }
                                }

                                else if ($player2Id != $gameData['player2Id']){
                                        $changeData['player2Id'] = $gameData['player2Id'];

                                        if ($gameData['player2Id'] == 0){
                                                $changeData['player2FirstName'] = "";
                                                $changeData['player2LastName'] = "";

                                        }
                                        else {
                                                $player2Name = $this->Player_model->getPlayerById($gameData['player1Id'], "firstName, lastName");
                                                if ($player2Name){
                                                        $changeData['player2FirstName'] = $player2Name['firstName'];
                                                        $changeData['player2LastName'] = $player2Name['lastName'];
                                                }

                                                else {
                                                        $changeData['player2Id'] = 0;
                                                        $changeData['player2FirstName'] = "";
                                                        $changeData['player2LastName'] = "";
                                                }

                                        }
                                }

                                else {
                                        if ($gameData['moves'] != $moves)
                                                $changeData['moves'] = json_decode($gameData['moves']);
                                        if ($gameData['temp_turn'] != $temp_turn)
                                                $changeData['temp_turn'] = $gameData['temp_turn'];
                                        if ($gameData['countdown'] != $countdown)
                                                $changeData['countdown'] = $gameData['countdown'];
                                }

                                if (!empty($changeData)){
                                        echo json_encode($changeData);
                                        exit();
                                }

                        }

                        else{
                                echo "Game is closed!";
                                exit();
                        }
                }
        }

        public function update_move(){
                $playerId = $this->session->userdata('player')['id'];
                if ($playerId > 0){
                        $gameId = $this->input->post('gameId');
                        $gameData = $this->Game_model->getGameData($gameId, 'player1Id, player2Id, moves, temp_turn, countdown, status');
                        if ($gameData){
                                if ($gameData['status'] == 2){
                                        if (($gameData['player1Id'] == $playerId && $gameData['temp_turn'] == 1) || ($gameData['player2Id'] == $playerId && $gameData['temp_turn'] == 2)){
                                                $move_x = intval($this->input->post('moveX'));
                                                $move_y = intval($this->input->post('moveY'));
                                                $moves = json_decode($gameData['moves']);
                                                if ($moves[$moveX][$moveY] == 0 && $moveX >= 0 && $moveX < 15 && $moveY >= 0 && $moveY < 15){
                                                        $moves[$moveX][$moveY] = $gameData['temp_turn'];
                                                        $newTurn = ($gameData['temp_turn'] == 1) ? 1 : 2;
                                                        if ($this->Game_model->updateMove($gameId, array('moves' => json_encode($moves), 'temp_turn' => $newTurn)))
                                                                echo ($newTurn == 2) ? "X" : "O";
                                                        else
                                                                echo "Error while making a move!";
                                                }
                                        }

                                }

                        }
                        else {
                                echo "Game is closed!";
                        }

                }

        }

        private function countdown($gameId){
                $currentTime = date("Y-m-d H:i:s");
                while (1){
                        if (date("Y-m-d H:i:s") - $currentTime >= 1){
                                $currentTime = date("Y-m-d H:i:s");
                                $gameData = $this->Game_model->getGameData($gameId, 'temp_turn, countdown');
                                if ($gameData){
                                        if ($gameData['countdown'] <= 0){
                                                return ($gameData['temp_turn'] == 1) ? 2 : 1;
                                        }
                                        else if ($this->Game_model->updateGameData(array('countdown' => $gameData['countdown'] - 1), $gameId) <= 0){
                                                return 0;
                                        }
                                }
                                else {
                                        return 0;
                                }
                        }
                }
        }

}
