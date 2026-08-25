<?php

namespace App\Services\Teacher;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\LearningMaterial;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherService
{
    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            $teacherRole = Role::where('code', 'TEACHER')->where('is_active', true)->firstOrFail();
            $status = $data['status'] ?? 'ACTIVE';

            $user = User::create([
                'role_id' => $teacherRole->id,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'preferred_language' => $data['preferredLanguage'] ?? 'KM',
                'status' => $status,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'teacher_code' => $data['teacherCode'],
                'first_name_km' => $data['firstNameKm'],
                'last_name_km' => $data['lastNameKm'],
                'first_name_en' => $data['firstNameEn'] ?? null,
                'last_name_en' => $data['lastNameEn'] ?? null,
                'gender' => $data['gender'],
                'date_of_birth' => $data['dateOfBirth'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address_km' => $data['addressKm'] ?? null,
                'address_en' => $data['addressEn'] ?? null,
                'hire_date' => $data['hireDate'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'status' => $status,
            ]);

            return $teacher->load('user.role');
        });
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        return DB::transaction(function () use ($teacher, $data) {
            $user = $teacher->user;

            if (array_key_exists('username', $data)) $user->username = $data['username'];
            if (array_key_exists('email', $data)) $user->email = $data['email'];
            if (array_key_exists('preferredLanguage', $data)) $user->preferred_language = $data['preferredLanguage'];
            if (array_key_exists('password', $data) && !empty($data['password'])) $user->password = Hash::make($data['password']);
            $user->save();

            if (array_key_exists('teacherCode', $data)) $teacher->teacher_code = $data['teacherCode'];
            if (array_key_exists('firstNameKm', $data)) $teacher->first_name_km = $data['firstNameKm'];
            if (array_key_exists('lastNameKm', $data)) $teacher->last_name_km = $data['lastNameKm'];
            if (array_key_exists('firstNameEn', $data)) $teacher->first_name_en = $data['firstNameEn'];
            if (array_key_exists('lastNameEn', $data)) $teacher->last_name_en = $data['lastNameEn'];
            if (array_key_exists('gender', $data)) $teacher->gender = $data['gender'];
            if (array_key_exists('dateOfBirth', $data)) $teacher->date_of_birth = $data['dateOfBirth'];
            if (array_key_exists('phone', $data)) $teacher->phone = $data['phone'];
            if (array_key_exists('addressKm', $data)) $teacher->address_km = $data['addressKm'];
            if (array_key_exists('addressEn', $data)) $teacher->address_en = $data['addressEn'];
            if (array_key_exists('hireDate', $data)) $teacher->hire_date = $data['hireDate'];
            if (array_key_exists('qualification', $data)) $teacher->qualification = $data['qualification'];
            if (array_key_exists('specialization', $data)) $teacher->specialization = $data['specialization'];

            $teacher->save();
            return $teacher->load('user.role');
        });
    }

    public function updateStatus(Teacher $teacher, string $status): Teacher
    {
        return DB::transaction(function () use ($teacher, $status) {
            $teacher->update(['status' => $status]);
            $teacher->user->update(['status' => $status === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE']);
            return $teacher->load('user.role');
        });
    }

    public function applyAccessScope(Builder $query, User $user): Builder
    {
        $role = $user->role->code;
        if (in_array($role, ['SUPER_ADMIN', 'ADMIN'], true)) return $query;
        if ($role === 'TEACHER') return $query->where('user_id', $user->id);
        return $query->whereRaw('1 = 0');
    }

    public function findByUserId(int $userId): Teacher
    {
        return Teacher::where('user_id', $userId)->firstOrFail();
    }

    public function getMyAssignmentIds(User $user)
    {
        $teacher = $this->findByUserId($user->id);

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'ACTIVE')
            ->pluck('id');
    }

    public function getMyAssignments(User $user)
    {
        $teacher = $this->findByUserId($user->id);

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'ACTIVE')
            ->with(['schoolClass.grade', 'schoolClass.academicYear', 'subject', 'semester'])
            ->orderBy('id')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'assignedAt' => $a->assigned_at,
                'status' => $a->status,
                'class' => $a->schoolClass ? [
                    'id' => $a->schoolClass->id,
                    'nameKm' => $a->schoolClass->name_km,
                    'nameEn' => $a->schoolClass->name_en,
                    'grade' => $a->schoolClass->grade ? [
                        'id' => $a->schoolClass->grade->id,
                        'code' => $a->schoolClass->grade->code,
                        'nameKm' => $a->schoolClass->grade->name_km,
                        'nameEn' => $a->schoolClass->grade->name_en,
                    ] : null,
                    'academicYear' => $a->schoolClass->academicYear ? [
                        'id' => $a->schoolClass->academicYear->id,
                        'name' => $a->schoolClass->academicYear->name,
                    ] : null,
                ] : null,
                'subject' => $a->subject ? [
                    'id' => $a->subject->id,
                    'code' => $a->subject->code,
                    'nameKm' => $a->subject->name_km,
                    'nameEn' => $a->subject->name_en,
                ] : null,
                'semester' => $a->semester ? [
                    'id' => $a->semester->id,
                    'name' => $a->semester->name,
                ] : null,
            ]);
    }

    public function getMyClassIds(User $user)
    {
        $teacher = $this->findByUserId($user->id);

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'ACTIVE')
            ->pluck('class_id')
            ->unique()
            ->values();
    }

    public function getMyClasses(User $user)
    {
        $classIds = $this->getMyClassIds($user);

        return SchoolClass::whereIn('id', $classIds)
            ->with(['grade', 'academicYear'])
            ->orderBy('id')
            ->get()
            ->map(fn($class) => [
                'id' => $class->id,
                'nameKm' => $class->name_km,
                'nameEn' => $class->name_en,
                'capacity' => $class->capacity,
                'status' => $class->status,
                'grade' => [
                    'id' => $class->grade?->id,
                    'code' => $class->grade?->code,
                    'nameKm' => $class->grade?->name_km,
                    'nameEn' => $class->grade?->name_en,
                ],
                'academicYear' => [
                    'id' => $class->academicYear?->id,
                    'name' => $class->academicYear?->name,
                ],
            ]);
    }

    public function getMySubjects(User $user)
    {
        $teacher = $this->findByUserId($user->id);

        $subjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'ACTIVE')
            ->pluck('subject_id')
            ->unique()
            ->values();

        return Subject::whereIn('id', $subjectIds)
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn($subject) => [
                'id' => $subject->id,
                'code' => $subject->code,
                'nameKm' => $subject->name_km,
                'nameEn' => $subject->name_en,
            ]);
    }

    public function getMyStudents(User $user)
    {
        $classIds = $this->getMyClassIds($user);

        return Student::whereHas('enrollments', function ($q) use ($classIds) {
            $q->whereIn('class_id', $classIds)->where('status', 'ACTIVE');
        })
            ->orderBy('id')
            ->get()
            ->map(fn($student) => [
                'id' => $student->id,
                'studentCode' => $student->student_code,
                'fullNameKm' => trim($student->first_name_km.' '.$student->last_name_km),
                'fullNameEn' => trim($student->first_name_en.' '.$student->last_name_en),
                'gender' => $student->gender,
                'phone' => $student->phone,
                'status' => $student->status,
            ]);
    }

    public function getMyTimetable(User $user)
    {
        $assignmentIds = $this->getMyAssignmentIds($user);

        return Timetable::whereIn('teacher_assignment_id', $assignmentIds)
            ->with(['teacherAssignment.subject', 'teacherAssignment.schoolClass.grade', 'room'])
            ->orderByRaw("CASE day_of_week
                WHEN 'MONDAY' THEN 1 WHEN 'TUESDAY' THEN 2 WHEN 'WEDNESDAY' THEN 3
                WHEN 'THURSDAY' THEN 4 WHEN 'FRIDAY' THEN 5 WHEN 'SATURDAY' THEN 6
                WHEN 'SUNDAY' THEN 7 ELSE 8 END")
            ->orderBy('start_time')
            ->get()
            ->map(function ($t) {
                $a = $t->teacherAssignment;
                $class = $a?->schoolClass;
                $subject = $a?->subject;
                $room = $t->room;

                return [
                    'id' => $t->id,
                    'dayOfWeek' => $t->day_of_week,
                    'startTime' => $t->start_time,
                    'endTime' => $t->end_time,
                    'class' => $class ? [
                        'id' => $class->id,
                        'nameKm' => $class->name_km,
                        'nameEn' => $class->name_en,
                        'grade' => $class->grade ? [
                            'id' => $class->grade->id,
                            'code' => $class->grade->code,
                            'nameKm' => $class->grade->name_km,
                            'nameEn' => $class->grade->name_en,
                        ] : null,
                    ] : null,
                    'subject' => $subject ? [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'nameKm' => $subject->name_km,
                        'nameEn' => $subject->name_en,
                    ] : null,
                    'room' => $room ? [
                        'id' => $room->id,
                        'nameKm' => $room->name_km,
                        'nameEn' => $room->name_en,
                        'buildingKm' => $room->building_km,
                        'buildingEn' => $room->building_en,
                    ] : null,
                ];
            });
    }

    public function getMyAttendanceSessions(User $user)
    {
        $assignmentIds = $this->getMyAssignmentIds($user);

        return AttendanceSession::whereIn('teacher_assignment_id', $assignmentIds)
            ->with(['teacherAssignment.subject', 'teacherAssignment.schoolClass'])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($session) {
                $a = $session->teacherAssignment;

                return [
                    'id' => $session->id,
                    'attendanceDate' => $session->attendance_date,
                    'startTime' => $session->start_time,
                    'endTime' => $session->end_time,
                    'status' => $session->status,
                    'class' => $a?->schoolClass ? [
                        'id' => $a->schoolClass->id,
                        'nameKm' => $a->schoolClass->name_km,
                        'nameEn' => $a->schoolClass->name_en,
                    ] : null,
                    'subject' => $a?->subject ? [
                        'id' => $a->subject->id,
                        'code' => $a->subject->code,
                        'nameKm' => $a->subject->name_km,
                        'nameEn' => $a->subject->name_en,
                    ] : null,
                ];
            });
    }

    public function getMyExams(User $user)
    {
        $assignmentIds = $this->getMyAssignmentIds($user);

        return Exam::whereHas('examSubjects', fn($q) =>
            $q->whereIn('teacher_assignment_id', $assignmentIds))
            ->with([
                'semester',
                'examSubjects' => fn($q) =>
                    $q->whereIn('teacher_assignment_id', $assignmentIds)
                        ->with(['teacherAssignment.subject', 'teacherAssignment.schoolClass']),
            ])
            ->orderByDesc('start_date')
            ->get()
            ->map(fn($exam) => [
                'id' => $exam->id,
                'nameKm' => $exam->name_km,
                'nameEn' => $exam->name_en,
                'examType' => $exam->exam_type,
                'startDate' => $exam->start_date,
                'endDate' => $exam->end_date,
                'status' => $exam->status,
                'semester' => $exam->semester ? [
                    'id' => $exam->semester->id,
                    'name' => $exam->semester->name,
                ] : null,
                'subjects' => $exam->examSubjects->map(fn($es) => [
                    'id' => $es->id,
                    'examDate' => $es->exam_date,
                    'startTime' => $es->start_time,
                    'endTime' => $es->end_time,
                    'maxScore' => $es->max_score,
                    'passScore' => $es->pass_score,
                    'subject' => $es->teacherAssignment?->subject ? [
                        'id' => $es->teacherAssignment->subject->id,
                        'code' => $es->teacherAssignment->subject->code,
                        'nameKm' => $es->teacherAssignment->subject->name_km,
                        'nameEn' => $es->teacherAssignment->subject->name_en,
                    ] : null,
                    'class' => $es->teacherAssignment?->schoolClass ? [
                        'id' => $es->teacherAssignment->schoolClass->id,
                        'nameKm' => $es->teacherAssignment->schoolClass->name_km,
                        'nameEn' => $es->teacherAssignment->schoolClass->name_en,
                    ] : null,
                ])->values(),
            ]);
    }

    public function getMyExamResults(User $user)
    {
        $assignmentIds = $this->getMyAssignmentIds($user);

        return ExamResult::whereHas('examSubject', fn($q) =>
            $q->whereIn('teacher_assignment_id', $assignmentIds))
            ->with([
                'student',
                'examSubject.exam',
                'examSubject.teacherAssignment.subject',
                'examSubject.teacherAssignment.schoolClass',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($result) {
                $student = $result->student;
                $examSubject = $result->examSubject;
                $exam = $examSubject?->exam;
                $a = $examSubject?->teacherAssignment;

                return [
                    'id' => $result->id,
                    'score' => $result->score,
                    'grade' => $result->grade,
                    'remarksKm' => $result->remarks_km,
                    'remarksEn' => $result->remarks_en,
                    'publishedAt' => $result->published_at,
                    'student' => $student ? [
                        'id' => $student->id,
                        'studentCode' => $student->student_code,
                        'fullNameKm' => trim($student->first_name_km.' '.$student->last_name_km),
                        'fullNameEn' => trim($student->first_name_en.' '.$student->last_name_en),
                    ] : null,
                    'exam' => $exam ? [
                        'id' => $exam->id,
                        'nameKm' => $exam->name_km,
                        'nameEn' => $exam->name_en,
                        'examType' => $exam->exam_type,
                    ] : null,
                    'subject' => $a?->subject ? [
                        'id' => $a->subject->id,
                        'code' => $a->subject->code,
                        'nameKm' => $a->subject->name_km,
                        'nameEn' => $a->subject->name_en,
                    ] : null,
                    'class' => $a?->schoolClass ? [
                        'id' => $a->schoolClass->id,
                        'nameKm' => $a->schoolClass->name_km,
                        'nameEn' => $a->schoolClass->name_en,
                    ] : null,
                ];
            });
    }

    public function getMyHomework(User $user)
    {
        $assignmentIds = $this->getMyAssignmentIds($user);

        return Assignment::whereIn('teacher_assignment_id', $assignmentIds)
            ->with(['teacherAssignment.subject', 'teacherAssignment.schoolClass'])
            ->orderByDesc('assigned_at')
            ->get()
            ->map(function ($homework) {
                $a = $homework->teacherAssignment;

                return [
                    'id' => $homework->id,
                    'titleKm' => $homework->title_km,
                    'titleEn' => $homework->title_en,
                    'descriptionKm' => $homework->description_km,
                    'descriptionEn' => $homework->description_en,
                    'assignedAt' => $homework->assigned_at,
                    'dueAt' => $homework->due_at,
                    'maxScore' => $homework->max_score,
                    'status' => $homework->status,
                    'class' => $a?->schoolClass ? [
                        'id' => $a->schoolClass->id,
                        'nameKm' => $a->schoolClass->name_km,
                        'nameEn' => $a->schoolClass->name_en,
                    ] : null,
                    'subject' => $a?->subject ? [
                        'id' => $a->subject->id,
                        'code' => $a->subject->code,
                        'nameKm' => $a->subject->name_km,
                        'nameEn' => $a->subject->name_en,
                    ] : null,
                ];
            });
    }

    public function getMySubmissions(User $user)
    {
        $assignmentIds = $this->getMyAssignmentIds($user);

        return AssignmentSubmission::whereHas('assignment', fn($q) =>
            $q->whereIn('teacher_assignment_id', $assignmentIds))
            ->with([
                'student',
                'assignment.teacherAssignment.subject',
                'assignment.teacherAssignment.schoolClass',
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($s) {
                $student = $s->student;
                $assignment = $s->assignment;
                $ta = $assignment?->teacherAssignment;

                return [
                    'id' => $s->id,
                    'submittedAt' => $s->submitted_at,
                    'content' => $s->content,
                    'fileUrl' => $s->file_url,
                    'score' => $s->score,
                    'feedbackKm' => $s->feedback_km,
                    'feedbackEn' => $s->feedback_en,
                    'status' => $s->status,
                    'student' => $student ? [
                        'id' => $student->id,
                        'studentCode' => $student->student_code,
                        'fullNameKm' => trim($student->first_name_km.' '.$student->last_name_km),
                        'fullNameEn' => trim($student->first_name_en.' '.$student->last_name_en),
                    ] : null,
                    'assignment' => $assignment ? [
                        'id' => $assignment->id,
                        'titleKm' => $assignment->title_km,
                        'titleEn' => $assignment->title_en,
                        'dueAt' => $assignment->due_at,
                        'maxScore' => $assignment->max_score,
                    ] : null,
                    'subject' => $ta?->subject ? [
                        'id' => $ta->subject->id,
                        'code' => $ta->subject->code,
                        'nameKm' => $ta->subject->name_km,
                        'nameEn' => $ta->subject->name_en,
                    ] : null,
                    'class' => $ta?->schoolClass ? [
                        'id' => $ta->schoolClass->id,
                        'nameKm' => $ta->schoolClass->name_km,
                        'nameEn' => $ta->schoolClass->name_en,
                    ] : null,
                ];
            });
    }

    public function getMyLearningMaterials(User $user)
    {
        $assignmentIds = $this->getMyAssignmentIds($user);

        return LearningMaterial::whereIn('teacher_assignment_id', $assignmentIds)
            ->with(['teacherAssignment.subject', 'teacherAssignment.schoolClass'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($m) {
                $a = $m->teacherAssignment;

                return [
                    'id' => $m->id,
                    'titleKm' => $m->title_km,
                    'titleEn' => $m->title_en,
                    'descriptionKm' => $m->description_km,
                    'descriptionEn' => $m->description_en,
                    'materialType' => $m->material_type,
                    'fileUrl' => $m->file_url,
                    'publishedAt' => $m->published_at,
                    'subject' => $a?->subject ? [
                        'id' => $a->subject->id,
                        'code' => $a->subject->code,
                        'nameKm' => $a->subject->name_km,
                        'nameEn' => $a->subject->name_en,
                    ] : null,
                    'class' => $a?->schoolClass ? [
                        'id' => $a->schoolClass->id,
                        'nameKm' => $a->schoolClass->name_km,
                        'nameEn' => $a->schoolClass->name_en,
                    ] : null,
                ];
            });
    }

    public function canAccessClass(User $user, int $classId): bool
    {
        $teacher = $this->findByUserId($user->id);

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('status', 'ACTIVE')
            ->exists();
    }

    public function canAccessStudent(User $user, int $studentId): bool
    {
        $classIds = $this->getMyClassIds($user);

        return Student::whereKey($studentId)
            ->whereHas('enrollments', fn($q) =>
                $q->whereIn('class_id', $classIds)->where('status', 'ACTIVE'))
            ->exists();
    }

    public function paginate(array $filters, User $user): LengthAwarePaginator
    {
        $query = $this->applyAccessScope(
            Teacher::query()->with('user.role'),
            $user
        );

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('teacher_code', 'ilike', "%{$search}%")
                    ->orWhere('first_name_km', 'ilike', "%{$search}%")
                    ->orWhere('last_name_km', 'ilike', "%{$search}%")
                    ->orWhere('first_name_en', 'ilike', "%{$search}%")
                    ->orWhere('last_name_en', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhereHas('user', fn($u) =>
                        $u->where('email', 'ilike', "%{$search}%")
                            ->orWhere('username', 'ilike', "%{$search}%"));
            });
        }

        if (!empty($filters['gender'])) $query->where('gender', $filters['gender']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);

        return $query->orderBy('id')->paginate($filters['size'] ?? 10);
    }
}