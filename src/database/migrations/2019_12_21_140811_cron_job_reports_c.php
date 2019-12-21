<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CronJobReportsC extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		if (!Schema::hasTable('cron_job_reports')) {
			Schema::create('cron_job_reports', function (Blueprint $table) {
				$table->increments('id');
				$table->unsignedInteger('cron_job_id');
				$table->unsignedInteger('total_records');
				$table->unsignedInteger('processed_count')->nullable();
				$table->unsignedInteger('success_count')->nullable();
				$table->unsignedInteger('error_count')->nullable();
				$table->unsignedInteger('new_count')->nullable();
				$table->unsignedInteger('updated_count')->nullable();
				$table->text('parameters')->nullable();
				$table->text('errors')->nullable();

				$table->datetime('started_at');
				$table->datetime('completed_at')->nullable();
				$table->unsignedInteger('status_id')->nullable();

				$table->foreign('cron_job_id')->references('id')->on('cron_jobs')->onDelete('cascade')->onUpdate('cascade');

				$table->foreign('status_id')->references('id')->on('configs')->onDelete('cascade')->onUpdate('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('cron_job_reports');
	}
}
