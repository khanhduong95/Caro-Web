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
if ( ! defined('BASEPATH')) exit("No direct script access allowed");

class Migrate extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->input->is_cli_request() 
			or exit();

		$this->load->library('migration');
	}

	public function index()
	{

		if(!$this->migration->latest()) 
			{
				show_error($this->migration->error_string());
			}

	}
}