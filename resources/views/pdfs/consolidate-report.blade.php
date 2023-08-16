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

    .levels-details p {
        display: initial;
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
            <img src="{{ public_path($school->logo) }}" alt="">
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
        <h4>{{ __('Summative Assessment Consolidate Report') }}</h4>
    </div>
    <div class="learners-details">
        <p><strong>{{ __('Learner') }}: </strong>{{ $learner->name }}</p>
        <p><strong>{{ __('Admission') }} #: </strong>{{ $learner->admission_number }}</p>
        <p><strong>{{ __('Grade') }}: </strong>{{ $stream->school_class->class }}</p>
        <p><strong>{{ __('Stream') }}: </strong>{{ $stream->title }}</p>
    </div>
    <div class="levels-details">
        @foreach($levels as $level)
            <p>&#x2022; {{ $level->title }} - <strong>{{ initials($level->title) }}</strong> ({{ $level->min_point }} - {{ $level->max_point }} Points)</p>
        @endforeach
    </div>
    <div class="date-generated">Date Generated: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
    <div class="subjects-table">
        <table>
            <thead>
            <tr>
                <th>{{ __('Learning Area') }}</th>
                @foreach($exams as $exam)
                    <th>{{ $exam->title }}</th>
                @endforeach
                <th>{{ __('Total') }}</th>
                <th>{{ __('Average') }}</th>
                <th>{{ __('Summative Remarks of the Average') }}</th>
            </tr>
            </thead>
            <tbody>
            @php
                $report_total = 0;
                $report_average = 0;
            @endphp
            @foreach($reports as $report)
                <tr>
                    <td><strong>{{ $report['subject'] }}</strong></td>
                    @php
                        $subject_total = 0;
                    @endphp
                    @foreach($report['points'] as $point)
                        @php
                            $subject_total += $point;
                        @endphp
                        <td>{{ $point }}</td>
                    @endforeach
                    @php
                        $average = round($subject_total / count($report['points']), 1);
                        $report_total += $subject_total;
                        $report_average += $average;
                    @endphp
                    <td><strong>{{ $subject_total }}</strong></td>
                    <td><strong>{{ $average }}</strong></td>
                    <td><strong>{{ checkSummetiveCriteria($average) }}</strong></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th>{{ __('Total') }}</th>
                <th colspan="{{ count($exams) }}"></th>
                <th>{{ $report_total }}</th>
                @php
                    $calculated_average = round(($report_average / count($reports)), 2);
                @endphp
                <th>{{ $calculated_average }}</th>
                <th>{{ checkSummetiveCriteria($calculated_average) }}</th>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="signatures">
        <div class="teacher">
            <p>{{ __('Signature') }}</p>
            <div class="border"></div>
            <p>Class Teacher</p>
        </div>
        <div class="principle">
            <p>{{ __('Signature') }}</p>
            <div class="border"></div>
            <p>Principal</p>
        </div>
    </div>
    <footer>
        <p>Powered by CRE.CO.KE</p>
    </footer>
</div>
<p style="page-break-before: always;"></p>
