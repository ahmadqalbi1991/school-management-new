(function($) {
    'use strict';
    // permission table
    function searchClassList() {
        var table = $('#class_list_table').DataTable();
        table.destroy();
        var searchable = [];
        var selectable = [];
        var token = $('#token').val();

        var role = $('meta[name="role"]').attr('content');
        let columns = [
            {data:'admission_number', name: 'admission_number'},
            {data:'learner', name: 'learner'},
            {data:'grade', name: 'grade'},
            {data:'stream', name: 'stream'},
        ];
        if (role === 'super_admin') {
            columns = [
                {data:'admission_number', name: 'admission_number'},
                {data:'learner', name: 'learner'},
                {data:'school', name: 'school'},
                {data:'grade', name: 'grade'},
                {data:'stream', name: 'stream'},
            ]
        }

        $('#class_list_table').DataTable({
            order: [],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            responsive: false,
            serverSide: true,
            processing: true,
            language: {
                processing: '<i class="ace-icon fa fa-spinner fa-spin orange bigger-500" style="font-size:60px;margin-top:50px;"></i>'
            },
            scroller: {
                loadingIndicator: false
            },
            pagingType: "full_numbers",
            dom: "<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>tipr",
            ajax: {
                url: '/learners/get-class-list?class_id=' + $('#class_id').val() + '&stream_id=' + $('#stream-id').val(),
                type: "get",
                headers: {
                    'X-CSRF-TOKEN': token
                }
            },
            columns: columns,
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn-sm btn-info',
                    title: 'Learners',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    className: 'btn-sm btn-success',
                    title: 'Learners',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    className: 'btn-sm btn-warning',
                    title: 'Learners',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible',
                    }
                },
                {
                    extend: 'pdf',
                    className: 'btn-sm btn-primary',
                    title: 'Learners',
                    pageSize: 'A2',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    className: 'btn-sm btn-default',
                    title: 'Learners',
                    // orientation:'landscape',
                    pageSize: 'A2',
                    header: true,
                    footer: false,
                    orientation: 'landscape',
                    exportOptions: {
                        // columns: ':visible',
                        stripHtml: false
                    }
                }
            ],
            /*
             * create an element id to change permission names, while inline datatable updated
            */
            createdRow: function ( row, data, index ) {
                var td_index = data.DT_RowIndex;
                $('td', row).eq(0).attr('id', 'perm_'+data.id);
                $('td', row).eq(0).attr('title', 'Click to edit permission');
            },
            initComplete: function () {
                var api =  this.api();
                api.columns(searchable).every(function () {
                    var column = this;
                    var input = document.createElement("input");
                    input.setAttribute('placeholder', $(column.header()).text());
                    input.setAttribute('style', 'width: 140px; height:25px; border:1px solid whitesmoke;');

                    $(input).appendTo($(column.header()).empty())
                        .on('keyup', function () {
                            column.search($(this).val(), false, false, true).draw();
                        });

                    $('input', this.column(column).header()).on('click', function(e) {
                        e.stopPropagation();
                    });
                });

                api.columns(selectable).every( function (i, x) {
                    var column = this;

                    var select = $('<select style="width: 140px; height:25px; border:1px solid whitesmoke; font-size: 12px; font-weight:bold;"><option value="">'+$(column.header()).text()+'</option></select>')
                        .appendTo($(column.header()).empty())
                        .on('change', function(e){
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column.search(val ? '^'+val+'$' : '', true, false ).draw();
                            e.stopPropagation();
                        });

                    $.each(dropdownList[i], function(j, v) {
                        select.append('<option value="'+v+'">'+v+'</option>')
                    });
                });
            }
        });
    }
    $(document).ready(function()
    {
        var searchable = [];
        var selectable = [];
        var token = $('#token').val();

        var dTable = $('#learners_table').DataTable({

            order: [],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            responsive: false,
            serverSide: true,
            processing: true,
            language: {
                processing: '<i class="ace-icon fa fa-spinner fa-spin orange bigger-500" style="font-size:60px;margin-top:50px;"></i>'
            },
            scroller: {
                loadingIndicator: false
            },
            pagingType: "full_numbers",
            dom: "<'row'<'col-sm-2'l><'col-sm-7 text-center'B><'col-sm-3'f>>tipr",
            ajax: {
                url: '/learners/get-list',
                type: "get",
                headers: {
                    'X-CSRF-TOKEN': token
                }
            },
            columns: [
                {data:'name', name: 'name', orderable: false},
                {data:'school', name: 'school'},
                {data:'grade', name: 'grade'},
                {data:'stream', name: 'stream'},
                {data:'status', name: 'status'},
                {data:'action', name: 'action'}

            ],
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn-sm btn-info',
                    title: 'Learners',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    className: 'btn-sm btn-success',
                    title: 'Learners',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    className: 'btn-sm btn-warning',
                    title: 'Learners',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible',
                    }
                },
                {
                    extend: 'pdf',
                    className: 'btn-sm btn-primary',
                    title: 'Learners',
                    pageSize: 'A2',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    className: 'btn-sm btn-default',
                    title: 'Learners',
                    // orientation:'landscape',
                    pageSize: 'A2',
                    header: true,
                    footer: false,
                    orientation: 'landscape',
                    exportOptions: {
                        // columns: ':visible',
                        stripHtml: false
                    }
                }
            ],
            /*
             * create an element id to change permission names, while inline datatable updated
            */
            createdRow: function ( row, data, index ) {
                var td_index = data.DT_RowIndex;
                $('td', row).eq(0).attr('id', 'perm_'+data.id);
                $('td', row).eq(0).attr('title', 'Click to edit permission');
            },
            initComplete: function () {
                var api =  this.api();
                api.columns(searchable).every(function () {
                    var column = this;
                    var input = document.createElement("input");
                    input.setAttribute('placeholder', $(column.header()).text());
                    input.setAttribute('style', 'width: 140px; height:25px; border:1px solid whitesmoke;');

                    $(input).appendTo($(column.header()).empty())
                        .on('keyup', function () {
                            column.search($(this).val(), false, false, true).draw();
                        });

                    $('input', this.column(column).header()).on('click', function(e) {
                        e.stopPropagation();
                    });
                });

                api.columns(selectable).every( function (i, x) {
                    var column = this;

                    var select = $('<select style="width: 140px; height:25px; border:1px solid whitesmoke; font-size: 12px; font-weight:bold;"><option value="">'+$(column.header()).text()+'</option></select>')
                        .appendTo($(column.header()).empty())
                        .on('change', function(e){
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column.search(val ? '^'+val+'$' : '', true, false ).draw();
                            e.stopPropagation();
                        });

                    $.each(dropdownList[i], function(j, v) {
                        select.append('<option value="'+v+'">'+v+'</option>')
                    });
                });
            }
        });
        searchClassList();
    });
    // datatable inline cell edit callback function

    function activeActionButtons() {
        if ($('#class_id').val() != "" && $('#stream-id').val() != "") {
            $('#pdf-btn').prop('disabled', false);
            $('#excel-btn').prop('disabled', false);
        } else {
            $('#pdf-btn').prop('disabled', true);
            $('#excel-btn').prop('disabled', true);
        }
    }

    $('#class_id').on('change', function () {
        $.ajax({
            url: '/get-streams/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#stream-id').html(response.streams).select2()
                $('#subject-id').html(response.subjects).select2()
                $('#stream-id').prop('disabled', false)
                $('#subject-id').prop('disabled', false)
            }
        })

        activeActionButtons();
    })

    $('#search-btn').on('click', function () {
        searchClassList();
    })

    $('#pdf-btn').on('click', function () {
        window.open('/learners/class-list-pdf?class_id=' + $('#class_id').val() + '&stream_id=' + $('#stream-id').val() + '&pdf=1', '_blank');
    })

    $('#excel-btn').on('click', function () {
        window.open('/learners/class-list-pdf?class_id=' + $('#class_id').val() + '&stream_id=' + $('#stream-id').val() + '&excel=1', '_blank');
    })

    $('#stream-id').on('change', function () {
        $.ajax({
            url: '/get-learners/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#learner-id').html(response).select2()
                $('#learner-id').prop('disabled', false)
                $('#all_learners').prop('disabled', false)
            }
        })

        activeActionButtons();
    })

    $('#all_learners').on('change', function () {
        if ($(this).is(':checked')) {
            $('#learner-id').prop('disabled', true).select();
        } else {
            $('#learner-id').prop('disabled', false).select();
        }
    })

    $('select').select2();
})(jQuery);
