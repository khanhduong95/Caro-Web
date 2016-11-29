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
                        if ($this->Game_model->readyGame($gameId, $playerId) > 0)
                                echo $this->Game_model->startGame($gameId);
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

        public function update_game(){
                while (1){

                        $gameId = $this->input->post('gameId');
                        $player1Id = $this->input->post('player1Id');
                        $player2Id = $this->input->post('player2Id');
                        $moves = json_encode($this->input->post('moves'));
                        $temp_turn = intval($this->input->post('temp_turn'));
                        $countdown = intval($this->input->post('countdown'));
                        $query = $this->Game_model->getGameData($gameId, "player1Id, player2Id, moves, temp_turn, countdown");
                        if ($query->num_rows() > 0){
                                $gameData = $query->row_array();
                                $changeData = array();
                                if ($gameData['player1Id'] != $player1Id)
                                        $changeData['player1Id'] = $gameData['player1Id'];
                                if ($gameData['player2Id']) != $player2Id)
                                        $changeData['player2Id'] = $gameData['player2Id'];
                                if ($gameData['moves'] != $moves)
                                        $changeData['moves'] = json_decode($gameData['moves']);
                                if ($gameData['temp_turn']) != $temp_turn)
                                        $changeData['temp_turn'] = $gameData['temp_turn'];
                                if ($gameData['countdown'] != $countdown)
                                        $changeData['countdown'] = $gameData['countdown'];
                                if (!empty($changeData)){
                                        echo json_encode($changeData);
                                        break;
                                }
                        }
                        else{
                                echo "Game closed!";
                                break;
                        }
                }
        }

        private function keep_game_connection($gameId){
                $timeResult = $this->Game_model->countdown($gameId);
                if ($timeResult){
                        $countdown = $timeResult['countdown'];
                        $timeout = $timeResult['timeout'];
                }
        }
}
