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
                    $('#all_streams').prop('disabled', false)
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
                    $('#exam_id').html(response).select2();
                    $('#exam_id').prop('disabled', false).select2();
                }
            })
        });

        $('#exam_id').on('change', function (e) {
            let ids = $(this).val();
            if (ids.length > 4) {
                $('#generate-reports-btn').prop('disabled', true);
                return false;
            }

            if (ids.length > 0) {
                $('#generate-reports-btn').prop('disabled', false);
            }
        })

        $('#all_streams').on('change', function () {
            if ($(this).is(':checked')) {
                $('#stream_id').prop('disabled', true).removeAttr('required').select();
            } else {
                $('#stream_id').prop('disabled', false).attr('required').select();
            }
        })
    });
    $('select').select2();
})(jQuery);
