<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DocsgoQueue extends Migration
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
			'type'          => [
				'type'           => 'VARCHAR',
				'constraint'     => 64,
				'null'			=> false,
			],
			'status'       => [
				'type'           => 'ENUM("SUBMITTED", "PROCESSING", "SUCCESS", "FAILED")',
				'default'           => 'SUBMITTED',
				'null'			=> false,
			],
			'json' => [
				'type' => 'LONGTEXT',
				'null' => false,
			],
			'created_at datetime default current_timestamp',
			'updated_at datetime default current_timestamp on update current_timestamp',
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('docsgo-queue');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('docsgo-queue');
	}
}
