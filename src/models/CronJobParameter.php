<?php

namespace Abs\CronJobPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CronJobParameter extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'cron_job_parameters';
	protected $fillable = [
		'cron_job_id',
		'key',
		'value',
	];

	public function cronJob() {
		return $this->belongsTo('Abs\CronJobPkg\CronJob', 'cron_job_id');
	}

}
