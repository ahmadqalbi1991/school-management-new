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
        margin-top: 0 !important;
        margin-bottom: 0 !important;
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

    .term-dates {
        width: 100%;
    }

    .term-dates div {
        width: 50%;
        float: left;
    }
</style>
<div class="pdf-wrapper">
    <div class="school-detail-wrapper">
        <div class="school-img">
           <img src="{{ url('/') . '/' . $school->logo }}" alt="" width='100%' height="auto">
        </div>
        <div class="school-address">
            <h5>{{ $school->school_name }}</h5>
            <p><strong>{{ __('School Address') }}: </strong>{{ $school->address }} <strong>{{ __('Telephone') }}
                    : </strong>{{ $school->phone_number }}</p>
            <p><strong>{{ __('Email') }}: </strong>{{ $school->email }}</p>
            <p><strong>{{ __('Website') }}: </strong><a href="">{{ $school->school_website }}</a></p>
            <p><strong>{{ __('Grade') }}: </strong>{{ $class->class }}</p>
        </div>
    </div>
    <div class="date-generated">Generated by ZAMILI.CO.KE on: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
    <div class="subjects-table">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>{{ __('AdmNo') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Stream') }}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @php
                $i = 1;
            @endphp
            @foreach($learners as $key => $learner)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $learner->admission_number }}</td>
                    <td>{{ $learner->name }}</td>
                    <td>{{ $learner->stream->title }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @php
                    $i++;
                @endphp
            @endforeach
            </tbody>
        </table>
    </div>
    <!--<footer>
        <p>Powered by ZAMILI.CO.KE</p>
    </footer>-->
</div>
<p style="page-break-before: always;"></p>
