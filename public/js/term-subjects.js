(function($) {
    'use strict';
    // permission table
    $(document).ready(function()
    {
        var searchable = [];
        var selectable = [];
        var token = $('#token').val();

        var dTable = $('#terms_table').DataTable({

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
                url: 'term-subjects/get-list',
                type: "get",
                headers: {
                    'X-CSRF-TOKEN': token
                }
            },
            columns: [
                {data:'term', name: 'term', orderable: false},
                {data:'year', name: 'year', orderable: false},
                {data:'subjects', name: 'subjects', orderable: false},
                {data:'action', name: 'action'},
            ],
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn-sm btn-info',
                    title: 'Term Subjects',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    className: 'btn-sm btn-success',
                    title: 'Term Subjects',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    className: 'btn-sm btn-warning',
                    title: 'Term Subjects',
                    header: false,
                    footer: true,
                    exportOptions: {
                        // columns: ':visible',
                    }
                },
                {
                    extend: 'pdf',
                    className: 'btn-sm btn-primary',
                    title: 'Term Subjects',
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
                    title: 'Term Subjects',
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
        $("#start_date").dateDropper({
            dropWidth: 500,
            dropPrimaryColor: "#1abc9c",
            dropBorder: "1px solid #1abc9c"
        })
        $("#end_date").dateDropper({
            dropWidth: 500,
            dropPrimaryColor: "#1abc9c",
            dropBorder: "1px solid #1abc9c",
        })
    });

    $('#class_id').on('change', function () {
        $.ajax({
            url: '/get-streams/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#stream_id').html(response.streams).select2()
                $('#stream_id').prop('disabled', false)
                $('#subject_id').html(response.subjects).select2()
                $('#subject_id').prop('disabled', false)
            }
        })
    })

    $('select').select2();

    $('#year').on('change', function () {
        $.ajax({
            url: '/get-terms/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#term_id').html(response).select2()
                $('#term_id').prop('disabled', false)
            }
        })
    })
})(jQuery);
