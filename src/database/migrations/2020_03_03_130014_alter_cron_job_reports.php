<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCronJobReports extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('cron_job_reports', function (Blueprint $table) {
			$table->unsignedInteger('frequency_id')->nullable()->after('cron_job_id');
			$table->string('frequency_command', 255)->nullable()->after('frequency_id');
			// $table->text('parameters', 255)->nullable()->after('frequency_command');
			$table->boolean('allow_overlapping')->after('parameters');
			$table->boolean('run_in_background')->after('allow_overlapping');

			$table->foreign('frequency_id')->references('id')->on('configs')->onDelete('CASCADE')->onUpdate('cascade');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('cron_job_reports', function (Blueprint $table) {
			$table->dropForeign('cron_job_reports_frequency_id_foreign');
			$table->dropColumn('frequency_id');
			$table->dropColumn('frequency_command');
			// $table->dropColumn('parameters');
			$table->dropColumn('allow_overlapping');
			$table->dropColumn('run_in_background');
		});
	}
}
