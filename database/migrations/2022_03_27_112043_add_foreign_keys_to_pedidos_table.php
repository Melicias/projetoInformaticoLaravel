<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPedidosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('pedidos', function(Blueprint $table)
		{
			$table->foreign('idUtilizador', 'idUtilizador_')->references('id')->on('utilizador')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('pedidos', function(Blueprint $table)
		{
			$table->dropForeign('idUtilizador_');
		});
	}

}