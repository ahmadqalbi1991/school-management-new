$(document).ready(function () {
    $('#class_id').on('change', function () {
        $.ajax({
            url: '/get-streams/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#stream-id').html(response.streams).select2()
                $('#stream-id').prop('disabled', false)
            }
        })
    })

    $('#term_id').on('change', function () {
       getReports();
        $('#show_table').show()
    });

    function getReports() {
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
                url: '/reports/get-list',
                type: "get",
                data: {term_id: $('#term_id').val(), stream_id: $('#stream-id').val()},
                headers: {
                    'X-CSRF-TOKEN': token
                }
            },
            columns: [
                {data:'checkbox', name: 'checkbox', orderable: false},
                {data:'name', name: 'name'},
                {data:'email', name: 'email'},
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
    }

    $('#result-table').DataTable({
        scrollY: '50vh',
        scrollCollapse: true,
        paging: false,
        ordering: false
    });

    $('#scr-vtr-dynamic').DataTable({
        scrollY: '50vh',
        scrollCollapse: true,
        paging: false,
        ordering: false
    });

    function checkAllCheckBoxes() {
        let all_selected = false;
        $('.learner-checkboxes').map((index, checkbox) => {
            if ($(checkbox).is(':checked')) {
                all_selected = true
            }
        })

        if (all_selected) {
            $('#generate-all-formative-report').prop('disabled', false);
        } else {
            $('#generate-all-formative-report').prop('disabled', true);
        }
    }

    $(document).on('click', '.learner-checkboxes', function () {
        checkAllCheckBoxes()
    })

    $('#all-formative-assessment').on('click', function () {
        if ($(this).is(':checked')) {
            $('.learner-checkboxes').map((index, checkbox) => {
                $(checkbox).prop('checked', true)
            });
        } else {
            $('.learner-checkboxes').map((index, checkbox) => {
                $(checkbox).prop('checked', false)
            });
        }
        checkAllCheckBoxes()
    })
})
