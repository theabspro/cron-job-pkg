<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CronJobsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('cron_jobs')) {
			Schema::create('cron_jobs', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('company_id');
				$table->unsignedInteger('type_id');
				$table->string('description', 191)->nullable();
				$table->unsignedInteger('frequency_id');
				$table->string('frequency_command', 255)->nullable();
				$table->text('parameters', 255)->nullable();
				$table->boolean('allow_overlapping');
				$table->boolean('run_in_background');
				$table->unsignedInteger('created_by_id')->nullable();
				$table->unsignedInteger('updated_by_id')->nullable();
				$table->unsignedInteger('deleted_by_id')->nullable();
				$table->timestamps();
				$table->softDeletes();

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
				$table->foreign('type_id')->references('id')->on('cron_job_types')->onDelete('cascade')->onUpdate('cascade');

				$table->foreign('frequency_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

				$table->foreign('created_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('updated_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
				$table->foreign('deleted_by_id')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');

			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('cron_jobs');
	}
}
