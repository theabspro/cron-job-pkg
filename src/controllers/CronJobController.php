<?php

namespace Abs\CronJobPkg;
use Abs\CronJobPkg\CronJob;
use Abs\CronJobPkg\CronJobParameter;
use Abs\CronJobPkg\CronJobType;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class CronJobController extends Controller {

	public function __construct() {
	}

	public function getCronJobList(Request $request) {
		$cron_job_list = CronJob::withTrashed()
			->select(
				'cron_jobs.id',
				'cron_job_types.name as type',
				'cron_job_types.description',
				'configs.name as frequency',
				DB::raw('IF(cron_jobs.name IS NULL,"--",cron_jobs.name) as name'),
				DB::raw('IF(cron_jobs.allow_overlapping = 0,"No","Yes") as allow_overlapping'),
				DB::raw('IF(cron_jobs.run_in_background = 0,"No","Yes") as run_in_background'),
				DB::raw('IF((cron_jobs.deleted_at) IS NULL,"Active","Inactive") as status')
			)
			->leftJoin('cron_job_types', 'cron_job_types.id', 'cron_jobs.type_id')
			->leftJoin('configs', 'configs.id', 'cron_jobs.frequency_id')
			->where('cron_jobs.company_id', Auth::user()->company_id)
		// ->where(function ($query) use ($request) {
		// 	if (!empty($request->cron_job_code)) {
		// 		$query->where('cron_jobs.code', 'LIKE', '%' . $request->cron_job_code . '%');
		// 	}
		// })
		// ->where(function ($query) use ($request) {
		// 	if (!empty($request->cron_job_name)) {
		// 		$query->where('cron_jobs.name', 'LIKE', '%' . $request->cron_job_name . '%');
		// 	}
		// })
		// ->where(function ($query) use ($request) {
		// 	if (!empty($request->mobile_no)) {
		// 		$query->where('cron_jobs.mobile_no', 'LIKE', '%' . $request->mobile_no . '%');
		// 	}
		// })
		// ->where(function ($query) use ($request) {
		// 	if (!empty($request->email)) {
		// 		$query->where('cron_jobs.email', 'LIKE', '%' . $request->email . '%');
		// 	}
		// })
			->orderby('cron_jobs.id', 'desc');

		return Datatables::of($cron_job_list)
			->addColumn('name', function ($cron_job_list) {
				$status = $cron_job_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $cron_job_list->name;
			})
			->addColumn('action', function ($cron_job_list) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$view_img = asset('public/theme/img/table/cndn/view.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/cron-job-pkg/cron-job/edit/' . $cron_job_list->id . '">
						<img src="' . $edit_img . '" alt="Edit" class="img-responsive">
					</a>
					<a href="#!/cron-job-pkg/cron-job/view/' . $cron_job_list->id . '">
						<img src="' . $view_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_cron_job"
					onclick="angular.element(this).scope().deleteCronJob(' . $cron_job_list->id . ')" dusk = "delete-btn" title="Delete"><img src="' . $delete_img . '" alt="delete" class="img-responsive"></a>';
			})
			->make(true);
	}

	public function getCronJobFormData($id = NULL) {
		if (!$id) {
			$cron_job = new CronJob;
			$cron_job->cron_job_parameters = [];
			$action = 'Add';
		} else {
			$cron_job = CronJob::withTrashed()->where('id', $id)->with([
				'cronJobParameters',
			])
				->first();
			$action = 'Edit';
		}
		$this->data['cron_job_types'] = Collect(CronJobType::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Type']);
		$this->data['frequencies'] = Collect(Config::select('id', 'name')->where('config_type_id', 23)->get())->prepend(['id' => '', 'name' => 'Select Frequency']);
		$this->data['cron_job'] = $cron_job;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveCronJob(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'type_id.required' => 'CronJob Type is Required',
				'name.required' => 'CronJob Name is Required',
				'name.unique' => 'CronJob Name is already taken',
				'frequency_id.required' => 'CronJob Frequency is Required',
				'allow_overlapping.required' => 'Allow Over lapping is Required',
				'run_in_background.required' => 'Run in Background is Required',
			];
			$validator = Validator::make($request->all(), [
				'type_id' => 'required',
				'name' => [
					'unique:cron_jobs,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required',
				],
				'frequency_id' => 'required',
				'allow_overlapping' => 'required',
				'run_in_background' => 'required',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			//VALIDATE CRON-JOB-PARAMETERS
			if (isset($request->cron_job_parameters) && !empty($request->cron_job_parameters)) {
				$error_messages_1 = [
					'key.required' => 'Parameter Key is required',
					'value.required' => 'Parameter Value is required',
				];

				foreach ($request->cron_job_parameters as $cron_job_parameter_key => $cron_job_parameter) {
					$validator_1 = Validator::make($cron_job_parameter, [
						'key' => 'required',
						'value' => 'required',
					], $error_messages_1);

					if ($validator_1->fails()) {
						return response()->json(['success' => false, 'errors' => $validator_1->errors()->all()]);
					}
				}
			}

			DB::beginTransaction();
			if (!$request->id) {
				$cron_job = new CronJob;
				$cron_job->created_by_id = Auth::user()->id;
				$cron_job->created_at = Carbon::now();
				$cron_job->updated_at = NULL;
				$msg = "Saved";
			} else {
				$cron_job = CronJob::withTrashed()->find($request->id);
				$cron_job->updated_by_id = Auth::user()->id;
				$cron_job->updated_at = Carbon::now();
				$msg = "Updated";
			}
			$cron_job->fill($request->all());
			// $cron_job->parameters = json_encode($request->parameters);
			$cron_job->company_id = Auth::user()->company_id;
			if ($request->frequency_id == 1360) {
				//Custom
				$cron_job->frequency_command = $request->frequency_command;
			} else {
				$cron_job->frequency_command = NULL;
			}

			if ($request->allow_overlapping == 'Yes') {
				$cron_job->allow_overlapping = 1;
			} else {
				$cron_job->allow_overlapping = 0;
			}

			if ($request->run_in_background == 'Yes') {
				$cron_job->run_in_background = 1;
			} else {
				$cron_job->run_in_background = 0;
			}

			if ($request->status == 'Active') {
				$cron_job->deleted_by_id = NULL;
				$cron_job->deleted_at = NULL;
			} else {
				$cron_job->deleted_at = Carbon::now();
				$cron_job->deleted_by_id = Auth::user()->id;
			}
			$cron_job->save();

			//DELETE CRON-JOB-PARAMETERS
			if (!empty($request->cron_job_parameters_removal_ids)) {
				$cron_job_parameters_removal_ids = json_decode($request->cron_job_parameters_removal_ids, true);
				CronJobParameter::withTrashed()->whereIn('id', $cron_job_parameters_removal_ids)->forcedelete();
			}

			if (isset($request->cron_job_parameters) && !empty($request->cron_job_parameters)) {
				foreach ($request->cron_job_parameters as $key => $cron_job_parameter) {
					$cron_job_parameter_save = CronJobParameter::withTrashed()->firstOrNew(['id' => $cron_job_parameter['id']]);
					$cron_job_parameter_save->fill($cron_job_parameter);
					$cron_job_parameter_save->cron_job_id = $cron_job->id;
					if (empty($cron_job_parameter['id'])) {
						$cron_job_parameter_save->created_by_id = Auth::user()->id;
						$cron_job_parameter_save->updated_at = NULL;
					} else {
						$cron_job_parameter_save->updated_by_id = Auth::user()->id;
						$cron_job_parameter_save->updated_at = Carbon::now();
					}
					$cron_job_parameter_save->save();
				}
			}

			DB::commit();
			return response()->json(['success' => true, 'comes_from' => $msg]);
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function viewCronJob($id) {
		$this->data['cron_job'] = CronJob::withTrashed()->where('id', $id)->with([
			'type',
			'frequency',
			'cronJobParameters',
		])
			->first();
		$this->data['action'] = 'View';

		return response()->json($this->data);
	}

	public function deleteCronJob($id) {
		DB::beginTransaction();
		try {
			$delete_status = CronJob::withTrashed()->where('id', $id)->forceDelete();
			DB::commit();
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
