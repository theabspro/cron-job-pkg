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
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect) {
        // $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            cron_job_filter_data_url
        ).then(function(response) {
            self.cron_job_types = response.data.cron_job_types;
            self.frequencies = response.data.frequencies;
            self.allow_overlapping_filter = response.data.allow_overlapping_filter;
            self.run_in_background_filter = response.data.run_in_background_filter;
        });
        var dataTable;
        setTimeout(function() {
            var table_scroll;
            table_scroll = $('.page-main-content').height() - 37;
            dataTable = $('#cron_jobs_list').DataTable({
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
                        d.cron_job_name = $('#cron_job_name').val();
                        d.type_id = $('#type_id').val();
                        d.frequency_id = $('#frequency_id').val();
                        d.allow_overlapping = $('#allow_overlapping').val();
                        d.run_in_background = $('#run_in_background').val();
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
        },1000);
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });
        $('.refresh_table').on("click", function() {
            $('#cron_jobs_list').DataTable().ajax.reload();
        });
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
        $('#cron_job_name').on('keyup', function() {
            setTimeout(function() {
                dataTable.draw();
            }, 900);
        });
        $scope.onSelectedtype = function(selected_type) {
            setTimeout(function() {
                $('#type_id').val(selected_type);
                dataTable.draw();
            }, 900);
        }
        $scope.onSelectedFrequency = function(selected_frequency_id) {
            setTimeout(function() {
                $('#frequency_id').val(selected_frequency_id);
                dataTable.draw();
            }, 900);
        }
        $scope.onSelectedOverlapping = function(selected_allow_overlapping_id) {
            setTimeout(function() {
                $('#allow_overlapping').val(selected_allow_overlapping_id);
                dataTable.draw();
            }, 900);
        }
        $scope.onSelectedRunInBackground = function(selected_run_in_background_id) {
            setTimeout(function() {
                $('#run_in_background').val(selected_run_in_background_id);
                dataTable.draw();
            }, 900);
        }
        $scope.reset_filter = function() {
            $("#cron_job_name").val('');
            $("#type_id").val('');
            $("#frequency_id").val('');
            $("#allow_overlapping").val('');
            $("#run_in_background").val('');
            dataTable.draw();
        }

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
            // console.log(response.data);
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

//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
/*Cron Job Report*/
app.component('cronJobReportList', {
    templateUrl: cron_job_report_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect) {
        // $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            cron_job_report_filter_data_url
        ).then(function(response) {
            self.cron_job_types = response.data.cron_job_types;
            self.cron_job_names = response.data.cron_job_names;
            self.status = response.data.status;
        });
        var dataTable;
        setTimeout(function() {
            var table_scroll;
            table_scroll = $('.page-main-content').height() - 37;
            dataTable = $('#cron_job_report_list').DataTable({
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
                    url: laravel_routes['getCronJobReportList'],
                    type: "GET",
                    dataType: "json",
                    data: function(d) {
                        d.type_id = $('#type_id').val();
                        d.cron_job_id = $('#cron_job_id').val();
                        d.status_id = $('#status_id').val();
                    },
                },

                columns: [
                    { data: 'action', class: 'action', name: 'action', searchable: false },
                    { data: 'type', name: 'cron_job_types.name', searchable: true },
                    { data: 'cron_job', name: 'cj.name', searchable: true },
                    { data: 'frequency_name', name: 'frequency.name', searchable: true },
                    { data: 'started_at', name: 'cron_job_reports.started_at', searchable: false },
                    { data: 'completed_at', name: 'cron_job_reports.completed_at', searchable: false },
                    { data: 'status', name: 'status.name', searchable: true },
                    { data: 'errors', name: 'cron_job_reports.errors', searchable: false },
                    { data: 'duration', searchable: false },
                    { data: 'total_records', name: 'cron_job_reports.total_records', searchable: false },
                    { data: 'processed_count', name: 'cron_job_reports.processed_count', searchable: false },
                    { data: 'success_count', name: 'cron_job_reports.success_count', searchable: false },
                    { data: 'error_count', name: 'cron_job_reports.error_count', searchable: false },
                    { data: 'new_count', name: 'cron_job_reports.new_count', searchable: false },
                    { data: 'updated_count', name: 'cron_job_reports.updated_count', searchable: false },
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
        },1000);
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });
        $('.refresh_table').on("click", function() {
            $('#cron_job_report_list').DataTable().ajax.reload();
        });
        $scope.clear_search = function() {
            $('#search_cron_job').val('');
            $('#cron_job_report_list').DataTable().search('').draw();
        }
        $("#search_cron_job").keyup(function() {
            dataTable
                .search(this.value)
                .draw();
        });

        //FOR FILTER
        $scope.onSelectedtype = function(selected_type) {
            setTimeout(function() {
                $('#type_id').val(selected_type);
                dataTable.draw();
            }, 900);
        }
        $scope.onSelectedCronJob = function(selected_cron_job) {
            setTimeout(function() {
                $('#cron_job_id').val(selected_cron_job);
                dataTable.draw();
            }, 900);
        }
        $scope.onSelectedStatus = function(selected_status) {
            setTimeout(function() {
                $('#status_id').val(selected_status);
                dataTable.draw();
            }, 900);
        }
        $scope.reset_filter = function() {
            $("#type_id").val('');
            $("#cron_job_id").val('');
            $("#status_id").val('');
            dataTable.draw();
        }

        $rootScope.loading = false;
    }
});