<?php

namespace App\Exports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassExport implements FromCollection, WithHeadings
{
    protected $request;
    protected $streams;

    public function __construct($request, $streams)
    {
        $this->request = $request;
        $this->streams = $streams;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): \Illuminate\Support\Collection
    {
        $schools = School::when(Auth::user()->role !== 'super_admin', function ($q) {
            return $q->where('id', Auth::user()->school_id);
        })
            ->with('learners', function ($q) {
                return $q->when(!empty($this->streams), function ($q) {
                    return $q->where('stream_id', $this->streams);
                });
            })->get();

        $data = [];
        $i = 0;

        foreach ($schools as $school) {
            foreach ($school->learners as $learner) {
                $data[$i]['admission_number'] = $learner->admission_number;
                $data[$i]['learner'] = $learner->name;
                $data[$i]['school'] = $school->school_name;
                $data[$i]['grade'] = !empty($learner->stream) ? (!empty($learner->stream->school_class) ? $learner->stream->school_class->class : '') : '';
                $data[$i]['stream'] = !empty($learner->stream) ? $learner->stream->title : '';
                $i++;
            }
        }

        $data = collect($data);
        $data = $data->sortBy('school')->values();
        return $data->sortBy('admission_number')->values();
    }

    public function headings(): array
    {
        return [
            'Admission Number', 'Learner Name', 'School', 'Grade', 'Stream'
        ];
    }
}
