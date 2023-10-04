$(document).ready(function () {
    $('#scr-vtr-dynamic').DataTable({
        scrollY: '50vh',
        scrollCollapse: true,
        paging: false,
        ordering: false
    });

    $('#strand_id').on('change', function () {
        $.ajax({
            url: '/get-sub-strands/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#sub-strand-div').show();
                $('#sub-strands').html(response).select2();
            }
        })
    });

    $('#term_id').on('change', function () {
        $.ajax({
            url: '/get-term-exams/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#exam_id').html(response).select2();
                $('#exam_id').prop('disabled', false).select2();
            }
        })
    });

    $('#exam_id').on('change', function () {
        if ($(this).val()) {
            let exam_lock = false
            let data = {
                subject_id: $('#subject_id').val(),
                class_id: $('#class_id').val(),
                stream_id: $('#stream_id').val(),
                term_id: $('#term_id').val(),
                exam_id: $(this).val()
            }

            $.ajax({
                url: '/get-summative-assessments',
                type: 'POST',
                data: {data},
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                success: function (response) {
                    if (response['lock_exam']) {
                        exam_lock = true;
                    }
                    response['assessments'].map((data, index) => {
                        $('#score_' + data.learner_id).val(data.points)
                        $('#level_title_' + data.learner_id).text(data?.level?.title)
                        if (!exam_lock) {
                            $('#learner_save_btn_' + data.learner_id).prop('disabled', false)
                        }
                    })

                    $('#learners_id').html(response['learners']).select2()
                    $('#learners_id').prop('disabled', false)
                    $('#all_learners').prop('disabled', false)
                    $("#generate-report-btn button").prop('disabled', false)

                    if (!exam_lock) {
                        $('.points').prop('disabled', false)
                        $('#save-btn').prop('disabled', false)
                    }
                }
            })
        } else {
            $('.assessment-checkboxes').prop('disabled', true)
            $('#save-btn').prop('disabled', true)
        }
    });

    $('#class_id').on('change', function () {
        $.ajax({
            url: '/get-streams/' + $(this).val(),
            type: 'GET',
            success: function (response) {
                $('#stream-id').html(response.streams).select2()
                $('#stream-id').prop('disabled', false)
                $('#subject_id').html(response.subjects).select2()
                $('#subject_id').prop('disabled', false)
            }
        })
    })

    $('#subject_id').on('change', function () {
        getReports();
        $('#generate-report-btn').removeClass('d-none');
        $('#show_table').show()
    })

    // $('#stream-id').on('change', function () {
    //     $.ajax({
    //         url: '/get-learners/' + $(this).val(),
    //         type: 'GET',
    //         success: function (response) {
    //             $('#learners_id').html(response).select2()
    //             $('#learners_id').prop('disabled', false)
    //             $("#generate-report-btn button").prop('disabled', false)
    //         }
    //     })
    // })

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
                url: '/summative-reports/get-list',
                type: "get",
                data: {
                    term_id: $('#term_id').val(),
                    stream_id: $('#stream-id').val(),
                    exam_id: $('#exam_id').val(),
                    subject_id: $('#subject_id').val(),
                    class_id: $('#class_id').val()
                },
                headers: {
                    'X-CSRF-TOKEN': token
                }
            },
            columns: [
                {data: 'checkbox', name: 'checkbox', orderable: false},
                {data: 'admission_number', name: 'admission_number'},
                {data: 'name', name: 'name'},
                {data: 'score', name: 'score'},
                {data: 'remark', name: 'remark', orderable: false},
                {data: 'action', name: 'action', orderable: false}
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
            createdRow: function (row, data, index) {
                var td_index = data.DT_RowIndex;
                $('td', row).eq(0).attr('id', 'perm_' + data.id);
                $('td', row).eq(0).attr('title', 'Click to edit permission');
            },
            initComplete: function () {
                var api = this.api();
                api.columns(searchable).every(function () {
                    var column = this;
                    var input = document.createElement("input");
                    input.setAttribute('placeholder', $(column.header()).text());
                    input.setAttribute('style', 'width: 140px; height:25px; border:1px solid whitesmoke;');

                    $(input).appendTo($(column.header()).empty())
                        .on('keyup', function () {
                            column.search($(this).val(), false, false, true).draw();
                        });

                    $('input', this.column(column).header()).on('click', function (e) {
                        e.stopPropagation();
                    });
                });

                api.columns(selectable).every(function (i, x) {
                    var column = this;

                    var select = $('<select style="width: 140px; height:25px; border:1px solid whitesmoke; font-size: 12px; font-weight:bold;"><option value="">' + $(column.header()).text() + '</option></select>')
                        .appendTo($(column.header()).empty())
                        .on('change', function (e) {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                            e.stopPropagation();
                        });

                    $.each(dropdownList[i], function (j, v) {
                        select.append('<option value="' + v + '">' + v + '</option>')
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

    $('.summative-save').on('click', function () {
        let data = {
            subject_id: $('#subject_id').val(),
            stream_id: $('#stream_id').val(),
            class_id: $('#class_id').val(),
            term_id: $('#term_id').val(),
            exam_id: $('#exam_id').val(),
            learner_id: $('#learner_id_' + $(this).data('key')).val(),
            points: $('#score_' + $(this).data('learner-id')).val()
        }

        $.ajax({
            url: '/summative-assessments/save-learner-assessment',
            type: 'POST',
            data: data,
            headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
            success: function (response) {

            }
        })
    })

    function checkAllCheckBoxes() {
        let all_selected = false;
        $('.learner-checkboxes').map((index, checkbox) => {
            if ($(checkbox).is(':checked')) {
                all_selected = true
            }
        })

        if (all_selected) {
            $('#generate-all-summative-report').prop('disabled', false);
        } else {
            $('#generate-all-summative-report').prop('disabled', true);
        }
    }

    $(document).on('click', '.learner-checkboxes', function () {
        checkAllCheckBoxes()
    })

    $('#all-summative-assessment').on('click', function () {
        $('.learner-checkboxes').map((index, checkbox) => {
            $(checkbox).prop('checked', true)
        });
        checkAllCheckBoxes()
    })

    $('#generate-all-summative-report').on('click', function () {
        $('#summative-form').append('<input type="hidden" name="class_id" value="' + $('#class_id').val() + '" />')
            .append('<input type="hidden" name="stream_id" value="' + $('#stream-id').val() + '" />')
            .append('<input type="hidden" name="term_id" value="' + $('#term_id').val() + '" />')
            .append('<input type="hidden" name="exam_id" value="' + $('#exam_id').val() + '" />')
            .append('<input type="hidden" name="subject_id" value="' + $('#subject_id').val() + '" />')

        $('#summative-form').submit();
    })

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

    $('#all_learners').on('change', function () {
        if ($(this).is(':checked')) {
            $('#learners_id').prop('disabled', true).removeAttr('required').select();
        } else {
            $('#learners_id').prop('disabled', false).attr('required').select();
        }
    })
})
