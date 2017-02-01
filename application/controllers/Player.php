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

class Player extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->helper('security');
                $this->load->model('Player_model');

        }

        public function login(){
                $playerId = $this->session->userdata('player_id');

                if ($playerId > 0)
                        echo json_encode(array(
					       'status' => EXIT_SUCCESS,
					       'data' => array()
					       ));
                else {
                        $this->load->helper(array('cookie', 'form'));
                        $this->load->library('form_validation');
                        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|xss_clean');
                        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

                        if ($this->form_validation->run() === TRUE){
                                $email = $this->input->post('email');
                                $password = $this->input->post('password');
                                $player = $this->Player_model->login($email, $password);
                                if ($player){

                                        $this->session->set_userdata('player_id', $player['id']);
                                        $this->session->set_userdata('player_name', $player['name']);
                                        if($this->input->post('remember') == 'on'){

                                                $this->input->set_cookie(array('name' => 'email', 'value' => $email, 'expire' => CARO_COOKIE_EXPIRE));
                                                $this->input->set_cookie(array('name' => 'password', 'value' => $password, 'expire' => CARO_COOKIE_EXPIRE));
                                        }
                                        echo json_encode(array(
							       'status' => EXIT_SUCCESS,
							       'data' => array()
							       ));

                                }
                                else
                                        echo json_encode(array(
							       'status' => EXIT_ERROR,
							       'data' => array(
									       'errorMessage' => "Email or password is wrong!"
									       )
							       ));
                        }
                        else
                                echo json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'errorMessage' => "Email or password is wrong!"
								       )
						       ));

                }
        }

        public function logout(){
                $this->session->sess_destroy();
                echo json_encode(array(
				       'status' => EXIT_SUCCESS,
				       'data' => array()
				       ));
        }

        public function get_session_data(){
                $playerId = $this->session->userdata('player_id');

                if ($playerId > 0)
                        echo json_encode(array(
					       'status' => EXIT_SUCCESS,
					       'data' => array(
							       'playerId' => $playerId,
							       'playerName' => $this->session->userdata('player_name')
							       )
					       ));

		else
                        echo json_encode(array(
					       'status' => EXIT_ERROR,
					       'data' => array(
							       'errorMessage' => "You are not logged in!"
							       )
					       ));

        }

        public function register(){
                $playerId = $this->session->userdata('player_id');

                if ($playerId > 0)
                        echo json_encode(array(
					       'status' => EXIT_SUCCESS,
					       'data' => array()
					       ));

                else {
                        $this->load->helper('form');
                        $this->load->library('form_validation');
                        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[{CARO_PREFIX}players.email]|xss_clean');
                        $this->form_validation->set_rules('name', 'Name', 'trim|required|alpha_numeric_spaces|xss_clean');
                        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
                        $this->form_validation->set_rules('confirmPassword', 'Confirm Password', 'trim|required|matches[password]|xss_clean');

                        $this->form_validation->set_error_delimiters('', '');

                        if ($this->form_validation->run() === TRUE){
                                $inputData = array();
                                $inputData['name'] = $this->input->post('name');
                                $inputData['email'] = $this->input->post('email');

                                $password = $this->input->post('password');
                                if ($password == $this->input->post('confirmPassword'))
                                        $inputData['password'] = md5(md5(trim($password)));
                                else
                                        $data['error']['password'] = 'Password does not match!';

                                $inputData['dob'] = date('Y-m-d', strtotime($this->input->post('dob')));
                                if (empty($data['error'])){
                                        if ($this->Player_model->updatePlayer($inputData) > 0)

                                                echo json_encode(array(
								       'status' => EXIT_SUCCESS,
								       'data' => array()
								       ));

                                        else {
                                                echo json_encode(array(
								       'status' => EXIT_ERROR,
								       'data' => array(
										       'errorMessage' => "Error occured while registering!"
										       )
								       ));
                                        }

                                }
                                else 
                                        echo json_encode(array(
							       'status' => EXIT_ERROR,
							       'data' => $data['error']
							       ));
                        }

                        else {
                                echo json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'email' => form_error('email'),
								       'name' => form_error('name'),
								       'password' => form_error('password'),
								       'confirmPassword' => form_error('confirmPassword')
								       )
						       ));
                        }

                }
        }

        public function update_profile(){
                $playerId = $this->session->userdata('player_id');
                if ($playerId > 0){

                        $this->load->helper('form');
                        $this->load->library('form_validation');
                        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|required|xss_clean');
                        $this->form_validation->set_rules('name', 'Name', 'trim|required|alpha_numeric_spaces|xss_clean');
                        $this->form_validation->set_error_delimiters('', '');

                        $data = array();
                        $data['error'] = array();

                        if ($this->form_validation->run() === TRUE){

                                $inputData = array();
                                $inputData['name'] = $this->input->post('name');

                                $email = $this->input->post('email');
                                if ($this->Player_model->checkEmailExists($email, $playerId)){
                                        $data['error']['email'] = 'Email already exists!';
                                        $inputData['email'] = $email;
                                }

                                $config = array(
						'upload_path' => CARO_USER_PATH,
						'allowed_types' => "gif|jpg|png|jpeg",
						'max_size' => CARO_AVATAR_MAX,
						);

                                $this->load->library('upload', $config);
                                if($this->upload->do_upload('avatar')){
                                        $uploadData = $this->upload->data();
                                        $inputData['avatar'] = $uploadData['file_name'];
                                }
                                else {
                                        $data['error']['upload'] = 'Upload error!';

                                };

                                $inputData['dob'] = date('Y-m-d', strtotime($this->input->post('dob')));
                                if (empty($data['error'])){
                                        if ($this->Player_model->updatePlayer($inputData, $playerId) > 0)
                                                echo json_encode(array(
								       'status' => EXIT_SUCCESS,
								       'data' => array()
								       ));

                                        else {

                                                if ($inputData['avatar'] && file_exists($avatar = CARO_USER_PATH.$inputData['avatar']))
                                                        unlink($avatar);

                                                echo json_encode(array(
								       'status' => EXIT_ERROR,
								       'data' => array(
										       'errorMessage' => "Error occured while updating profiles!"
										       )
								       ));

                                        }
                                }
                                else {

                                        if ($inputData['avatar'] && file_exists($avatar = CARO_USER_PATH.$inputData['avatar']))
                                                unlink($avatar);

                                        echo json_encode(array(
							       'status' => EXIT_ERROR,
							       'data' => $data['error']
							       ));
                                }

                        }
                        else {
                                echo json_encode(array(
						       'status' => EXIT_ERROR,
						       'data' => array(
								       'email' => form_error('email'),
								       'name' => form_error('name')
								       )
						       ));

                        }

                }
                else 
                        echo json_encode(array(
					       'status' => EXIT_ERROR,
					       'data' => array(
							       'errorMessage' => "User is not specified!"
							       )
					       ));

        }
}
