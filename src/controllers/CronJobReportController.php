<?php

namespace Abs\CronJobPkg;
use Abs\CronJobPkg\CronJob;
use Abs\CronJobPkg\CronJobReport;
use Abs\CronJobPkg\CronJobType;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class CronJobReportController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getCronJobReportFilter() {
		$this->data['cron_job_types'] = Collect(CronJobType::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Cron Job Type']);
		$this->data['cron_job_names'] = Collect(CronJob::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Cron Job']);
		$this->data['status'] = Collect(Config::select('id', 'name')->where('config_type_id', 24)->get())->prepend(['id' => '', 'name' => 'Select Status']);
		return response()->json($this->data);
	}

	public function getCronJobReportList(Request $request) {
		$cron_job_reports = CronJobReport::join('cron_jobs as cj', 'cj.id', '=', 'cron_job_reports.cron_job_id')
			->join('cron_job_types', 'cron_job_types.id', 'cj.type_id')
			->join('configs as frequency', 'frequency.id', 'cj.frequency_id')
			->leftJoin('configs as status', 'status.id', '=', 'cron_job_reports.status_id')
			->select([
				'cron_job_reports.id as id',
				'cron_job_types.name as type',
				'cj.name as cron_job',
				'cj.type_id',
				'cron_job_types.name as type_name',
				'frequency.name as frequency_name',
				DB::raw('DATE_FORMAT(cron_job_reports.started_at,"%d/%m/%Y %h:%i %p") as started_at'),
				DB::raw('IF(cron_job_reports.completed_at,DATE_FORMAT(cron_job_reports.completed_at,"%d/%m/%Y  %h:%i %p"),"-") as completed_at'),
				'status.name as status',
				'cron_job_reports.errors as errors',
				DB::raw("CONCAT(
   MOD(HOUR(TIMEDIFF(cron_job_reports.started_at, cron_job_reports.completed_at)), 24), 'h :',
   MINUTE(TIMEDIFF(cron_job_reports.started_at, cron_job_reports.completed_at)),'m'
   )
AS duration"),
				// 'cron_job_reports.error_log_file',
				'cron_job_reports.total_records',
				'cron_job_reports.processed_count',
				'cron_job_reports.success_count',
				'cron_job_reports.error_count',
				'cron_job_reports.new_count',
				'cron_job_reports.updated_count',
				// 'cron_job_reports.parameters'
			])
			->where('cj.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->type_id)) {
					$query->where('cj.type_id', $request->type_id);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->cron_job_id)) {
					$query->where('cron_job_reports.cron_job_id', $request->cron_job_id);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->status_id)) {
					$query->where('cron_job_reports.status_id', $request->status_id);
				}
			})
			->orderBy('cron_job_reports.started_at', 'DESC')
		;

		return Datatables::of($cron_job_reports)
			->addColumn('action', function ($cron_job_reports) {
				$error = asset('/public/themes/' . $this->data['theme'] . '/img/content/icons/error_normal.svg');
				$error_active = asset('/public/themes/' . $this->data['theme'] . '/img/content/icons/error_hover.svg');

				return
				'<a href="storage/public/cron-job-errors/' . $cron_job_reports->id . '.xls">
						<img src="' . $error . '" alt="Error_report" class="img-responsive" onmouseover=this.src="' . $error_active . '" onmouseout=this.src="' . $error . '" ></a>'
				;
			})
			->make(true);
	}
}
