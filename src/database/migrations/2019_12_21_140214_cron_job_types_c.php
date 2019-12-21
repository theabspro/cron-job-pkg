<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CronJobTypesC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('cron_job_types')) {
			Schema::create('cron_job_types', function (Blueprint $table) {
				$table->increments('id');
				$table->string('name', 191);
				$table->string('description', 255)->nullable();
				$table->unsignedInteger('action_type_id');
				$table->string('command', 255);

				$table->foreign('action_type_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

				$table->unique(["name"]);
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('cron_job_types');
	}
}
