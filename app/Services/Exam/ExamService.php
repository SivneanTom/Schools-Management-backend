<?php
namespace App\Services\Exam;

use App\Models\Exam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamService
{
    public function getAll(array $filters=[]): LengthAwarePaginator
    {
        $q = Exam::query()->with('semester');

        $q->when($filters['search'] ?? null, function (Builder $q, string $search) {
            $q->where(function (Builder $x) use ($search) {
                $x->where('name_km','ilike',"%{$search}%")
                  ->orWhere('name_en','ilike',"%{$search}%");
            });
        });

        $q->when($filters['semester_id'] ?? null, fn(Builder $q,$id)=>$q->where('semester_id',$id));
        $q->when($filters['exam_type'] ?? null, fn(Builder $q,$v)=>$q->where('exam_type',$v));
        $q->when($filters['status'] ?? null, fn(Builder $q,$v)=>$q->where('status',$v));
        $q->when($filters['date_from'] ?? null, fn(Builder $q,$v)=>$q->whereDate('start_date','>=',$v));
        $q->when($filters['date_to'] ?? null, fn(Builder $q,$v)=>$q->whereDate('end_date','<=',$v));

        return $q->orderByDesc('start_date')->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Exam
    {
        return DB::transaction(function () use ($data) {
            $this->validateBusinessRules($data);
            return Exam::create($data)->load('semester');
        });
    }

    public function update(Exam $exam, array $data): Exam
    {
        return DB::transaction(function () use ($exam,$data) {
            $merged = array_merge([
                'semester_id'=>$exam->semester_id,
                'name_km'=>$exam->name_km,
                'name_en'=>$exam->name_en,
                'exam_type'=>$exam->exam_type,
                'start_date'=>$exam->start_date->format('Y-m-d'),
                'end_date'=>$exam->end_date->format('Y-m-d'),
                'status'=>$exam->status,
            ], $data);

            $this->validateBusinessRules($merged);
            $exam->update($data);
            return $exam->refresh()->load('semester');
        });
    }

    public function delete(Exam $exam): void
    {
        DB::transaction(fn()=>$exam->delete());
    }

    private function validateBusinessRules(array $data): void
    {
        if ($data['end_date'] < $data['start_date']) {
            throw ValidationException::withMessages([
                'end_date'=>['The exam end date must be on or after the start date.']
            ]);
        }
    }
}
