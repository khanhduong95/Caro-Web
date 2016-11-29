<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Installer extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->model('Installer_model');
        }

	public function install()
	{
                $this->Installer_model->install();
                redirect(base_url('index.php/home'));
        }

        public function uninstall()
	{
                $this->Installer_model->uninstall();
                redirect(base_url('index.php/home'));
	}
}
