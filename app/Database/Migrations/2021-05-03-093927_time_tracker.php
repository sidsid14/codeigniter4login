<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TimeTracker extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'          => [
				'type'           => 'INT',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'user_id'          => [
				'type'           => 'INT',
				'constraint'     => 11,
				'unsigned'       => true,
			],
			'tracker_date'       => [
				'type'       => 'DATE',
			],
			'action_list' => [
				'type' => 'LONGTEXT',
				'null' => true,
			],
			'created_at datetime default current_timestamp',
			'updated_at datetime default current_timestamp on update current_timestamp',
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('docsgo-time-tracker');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('docsgo-time-tracker');
	}
}
