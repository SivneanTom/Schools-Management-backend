<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Enrollment;
use App\Models\AttendanceRecord;
use App\Models\ExamResult;
use App\Models\AssignmentSubmission;
use App\Models\StudentFee;
use App\Models\StudentScholarship;

class Student extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVE = 'INACTIVE';
    public const STATUS_GRADUATED = 'GRADUATED';
    public const STATUS_SUSPENDED = 'SUSPENDED';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_GRADUATED,
        self::STATUS_SUSPENDED,
    ];

    protected $fillable = [
        'user_id',
        'student_code',
        'first_name_km',
        'last_name_km',
        'first_name_en',
        'last_name_en',
        'gender',
        'date_of_birth',
        'phone',
        'address_km',
        'address_en',
        'admission_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            ParentProfile::class,
            'student_parents',
            'student_id',
            'parent_id'
        )
            ->withPivot([
                'relationship',
                'is_primary',
            ])
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(
            Enrollment::class,
            'student_id'
        );
    }

    public function attendanceRecords()
    {
        return $this->hasMany(
            AttendanceRecord::class,
            'student_id'
        );
    }

    public function examResults()
    {
        return $this->hasMany(
            ExamResult::class,
            'student_id'
        );
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(
            AssignmentSubmission::class,
            'student_id'
        );
    }

    public function studentFees()
    {
        return $this->hasMany(
            StudentFee::class,
            'student_id'
        );
    }

    public function studentScholarships()
    {
        return $this->hasMany(
            StudentScholarship::class,
            'student_id'
        );
    }

    public function teacherAssignment()
    {
        return $this->belongsTo(
            TeacherAssignment::class,
            'teacher_assignment_id'
        );
    }
}
