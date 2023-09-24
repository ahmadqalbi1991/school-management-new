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

    .term-dates-table table, .term-dates-table table td {
        border: none;
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
           <img src="{{ url('/') . '/' . $school->logo }}" alt="" width='100%' height="auto">
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
        <h4>{{ __('Summative Assessment Summary Report') }}</h4>
    </div>
    <div class="learners-details">
        <p><strong>{{ __('Learner') }}: </strong>{{ $learner->name }}</p>
        <p><strong>{{ __('Admission') }} #: </strong>{{ $learner->admission_number }}</p>
        <p><strong>{{ __('Grade') }}: </strong>{{ $stream->school_class->class }}</p>
        <p><strong>{{ __('Stream') }}: </strong>{{ $stream->title }}</p>
    </div>
    <div class="levels-details">
        @foreach($levels as $level)
            <div>&#x2022; {{ $level->teacher_remark }} {{ $level->title }} - <strong>{{ initials($level->title) }}</strong> ({{ $level->min_point }} - {{ $level->max_point }})%</div>
        @endforeach
    </div>
    <div class="date-generated">Date Generated: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
    <div class="subjects-table">
        <table>
            <thead>
            <tr>
                <th>{{ __('Learning Area') }}</th>
                <th>{{ __('Score') }}</th>
                <th>{{ __('Remarks') }}</th>
                <th>{{ __('Teacher Remark') }}</th>
            </tr>
            </thead>
            <tbody>
            @php
                $total = 0;
            @endphp
            @foreach($assessments as $assessment)
                <tr>
                    @php
                        $point = !empty($assessment->assessment->points) ? $assessment->assessment->points : 0;
                        $level = !empty($assessment->assessment->level->title) ? $assessment->assessment->level->title : '';
                        $teacher_remark = !empty($assessment->assessment->level->teacher_remark) ? $assessment->assessment->level->teacher_remark : '';
                        $total += $point;
                    @endphp
                    <td>{{ $assessment->subject->title }}</td>
                    <td style="text-align: right">{{ $point }}%</td>
                    <td>{{ $level }}</td>
                    <td>{{ $teacher_remark }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                @php
                    $count = count($assessments) > 0 ? count($assessments) : 1;
                    $average = $total / $count;
                @endphp
                <th>Average</th>
                <th style="text-align: right">{{ round($average, 2) }}%</th>
                <th>{{ checkSummetiveCriteria(round($average, 2)) }}</th>
                <th>{{ checkSummetiveCriteria(round($average, 2), true) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="general-text">Learner General Average:  {{ checkSummetiveCriteria(round($average, 2)) }}</div>
    <p>{{ __('Teacher Comment:') }} {{ checkSummetiveCriteria(round($average, 2), false, rand(1, 10)) }}</p>
    <div class="term-dates-table">
        <table>
            <tbody>
            <tr>
                <td>
                    <p class="m-0"><strong>{{ __('Term Closing Date') }}: </strong>{{ \Carbon\Carbon::parse($term->end_date)->format('d M, Y') }}</p>
                </td>
                <td>
                    <p class="m-0"><strong>{{ __('Next Term Start Date') }}: </strong>{{ \Carbon\Carbon::parse($term->next_term_date)->format('d M, Y') }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p>{{ __('Signature') }}</p>
                    <div class="border"></div>
                    <p>Class Teacher</p>
                </td>
                <td>
                    <p>{{ __('Signature') }}</p>
                    <div class="border"></div>
                    <p>Principal</p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <footer>
        <p>Powered by CRE.CO.KE</p>
    </footer>
</div>
<p style="page-break-before: always;"></p>
