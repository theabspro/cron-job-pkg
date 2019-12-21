<?php

namespace Abs\CronJobPkg;
use Abs\CronJobPkg\CronJob;
use App\Address;
use App\Country;
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
				'cron_jobs.code',
				'cron_jobs.name',
				DB::raw('IF(cron_jobs.mobile_no IS NULL,"--",cron_jobs.mobile_no) as mobile_no'),
				DB::raw('IF(cron_jobs.email IS NULL,"--",cron_jobs.email) as email'),
				DB::raw('IF(cron_jobs.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('cron_jobs.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->cron_job_code)) {
					$query->where('cron_jobs.code', 'LIKE', '%' . $request->cron_job_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->cron_job_name)) {
					$query->where('cron_jobs.name', 'LIKE', '%' . $request->cron_job_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->mobile_no)) {
					$query->where('cron_jobs.mobile_no', 'LIKE', '%' . $request->mobile_no . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->email)) {
					$query->where('cron_jobs.email', 'LIKE', '%' . $request->email . '%');
				}
			})
			->orderby('cron_jobs.id', 'desc');

		return Datatables::of($cron_job_list)
			->addColumn('code', function ($cron_job_list) {
				$status = $cron_job_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $cron_job_list->code;
			})
			->addColumn('action', function ($cron_job_list) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/cron_job-pkg/cron_job/edit/' . $cron_job_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_cron_job"
					onclick="angular.element(this).scope().deleteCronJob(' . $cron_job_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getCronJobFormData($id = NULL) {
		if (!$id) {
			$cron_job = new CronJob;
			$address = new Address;
			$action = 'Add';
		} else {
			$cron_job = CronJob::withTrashed()->find($id);
			$address = Address::where('address_of_id', 24)->where('entity_id', $id)->first();
			if (!$address) {
				$address = new Address;
			}
			$action = 'Edit';
		}
		$this->data['country_list'] = $country_list = Collect(Country::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Country']);
		$this->data['cron_job'] = $cron_job;
		$this->data['address'] = $address;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveCronJob(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'CronJob Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'name.required' => 'CronJob Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
				'gst_number.required' => 'GST Number is Required',
				'gst_number.max' => 'Maximum 191 Numbers',
				'mobile_no.max' => 'Maximum 25 Numbers',
				// 'email.required' => 'Email is Required',
				'address_line1.required' => 'Address Line 1 is Required',
				'address_line1.max' => 'Maximum 255 Characters',
				'address_line1.min' => 'Minimum 3 Characters',
				'address_line2.max' => 'Maximum 255 Characters',
				'pincode.required' => 'Pincode is Required',
				'pincode.max' => 'Maximum 6 Characters',
				'pincode.min' => 'Minimum 6 Characters',
			];
			$validator = Validator::make($request->all(), [
				'code' => 'required|max:255|min:3',
				'name' => 'required|max:255|min:3',
				'gst_number' => 'required|max:191',
				'mobile_no' => 'nullable|max:25',
				// 'email' => 'nullable',
				'address_line1' => 'required|max:255|min:3',
				'address_line2' => 'max:255',
				'pincode' => 'required|max:6|min:6',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$cron_job = new CronJob;
				$cron_job->created_by_id = Auth::user()->id;
				$cron_job->created_at = Carbon::now();
				$cron_job->updated_at = NULL;
				$address = new Address;
			} else {
				$cron_job = CronJob::withTrashed()->find($request->id);
				$cron_job->updated_by_id = Auth::user()->id;
				$cron_job->updated_at = Carbon::now();
				$address = Address::where('address_of_id', 24)->where('entity_id', $request->id)->first();
			}
			$cron_job->fill($request->all());
			$cron_job->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$cron_job->deleted_at = Carbon::now();
				$cron_job->deleted_by_id = Auth::user()->id;
			} else {
				$cron_job->deleted_by_id = NULL;
				$cron_job->deleted_at = NULL;
			}
			$cron_job->gst_number = $request->gst_number;
			$cron_job->save();

			if (!$address) {
				$address = new Address;
			}
			$address->fill($request->all());
			$address->company_id = Auth::user()->company_id;
			$address->address_of_id = 24;
			$address->entity_id = $cron_job->id;
			$address->address_type_id = 40;
			$address->name = 'Primary Address';
			$address->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['CronJob Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['CronJob Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteCronJob($id) {
		$delete_status = CronJob::withTrashed()->where('id', $id)->forceDelete();
		if ($delete_status) {
			$address_delete = Address::where('address_of_id', 24)->where('entity_id', $id)->forceDelete();
			return response()->json(['success' => true]);
		}
	}
}
