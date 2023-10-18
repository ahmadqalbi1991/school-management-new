<style>
    .pdf-wrapper {
        font-family: sans-serif;
        padding: 0 25px;
        font-size: 12px;
    }

    .formative-report-header {
        text-align: center;
        position: relative;
    }

    .underline {
        position: relative;
    }

    .underline:after {
        content: '';
        background: #000;
        height: 2px;
        position: absolute;
        width: 100%;
        bottom: -5px;
        left: 0;
    }

    .learners-details, .levels-details {
        width: 100%;
        margin: 15px 0;
        margin-bottom: 10px !important;
        text-align: center;
    }

    .learners-details p {
        display: initial;
        padding: 0 10px;
    }

    .levels-details div {
        display: inline-block;
        padding: 0 20px;
    }

    .term-details {
        width: 100%;
        text-align: center;
        font-size: 12px;
    }

    .term-details h4, .term-details p {
        margin-top: 0!important;
        margin-bottom: 0!important;
    }

    table, td, th {
        border: 1px solid #ddd;
        text-align: left;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        padding: 8px;
        font-size: 12px;
    }

    .subjects-table {
        margin-top: 1rem;
    }

    .formative-assessment {
        width: 120px;
        text-align: center;
    }

    .formative-assessment p, .formative-assessment span {
        text-wrap: wrap;
    }

    .m-0 {
         margin: 0 !important;
    }

    .tick {
        width: 100%;
        text-align: center;
    }

    .tick img {
        display: inline-block;
        width: 40px;
    }

</style>
<div class="pdf-wrapper">
    <div class="formative-report-header">
        <h2 class="">{{ __($subject->title . ' Report')}}</h2>
    </div>
    <div class="learners-details">
        <p><strong>{{ __('Learner') }}: </strong>{{ $learner->name }}</p>
        <p><strong>{{ __('Admission') }} #: </strong>{{ $learner->admission_number }}</p>
        <p><strong>{{ __('Grade') }}: </strong>{{ $stream->school_class->class }}</p>
        <p><strong>{{ __('Stream') }}: </strong>{{ $stream->title }}</p>
    </div>
    <div class="levels-details">
        <div>&#x2022; {{ __('No Assessment') }} - <strong>{{ initials('No Assessment') }}</strong> (0 Points)</div>
        @foreach($levels as $level)
            <div>&#x2022; {{ $level->title }} - <strong>{{ initials($level->title) }}</strong> ({{ $level->points }}
                Points)
            </div>
        @endforeach
    </div>
    <div class="term-details">
        <p>{{ $term->term }}, {{ $term->year }} ({{ \Carbon\Carbon::parse($term->start_date)->format('d M, Y') }} - {{ \Carbon\Carbon::parse($term->end_date)->format('d M, Y') }})</p>
        <h4>{{ __('Formative Assessment Summary Report') }}</h4>
    </div>
    <div class="subjects-table">
        <table>
            <thead>
            <tr>
                <th></th>
                @foreach($levels as $level)
                    <th>
                        <div class="formative-assessment">
                            <p class="m-0"><strong>{{ initials($level->title) }}</strong></p>
                        </div>
                    </th>
                @endforeach
                <th></th>
            </tr>
            </thead>
            @php
                $total_attempted = 0;
                $subject_total = 0;
                $total_activities = 0;
                $average_performance = 0;
            @endphp
            <tbody>
            @foreach($subject->strands as $strand_key => $strand)
                <tr>
                    <td>
                        <h3><strong>{{ $strand->title }}</strong></h3>
                    </td>
                    <td colspan="{{ $levels->count() + 1 }}"></td>
                </tr>
                @foreach($strand->sub_strands as $sub_strand_key => $sub_strand)
                    <tr>
                        <td>
                            <strong>{{ $sub_strand->title }}</strong>
                        </td>
                        <td colspan="{{ $levels->count() + 1 }}"></td>
                    </tr>
                    @php
                        $total_activities += $sub_strand->learning_activities->count();
                    @endphp
                    @foreach($sub_strand->learning_activities as $activity_key => $activity)
                        <tr>
                            <td>
                                <p>{{ $activity->title }}</p>
                            </td>
                            @php
                                $point = 0;
                            @endphp
                            @foreach($levels as $level_key => $level)
                                <td class="text-center">
                                    <div class="tick">
                                        @if(!empty($activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['levels'][$level_key]))
                                            @php
                                                $point = $activities_defination[$strand_key]['sub_strands'][$sub_strand_key]['activities'][$activity_key]['levels'][$level_key]['points'];
                                                $total_attempted += 1;
                                                $subject_total += $point;
                                            @endphp
                                        <img src="{{ url('/') . '/images/tick.png' }}" alt="">
                                        @else
                                            <img src="{{ url('/') . '/images/tick.png' }}" alt="">
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            <td>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
            </tbody>
            <tfoot>
            @php
                $average_performance = $subject_total / $total_attempted;
            @endphp
            <tr>
                <td><strong>{{ __('Total Activities') }}</strong></td>
                <td colspan="{{ $levels->count() }}"></td>
                <td><h5>{{ $total_activities }}</h5></td>
            </tr>
            <tr>
                <td><strong>{{ __('Average Performance') }}</strong></td>
                <td colspan="{{ $levels->count() }}"></td>
                <td><h5>{{ round($average_performance, 2) }}</h5></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
