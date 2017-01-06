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

Class Player_model extends CI_Model
{
        public function __construct()
        {
                parent::__construct();
        } 

        public function login($email, $password){
                $this->db->select('id, name');
                $this->db->limit(1);
                $query = $this->db->get_where('{CARO_PREFIX}players', array('email' => $email, 'password' => md5(md5($password))));
                if ($query->num_rows() > 0){
                        $playerData = $query->row_array();

                        return $playerData;
                }
                return false;
        }

        public function updatePlayer($playerData = array(), $playerId = 0){
                if ($playerId > 0){
                        if ($this->db->update('{CARO_PREFIX}players', $playerData, array('id' => $playerId)))
                                return $playerId;
                }
                else {
                        $this->db->insert('{CARO_PREFIX}players', $playerData);
                        if ($this->db->insert_id())
                                return $this->db->insert_id();
                }
                return 0;
        }

        public function getPlayerById($playerId, $items = "*"){
                $this->db->select($items);
                $query = $this->db->get_where('{CARO_PREFIX}players', array('id' => $playerId));
                if ($query->num_rows() > 0)
                        return $query->row_array();
                return false;
        }

        public function checkEmailExists($email, $playerId = 0){
                $this->db->select('email');
                $this->db->limit(1);
                $this->db->where('id !=', $playerId);
                $this->db->where('email', $email);
                if ($this->db->get('{CARO_PREFIX}players')->num_rows() > 0)
                        return true;
                return false;

        }

        public function checkPassword($playerId, $password){
                $this->db->select('password');
                $this->db->limit(1);
                if ($this->db->get_where('{CARO_PREFIX}players', array('id' => $playerId))->row_array()['password'] == md5(md5($password)))
                        return true;
                return false;
        }

}
