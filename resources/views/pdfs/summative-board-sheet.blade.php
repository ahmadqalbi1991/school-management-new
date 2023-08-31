<style>
    .school-detail-wrapper {
        display: block;
        width: 100%;
    }

    .school-detail-wrapper > div {
        float: left;
        padding: 0 20px;
        height: 120px;
    }

    .school-img {
        width: 20%;
        position: relative;
    }

    .school-img img {
        width: 75%;
    }

    .school-address {
        width: 72%;
    }

    .pdf-wrapper {
        font-family: sans-serif;
        padding: 0 25px;
        font-size: 12px;
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

    .date-generated {
        width: 40%;
        font-weight: 600;
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

    .school-address h5 {
        font-size: 20px;
        margin: 0;
        margin-bottom: 0.5rem;
    }

    .school-address p {
        margin: 0;
        margin-bottom: 0.2rem;
        font-size: 12px;
    }

    .general-text {
        margin-top: 25px;
        font-size: 14px;
        font-weight: 600;
    }

    .signatures {
        margin: 1rem 0;
        width: 100%;
    }

    .signatures > div {
        width: 50%;
        float: left;
        font-size: 12px;
    }

    .border {
        height: 50px;
        border-bottom: 1px solid;
        width: 150px;
    }

    footer {
        font-size: 12px !important;
        width: 100%;
        line-height: 50px;
        text-align: right;
    }
</style>
<div class="pdf-wrapper">
    <div class="school-detail-wrapper">
        <div class="school-img">
{{--            <img src="<?php echo $_SERVER["DOCUMENT_ROOT"].'/public/' . $school->logo;?>"/>--}}
            <img src="{{ url('/') .   $school->logo }}" alt="" width='100%' height="auto">
        </div>
        <div class="school-address">
            <h5>{{ $school->school_name }}</h5>
            <p><strong>{{ __('School Address') }}: </strong>{{ $school->address }} <strong>{{ __('Telephone') }}: </strong>{{ $school->phone_number }}</p>
            <p><strong>{{ __('Email') }}: </strong>{{ $school->email }}</p>
            <p><strong>{{ __('Website') }}: </strong><a href="">{{ $school->school_website }}</a></p>
        </div>
    </div>
    <div class="term-details">
        <p>{{ $term->term }}, {{ $term->year }} ({{ \Carbon\Carbon::parse($term->start_date)->format('d M, Y') }} - {{ \Carbon\Carbon::parse($term->end_date)->format('d M, Y') }})</p>
        <h4>{{ __('Summative Board Sheet') }}</h4>
        <h4>{{ $exams }}</h4>
    </div>
    <div class="date-generated">Date Generated: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
    <div class="subjects-table">
        <table>
            <thead>
            <tr>
                <th>{{ __('Adm No') }}</th>
                <th>{{ __('Learner') }}</th>
                <th>{{ __('Stream') }}</th>
                @foreach($subjects as $subject)
                    <th>{{ $subject->subject->shortcode }}</th>
                @endforeach
                <th>{{ __('Learner Total') }}</th>
                <th>{{ __('Learner Average') }}</th>
                <th>{{ __('Rank') }}</th>
            </tr>
            </thead>
            <tbody>
            @php
                $total_points = 0;
                $rank = 0;
                $last_points = 0;
                $total_average = 0;
            @endphp
            @foreach($results as $result)
                <tr>
                    <td>{{ $result['learner']->admission_number }}</td>
                    <td>{{ $result['learner']->name }}</td>
                    <td>{{ $result['stream']->title }}</td>
                    @php
                        $points = '';
                        $total_learners_points = 0;
                    @endphp
                    @foreach($subjects as $subject)
                        @php
                            if (!empty($result['subjects'][$subject->subject_id])) {
                                $points = $result['subjects'][$subject->subject_id]['points'];
                                $total_learners_points += $points;
                            } else {
                                $points = '';
                            }
                        @endphp
                        <td>{{ $points }}</td>
                    @endforeach
                    @php
                        $learner_average = $total_learners_points / $subjects->count();
                        $total_average += $result['learner_average'];
                    @endphp
                    <td>{{ $result['total_learner_points'] }}</td>
                    <td>{{ $result['learner_average'] }}</td>
                    @php
                        if ($rank === 0) {
                            $last_points = $result['learner_average'];
                            $rank++;
                        }

                        if ($last_points !== $result['learner_average']) {
                            $rank++;
                        }
                    @endphp
                    <td>{{ $rank }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3">{{ __('Learning Activity Total') }}</td>
                @php
                    $total_points = 0;
                    $points = '';
                @endphp
                @foreach($subjects as $subject)
                    @php
                        if (!empty($totals[$subject->subject_id])) {
                            $points = $totals[$subject->subject_id]['points'];
                            $total_points += $points;
                        } else {
                            $points = '';
                        }
                    @endphp
                    <td><strong>{{ $points }}</strong></td>
                @endforeach
                <td colspan="3"><strong>{{ $total_points }}</strong></td>
            </tr>
            <tr>
                <td colspan="3">{{ __('Learning Activity Average') }}</td>
                @php
                    $average = '';
                @endphp
                @foreach($subjects as $subject)
                    @php
                        if (!empty($totals[$subject->subject_id])) {
                            $average = $totals[$subject->subject_id]['average'];
                        } else {
                            $average = '';
                        }
                    @endphp
                    <td><strong>{{ $average }}</strong></td>
                @endforeach
                @php
                if (count($results)) {
                    $total_average = $total_average / count($results);
                } else {
                    $total_average = 0;
                }
                @endphp
                <td colspan="3"><strong>{{ round($total_average, 2) }}</strong></td>
            </tr>
            </tfoot>
        </table>
    </div>
    <footer>
        <p>Powered by CRE.CO.KE</p>
    </footer>
</div>
<p style="page-break-before: always;"></p>
