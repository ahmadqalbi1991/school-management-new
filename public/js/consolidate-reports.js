(function($) {
    'use strict';
    // permission table
    $(document).ready(function()
    {
        $('#class_id').on('change', function () {
            $.ajax({
                url: '/get-streams/' + $(this).val(),
                type: 'GET',
                success: function (response) {
                    $('#stream_id').html(response.streams).select2()
                    $('#stream_id').prop('disabled', false)
                }
            })
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

        $('#term_id').on('change', function () {
            $.ajax({
                url: '/get-term-exams/' + $(this).val(),
                type: 'GET',
                success: function (response) {
                    $('#exam_ids').html(response).select2();
                    $('#exam_ids').prop('disabled', false).select2();
                }
            })
        });

        $('#exam_ids').on('change', function (e) {
            let ids = $(this).val();
            if (ids.length > 4) {
                $('#generate-reports-btn').prop('disabled', true);
                $('#all_learners').prop('disabled', true);
                return false;
            }

            if (ids.length > 1) {
                $('#generate-reports-btn').prop('disabled', false);
                $.ajax({
                    url: '/get-report-learners',
                    type: 'POST',
                    data: {
                        exam_ids: ids,
                        class_id: $('#class_id').val(),
                        stream_id: $('#stream_id').val(),
                        term_id: $('#term_id').val(),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (ids.length > 4) {
                            $('#generate-reports-btn').prop('disabled', true);
                            $('#all_learners').prop('disabled', true);
                        }
                        $('#all_learners').prop('disabled', false);
                        $('#generate-reports-btn').prop('disabled', false);
                        $('#learners_ids').html(response).select2();
                        $('#learners_ids').prop('disabled', false).select2();
                    }
                })
            }
        })
        $('#all_learners').on('change', function () {
            if ($(this).is(':checked')) {
                $('#learners_ids').prop('disabled', true).removeAttr('required').select();
            } else {
                $('#learners_ids').prop('disabled', false).attr('required').select();
            }
        })

    });
    $('select').select2();
})(jQuery);
