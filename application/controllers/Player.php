<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Player extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->model('Player_model');
                $this->load->helper(array('cookie', 'form'));
        }

        public function login(){
                $playerId = $this->session->userdata('player')['id'];

                if ($playerId > 0)
                        redirect(base_url());
                else {
                        $email = $this->input->post('email');
                        $password = $this->input->post('password');
                        $loginError = "";
                        if($this->input->post('submit') && !empty($email) && !empty($password)){
                                $player = $this->Player_model->login($email, $password);
                                if($player){

                                        $this->session->set_userdata('player', $player);
                                        if($this->input->post('remember')=='on'){

                                                $this->input->set_cookie(array('name' => 'email', 'value' => $email, 'expire' => '86400'));
                                                $this->input->set_cookie(array('name' => 'password', 'value' => $password, 'expire' => '86400'));
                                        }
                                        redirect(base_url());

                                }
                                else $loginError = "User is not activate or Email/Password is wrong.";
                        }
                        else $loginError = "Enter email and password.";
                        if(!empty($loginError)) {
                                $data = array();
                                $data['loginError'] = $loginError;
                                $this->load->view('login_view', $data);
                        }
                }
        }

        public function logout(){
                $this->session->sess_destroy();;
                redirect(base_url());
        }

        public function register(){
                $playerId = $this->session->userdata('player')['id'];

                if ($playerId > 0)
                        redirect(base_url());

                else {
                        $data['error'] = array();
                        if ($this->input->post('submit')){
                                $inputData = array();
                                $inputData['firstName'] = $this->input->post('firstName');
                                $inputData['lastName'] = $this->input->post('lastName');
                                $email = trim($this->input->post('email'));
                                if ($this->Player_model->checkEmailExists($email)){
                                        $data['error']['email'] = 'Email already exists!';
                                }
                                $inputData['email'] = $email;

                                $password = $this->input->post('password');
                                if ($password == $this->input->post('confirmPassword'))
                                        $inputData['password'] = md5(trim($password));
                                else
                                        $data['error']['password'] = 'Password does not match!';

                                $inputData['dob'] = date('Y-m-d', strtotime($this->input->post('dob')));
                                if (empty($data['error'])){
                                        if ($this->Player_model->updatePlayer($inputData) > 0)
                                                $data['success'] = 'Register success!';
                                        
                                        else 
                                                $data['error']['register'] = 'Register error!';
                                        
                                }
                        }
                        $this->load->view('register_view', $data);
                }
        }

        public function update_profile(){
                $playerId = $this->session->userdata('player')['id'];
                if ($playerId > 0){
                        $data = array();
                        $data['id'] = $playerId;
                        $data['error'] = array();
                        if ($data[] = $this->Player_model->getPlayerById($playerId)){
                                if ($this->input->post('submit')){

                                        $inputData = array();
                                        $inputData['firstName'] = $this->input->post('firstName');
                                        $inputData['lastName'] = $this->input->post('lastName');
                                        $email = trim($this->input->post('email'));
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

                                        if (!$this->Player_model->checkPassword($playerId, trim($this->input->post('password'))))
                                                $data['error']['password'] = 'Wrong password!';

                                        $inputData['dob'] = date('Y-m-d', strtotime($this->input->post('dob')));
                                        if (empty($data['error'])){
                                                if ($this->Player_model->updatePlayer($inputData, $playerId) > 0){
                                                        $data['success'] = 'Update profile success!';
                                                }
                                                else {
                                                        $data['error']['update'] = 'Update profile error!';
                                                        if ($inputData['avatar'] && file_exists($avatar = CARO_USER_PATH.$inputData['avatar']))
                                                                unlink($avatar);

                                                }
                                        }
                                        else {

                                                if ($inputData['avatar'] && file_exists($avatar = CARO_USER_PATH.$inputData['avatar']))
                                                        unlink($avatar);                
                                        }
                                }
                        }
                        else {
                                $data['error']['user'] = 'User does not exist!';

                        }

                        $this->load->view('update_profile_view', $data);

                }
                else 
                        redirect(base_url());

        }
}
