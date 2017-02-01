<?php  if ( ! defined('BASEPATH')) exit("No direct script access allowed");

class Migration_Init extends CI_Migration {

        public function up()
        {

		$player_fields = array(
				       'id' => array(
						     'type' => 'INT',
						     'constraint' => 11,
						     'auto_increment' => TRUE
						     ),
				       'name' => array(
						       'type' => 'VARCHAR',
						       'constraint' => '256'
						       ),
				       'email' => array(
							'type' =>'VARCHAR',
							'constraint' => '256'
							),
				       'password' => array(
							   'type' => 'VARCHAR',
							   'constraint' => '256'
							   ),
				       'avatar' => array(
							 'type' => 'VARCHAR',
							 'constraint' => '256'
							 ),
				       'dob' => array(
						      'type' => 'DATE',
						      'null' => TRUE
						      ),
				       'played' => array(
							 'type' => 'BIGINT',
							 'constraint' => 255,
							 'default' => 0
							 ),
				       'wins' => array(
						       'type' => 'BIGINT',
						       'constraint' => 255,
						       'default' => 0
						       ),

				       'draws' => array(
							'type' => 'BIGINT',
							'constraint' => 255,
							'default' => 0
							),
				       'points' => array(
							 'type' => 'BIGINT',
							 'constraint' => 255,
							 'default' => 0
							 ),
				       'money' => array(
							'type' => 'BIGINT',
							'constraint' => 255,
							'default' => 10000
							),
				       'lastActivity' => array(
							       'type' => 'TIMESTAMP'
							       )
				       );

                $this->dbforge->add_field($player_fields);
                $this->dbforge->add_key('id', TRUE);
                $this->dbforge->create_table('players');

		$emptyMatrix = array();
		for ($i = 0; $i < 15; $i++){
			for ($j = 0; $j < 15; $j++){
				$emptyMatrix[$i][$j] = 0;
			}
		}
		$emptyMoves = json_encode($emptyMatrix);

		$game_fields = array(
				     'gameId' => array(
						       'type' => 'INT',
						       'constraint' => 11,
						       'auto_increment' => TRUE
						       ),
				     'player1Id' => array(
							  'type' => 'INT',
							  'constraint' => 11,
							  'default' => 0
							  ),
				     'player2Id' => array(
							  'type' => 'INT',
							  'constraint' => 11,
							  'default' => 0
							  ),
				     'player1Ready' => array(
							     'type' => 'SMALLINT',
							     'constraint' => 1,
							     'default' => 0
							     ),
				     'player2Ready' => array(
							     'type' => 'SMALLINT',
							     'constraint' => 1,
							     'default' => 0
							     ),
				     'player1Wins' => array(
							    'type' => 'INT',
							    'constraint' => 11,
							    'default' => 0
							    ),
				     'player2Wins' => array(
							    'type' => 'INT',
							    'constraint' => 11,
							    'default' => 0
							    ),
				     'draws' => array(
						      'type' => 'INT',
						      'constraint' => 11,
						      'default' => 0
						      ),
				     'turn' => array(
						     'type' => 'SMALLINT',
						     'constraint' => 1,
						     'default' => 1
						     ),
				     'moves' => array(
						      'type' => 'VARCHAR',
						      'constraint' => ''.strlen($emptyMoves).'',
						      'default' => $emptyMoves
						      ),
				     'lastMove' => array(
							 'type' =>'TIMESTAMP'
							 ),
				     'status' => array(
						       'type' => 'SMALLINT',
						       'constraint' => 1,
						       'default' => 0
						       ),
				     'startTime' => array(
							  'type' => 'DATETIME'
							  ),
				     'bet' => array(
						    'type' => 'INT',
						    'constraint' => 10,
						    'default' => 10000
						    ),
				     );

                $this->dbforge->add_field($game_fields);

                $this->dbforge->add_key('gameId', TRUE);
                $this->dbforge->create_table('games');

		//$this->db->query("ALTER TABLE {CARO_PREFIX}games ADD FOREIGN KEY (player1Id) REFERENCES {CARO_PREFIX}players(id) ON UPDATE CASCADE ON DELETE CASCADE");
		//$this->db->query("ALTER TABLE {CARO_PREFIX}games ADD FOREIGN KEY (player2Id) REFERENCES {CARO_PREFIX}players(id) ON UPDATE CASCADE ON DELETE CASCADE");

		$record_fields = array(
				       'recordId' => array(
							   'type' => 'BIGINT',
							   'constraint' => 255,
							   'auto_increment' => TRUE
							   ),
				       'player1Id' => array(
							    'type' => 'INT',
							    'constraint' => 11,
							    'default' => 0
							    ),
				       'player2Id' => array(
							    'type' => 'INT',
							    'constraint' => 11,
							    'default' => 0
							    ),
				       'winner' => array(
							 'type' => 'SMALLINT',
							 'constraint' => 1,
							 'default' => 0
							 ),
				       'turn' => array(
						       'type' => 'SMALLINT',
						       'constraint' => 1,
						       'default' => 1
						       ),
				       'moves' => array(
							'type' => 'VARCHAR',
							'constraint' => ''.strlen($emptyMoves).'',
							'default' => $emptyMoves
							),
				       'startTime' => array(
							    'type' => 'DATETIME'
							    ),
				       'endTime' => array(
							  'type' => 'DATETIME'
							  ),
				       'bet' => array(
						      'type' => 'INT',
						      'constraint' => 10,
						      'default' => 10000
						      ),
				       );

                $this->dbforge->add_field($record_fields);
                $this->dbforge->add_key('recordId', TRUE);
                $this->dbforge->create_table('records');

		//$this->db->query("ALTER TABLE {CARO_PREFIX}records ADD FOREIGN KEY (player1Id) REFERENCES {CARO_PREFIX}players(id) ON UPDATE CASCADE ON DELETE CASCADE");
		//$this->db->query("ALTER TABLE {CARO_PREFIX}records ADD FOREIGN KEY (player2Id) REFERENCES {CARO_PREFIX}players(id) ON UPDATE CASCADE ON DELETE CASCADE");

        }

        public function down()
        {
                $this->dbforge->drop_table('games');
                $this->dbforge->drop_table('records');
                $this->dbforge->drop_table('players');
        }
}