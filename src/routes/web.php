<?php

Route::group(['namespace' => 'Abs\CronJobPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'cron_job-pkg'], function () {
	Route::get('/cron_jobs/get-list', 'CronJobController@getCronJobList')->name('getCronJobList');
	Route::get('/cron_job/get-form-data/{id?}', 'CronJobController@getCronJobFormData')->name('getCronJobFormData');
	Route::post('/cron_job/save', 'CronJobController@saveCronJob')->name('saveCronJob');
	Route::get('/cron_job/delete/{id}', 'CronJobController@deleteCronJob')->name('deleteCronJob');

});