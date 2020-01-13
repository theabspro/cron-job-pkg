@if(config('custom.PKG_DEV'))
    <?php $cron_job_pkg_prefix = '/packages/abs/cron-job-pkg/src';?>
@else
    <?php $cron_job_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var cron_job_list_template_url = "{{URL::asset($cron_job_pkg_prefix.'/public/angular/cron-job-pkg/pages/cron-job/list.html')}}";
    var cron_job_form_template_url = "{{URL::asset($cron_job_pkg_prefix.'/public/angular/cron-job-pkg/pages/cron-job/form.html')}}";
    var cron_job_view_template_url = "{{URL::asset($cron_job_pkg_prefix.'/public/angular/cron-job-pkg/pages/cron-job/view.html')}}";
    var cron_job_get_form_data_url = "{{url('cron-job-pkg/cron-job/get-form-data/')}}";
    var cron_job_delete_data_url = "{{url('cron-job-pkg/cron-job/delete/')}}";
    var cron_job_view_data_url = "{{url('cron-job-pkg/cron-job/view/')}}";
    var cron_job_filter_data_url = "{{route('getCronJobFilter')}}";
</script>
<script type="text/javascript" src="{{URL::asset($cron_job_pkg_prefix.'/public/angular/cron-job-pkg/routes-and-components.js')}}"></script>
