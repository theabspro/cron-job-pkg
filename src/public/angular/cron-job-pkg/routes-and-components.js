app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //CRON JOB
    when('/cron-job-pkg/cron-job/list', {
        template: '<cron-job-list></cron-job-list>',
        title: 'Cron Jobs',
    }).
    when('/cron-job-pkg/cron-job/add', {
        template: '<cron-job-form></cron-job-form>',
        title: 'Add Cron Job',
    }).
    when('/cron-job-pkg/cron-job/edit/:id', {
        template: '<cron-job-form></cron-job-form>',
        title: 'Edit Cron Job',
    }).
    when('/cron-job-pkg/cron-job/view/:id', {
        template: '<cron-job-view></cron-job-view>',
        title: 'View Cron Job',
    }).

    //CRON JOB REPORT
    when('/cron-job-pkg/cron-job-report/list', {
        template: '<cron-job-report-list></cron-job-report-list>',
        title: 'Cron Job Reports',
    });
}]);

app.component('cronJobList', {
    templateUrl: cron_job_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        // $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var table_scroll;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#cron_jobs_list').DataTable({
            "dom": cndn_dom_structure,
            "language": {
                // "search": "",
                // "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            pageLength: 10,
            processing: true,
            stateSaveCallback: function(settings, data) {
                localStorage.setItem('CDataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function(settings) {
                var state_save_val = JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                if (state_save_val) {
                    $('#search_cron_job').val(state_save_val.search.search);
                }
                return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
            },
            serverSide: true,
            paging: true,
            stateSave: true,
            ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getCronJobList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    // d.cron_job_code = $('#cron_job_code').val();
                    // d.cron_job_name = $('#cron_job_name').val();
                    // d.mobile_no = $('#mobile_no').val();
                    // d.email = $('#email').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'type', name: 'cron_job_types.name', searchable: true },
                { data: 'description', name: 'cron_job_types.description', searchable: true },
                { data: 'name', name: 'cron_jobs.name', searchable: true },
                { data: 'frequency', name: 'configs.name', searchable: true },
                { data: 'allow_overlapping', name: 'cron_jobs.allow_overlapping', searchable: false },
                { data: 'run_in_background', name: 'cron_jobs.run_in_background', searchable: false },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_cron_job').val('');
            $('#cron_jobs_list').DataTable().search('').draw();
        }
        $("#search_cron_job").keyup(function() {
            dataTable
                .search(this.value)
                .draw();
        });

        //DELETE
        $scope.deleteCronJob = function($id) {
            $('#cron_job_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#cron_job_id').val();
            $http.get(
                cron_job_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'CronJob Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#cron_jobs_list').DataTable().ajax.reload();
                    //$scope.$apply();
                    // $('#cron_jobs_list').DataTable().ajax.reload(function(json) {});
                    // $location.path('/cron-job-pkg/cron-job/list');
                }
            });
        }

        //FOR FILTER
        // $('#cron_job_code').on('keyup', function() {
        //     dataTables.fnFilter();
        // });
        // $('#cron_job_name').on('keyup', function() {
        //     dataTables.fnFilter();
        // });
        // $('#mobile_no').on('keyup', function() {
        //     dataTables.fnFilter();
        // });
        // $('#email').on('keyup', function() {
        //     dataTables.fnFilter();
        // });
        // $scope.reset_filter = function() {
        //     $("#cron_job_name").val('');
        //     $("#cron_job_code").val('');
        //     $("#mobile_no").val('');
        //     $("#email").val('');
        //     dataTables.fnFilter();
        // }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('cronJobForm', {
    templateUrl: cron_job_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? cron_job_get_form_data_url : cron_job_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
            console.log(response.data);
            self.cron_job = response.data.cron_job;
            self.cron_job_types = response.data.cron_job_types;
            self.frequencies = response.data.frequencies;
            self.action = response.data.action;
            self.cron_job_parameters_removal_ids = [];
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.cron_job.deleted_at == null) {
                    self.switch_value = 'Active';
                } else {
                    self.switch_value = 'Inactive';
                }
                if(self.cron_job.allow_overlapping == '1' || self.cron_job.allow_overlapping == 1) {
                    self.allow_overlapping = 'Yes';
                } else {
                    self.allow_overlapping = 'No';
                }
                if(self.cron_job.run_in_background == '1' || self.cron_job.run_in_background == 1) {
                    self.run_in_background   = 'Yes';
                } else {
                    self.run_in_background   = 'No';
                }
            } else {
                self.switch_value = 'Active';
                self.allow_overlapping = 'No';
                self.run_in_background   = 'No';
            }
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });

        self.addNewParameter = function() {
            self.cron_job.cron_job_parameters.push({
                key: '',
                value:'',
            });
        }

        self.removeParameter = function(index, cron_job_parameter_id) {
            if(cron_job_parameter_id) {
                self.cron_job_parameters_removal_ids.push(cron_job_parameter_id);
                $('#cron_job_parameters_removal_ids').val(JSON.stringify(self.cron_job_parameters_removal_ids));
            }
            self.cron_job.cron_job_parameters.splice(index, 1);
        }

        var form_id = '#form';
        $.validator.addClassRules({
            cron_job_parameter: {
                required:true,
            },
            cron_job_custom: {
                required:true,
            },
        });
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'type_id': {
                    required: true,
                },
                'name': {
                    required: true,
                },
                'frequency_id': {
                    required: true,
                },
                'allow_overlapping': {
                    required: true,
                },
                'run_in_background': {
                    required: true,
                },
                'status': {
                    required: true,
                },
            },
            invalidHandler: function(event, validator) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs'
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 3000)
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveCronJob'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'CronJob Details ' + res.comes_from + ' Successfully',
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $location.path('/cron-job-pkg/cron-job/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                $noty = new Noty({
                                    type: 'error',
                                    layout: 'topRight',
                                    text: errors
                                }).show();
                                setTimeout(function() {
                                    $noty.close();
                                }, 3000);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/cron-job-pkg/cron-job/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                        }).show();
                        setTimeout(function() {
                            $noty.close();
                        }, 3000);
                    });
            }
        });
    }
});

//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('cronJobView', {
    templateUrl: cron_job_view_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_view_data_url = typeof($routeParams.id) == 'undefined' ? cron_job_view_data_url : cron_job_view_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_view_data_url
        ).then(function(response) {
            // console.log(response.data);
            self.cron_job = response.data.cron_job;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.cron_job.deleted_at == null) {
                self.switch_value = 'Active';
            } else {
                self.switch_value = 'Inactive';
            }
            if(self.cron_job.allow_overlapping == '1' || self.cron_job.allow_overlapping == 1) {
                self.allow_overlapping = 'Yes';
            } else {
                self.allow_overlapping = 'No';
            }
            if(self.cron_job.run_in_background == '1' || self.cron_job.run_in_background == 1) {
                self.run_in_background   = 'Yes';
            } else {
                self.run_in_background   = 'No';
            }
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
    }
});