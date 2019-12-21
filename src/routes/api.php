<?php
Route::group(['namespace' => 'Abs\CronJobPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'cron_job-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});