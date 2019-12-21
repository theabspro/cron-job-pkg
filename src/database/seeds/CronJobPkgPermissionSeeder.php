<?php
namespace Abs\CronJobPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class CronJobPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [

			//Cron Jobs
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'cron-jobs',
				'display_name' => 'Cron Jobs',
				'operation' => 'add/update',
			],
			[
				'display_order' => 1,
				'parent' => 'cron-jobs',
				'name' => 'add-cron-jobs',
				'display_name' => 'Add',
				'operation' => 'add/update',
			],
			[
				'display_order' => 2,
				'parent' => 'cron-jobs',
				'name' => 'edit-cron-jobs',
				'display_name' => 'Edit',
				'operation' => 'add/update',
			],
			[
				'display_order' => 3,
				'parent' => 'cron-jobs',
				'name' => 'delete-cron-jobs',
				'display_name' => 'Delete',
				'operation' => 'add/update',
			],

			//Cron Job Reports
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'cron-job-reports',
				'display_name' => 'Cron Job Reports',
				'operation' => 'add/update',
			],

		];
		Permission::createFromArrays($permissions);

	}
}