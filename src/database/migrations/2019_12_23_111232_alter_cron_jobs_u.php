<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCronJobsU extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('cron_jobs', function (Blueprint $table) {
			$table->renameColumn('description', 'name');

			$table->unique(["company_id", "name"]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('cron_jobs', function (Blueprint $table) {
			$table->dropUnique('cron_jobs_company_id_name_unique');
			$table->rename("name", "description");
		});
	}
}
