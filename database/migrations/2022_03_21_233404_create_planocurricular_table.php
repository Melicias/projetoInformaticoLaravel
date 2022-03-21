<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanocurricularTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('planocurricular', function(Blueprint $table)
		{
			$table->integer('id')->primary();
			$table->string('codigo', 45)->nullable();
			$table->integer('idCurso')->index('idCurso_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('planocurricular');
	}

}
