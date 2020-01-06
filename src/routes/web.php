<?php

Route::group(['namespace' => 'Abs\CronJobPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'cron-job-pkg'], function () {
	Route::get('/cron-jobs/get-list', 'CronJobController@getCronJobList')->name('getCronJobList');
	Route::get('/cron-job/get-form-data/{id?}', 'CronJobController@getCronJobFormData')->name('getCronJobFormData');
	Route::post('/cron-job/save', 'CronJobController@saveCronJob')->name('saveCronJob');
	Route::get('/cron-job/delete/{id}', 'CronJobController@deleteCronJob')->name('deleteCronJob');
	Route::get('/cron-job/view/{id}', 'CronJobController@viewCronJob')->name('viewCronJob');
	Route::get('/cron-job/filter', 'CronJobController@getCronJobFilter')->name('getCronJobFilter');
});