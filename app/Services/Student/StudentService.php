<?php

namespace App\Services\Student;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Timetable;
use App\Models\LearningMaterial;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;

class StudentService
{
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $studentRole = Role::where('code', 'STUDENT')
                ->where('is_active', true)
                ->firstOrFail();

            $user = User::create([
                'role_id' => $studentRole->id,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'preferred_language' =>
                $data['preferredLanguage'] ?? 'KM',
                'status' => 'ACTIVE',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_code' => $data['studentCode'],
                'first_name_km' => $data['firstNameKm'],
                'last_name_km' => $data['lastNameKm'],
                'first_name_en' =>
                $data['firstNameEn'] ?? null,
                'last_name_en' =>
                $data['lastNameEn'] ?? null,
                'gender' => $data['gender'],
                'date_of_birth' =>
                $data['dateOfBirth'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address_km' =>
                $data['addressKm'] ?? null,
                'address_en' =>
                $data['addressEn'] ?? null,
                'admission_date' =>
                $data['admissionDate'] ?? null,
                'status' =>
                $data['status'] ?? 'ACTIVE',
            ]);

            return $student->load('user.role');
        });
    }

    public function update(
        Student $student,
        array $data
    ): Student {
        return DB::transaction(function () use (
            $student,
            $data
        ) {
            $user = $student->user;

            if (array_key_exists('username', $data)) {
                $user->username = $data['username'];
            }

            if (array_key_exists('email', $data)) {
                $user->email = $data['email'];
            }

            if (array_key_exists(
                'preferredLanguage',
                $data
            )) {
                $user->preferred_language =
                    $data['preferredLanguage'];
            }

            if (
                array_key_exists('password', $data)
                && !empty($data['password'])
            ) {
                $user->password = Hash::make(
                    $data['password']
                );
            }

            $user->save();

            if (array_key_exists('studentCode', $data)) {
                $student->student_code =
                    $data['studentCode'];
            }

            if (array_key_exists('firstNameKm', $data)) {
                $student->first_name_km =
                    $data['firstNameKm'];
            }

            if (array_key_exists('lastNameKm', $data)) {
                $student->last_name_km =
                    $data['lastNameKm'];
            }

            if (array_key_exists('firstNameEn', $data)) {
                $student->first_name_en =
                    $data['firstNameEn'];
            }

            if (array_key_exists('lastNameEn', $data)) {
                $student->last_name_en =
                    $data['lastNameEn'];
            }

            if (array_key_exists('gender', $data)) {
                $student->gender = $data['gender'];
            }

            if (array_key_exists('dateOfBirth', $data)) {
                $student->date_of_birth =
                    $data['dateOfBirth'];
            }

            if (array_key_exists('phone', $data)) {
                $student->phone = $data['phone'];
            }

            if (array_key_exists('addressKm', $data)) {
                $student->address_km =
                    $data['addressKm'];
            }

            if (array_key_exists('addressEn', $data)) {
                $student->address_en =
                    $data['addressEn'];
            }

            if (array_key_exists('admissionDate', $data)) {
                $student->admission_date =
                    $data['admissionDate'];
            }

            $student->save();

            return $student->load('user.role');
        });
    }

    public function updateStatus(
        Student $student,
        string $status
    ): Student {
        return DB::transaction(function () use (
            $student,
            $status
        ) {
            $student->update([
                'status' => $status,
            ]);

            if ($status === 'ACTIVE') {
                $student->user->update([
                    'status' => 'ACTIVE',
                ]);
            }

            if (in_array(
                $status,
                ['INACTIVE', 'SUSPENDED'],
                true
            )) {
                $student->user->update([
                    'status' => 'INACTIVE',
                ]);
            }

            return $student->load('user.role');
        });
    }

    public function findByUserId(int $userId): Student
    {
        return Student::query()
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function applyAccessScope(
        Builder $query,
        User $user
    ): Builder {
        $role = $user->role->code;

        if (in_array($role, [
            'SUPER_ADMIN',
            'ADMIN',
        ], true)) {
            return $query;
        }

        if ($role === 'STUDENT') {
            return $query->where(
                'user_id',
                $user->id
            );
        }

        if ($role === 'PARENT') {
            return $query->whereHas(
                'parents',
                function ($q) use ($user) {
                    $q->where(
                        'parents.user_id',
                        $user->id
                    );
                }
            );
        }

        if ($role === 'TEACHER') {
            return $query->whereHas(
                'enrollments.schoolClass'
                    . '.teacherAssignments.teacher',
                function ($q) use ($user) {
                    $q->where(
                        'user_id',
                        $user->id
                    );
                }
            );
        }

        if (in_array($role, [
            'ACCOUNTANT',
            'LIBRARIAN',
        ], true)) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    // For parent and teacher roles, find a student by ID only if they have access to that student
    public function findAccessibleStudent(
        User $user,
        int $studentId
    ): Student {
        $query = Student::query();

        $this->applyAccessScope(
            $query,
            $user
        );

        return $query
            ->whereKey($studentId)
            ->firstOrFail();
    }

    public function getMyEnrollments(User $user)
    {
        $student = $this->findByUserId($user->id);

        return $student->enrollments()
            ->with([
                'schoolClass.grade',
                'academicYear',
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getMyAttendance(User $user)
    {
        $student = $this->findByUserId($user->id);

        return $student->attendanceRecords()
            ->with('attendanceSession')
            ->orderByDesc('id')
            ->get();
    }

    public function getMyExamResults(User $user)
    {
        $student = $this->findByUserId($user->id);

        return $student->examResults()
            ->with([
                'examSubject.exam',
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getMySubmissions(User $user)
    {
        $student = $this->findByUserId($user->id);

        return $student->assignmentSubmissions()
            ->with('assignment')
            ->orderByDesc('id')
            ->get();
    }

    public function getMyFees(User $user)
    {
        $student = $this->findByUserId($user->id);

        return $student->studentFees()
            ->with([
                'feeType',
                'academicYear',
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getMyScholarships(User $user)
    {
        $student = $this->findByUserId($user->id);

        return $student->studentScholarships()
            ->with([
                'scholarship',
                'academicYear',
            ])
            ->orderByDesc('id')
            ->get();
    }

    public function getMyAssignments(User $user)
    {
        $student = $this->findByUserId($user->id);

        $classIds = $student->enrollments()
            ->pluck('class_id');

        return \App\Models\Assignment::query()
            ->whereHas(
                'teacherAssignment',
                function ($query) use ($classIds) {
                    $query->whereIn(
                        'class_id',
                        $classIds
                    );
                }
            )
            ->with('teacherAssignment')
            ->orderByDesc('id')
            ->get();
    }

    public function getMyTimetable(User $user)
    {
        $student = $this->findByUserId($user->id);

        $classIds = $student->enrollments()
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        return Timetable::query()
            ->whereHas(
                'teacherAssignment',
                function ($query) use ($classIds) {
                    $query->whereIn(
                        'class_id',
                        $classIds
                    );
                }
            )
            ->with([
                'teacherAssignment.teacher',
                'teacherAssignment.schoolClass',
                'teacherAssignment.subject',
                'room',
            ])
            ->orderByRaw("
            CASE day_of_week
                WHEN 'MONDAY' THEN 1
                WHEN 'TUESDAY' THEN 2
                WHEN 'WEDNESDAY' THEN 3
                WHEN 'THURSDAY' THEN 4
                WHEN 'FRIDAY' THEN 5
                WHEN 'SATURDAY' THEN 6
                WHEN 'SUNDAY' THEN 7
                ELSE 8
            END
        ")
            ->orderBy('start_time')
            ->get();
    }

    public function getMyLearningMaterials(User $user)
    {
        $student = $this->findByUserId($user->id);

        $classIds = $student->enrollments()
            ->where('status', 'ACTIVE')
            ->pluck('class_id');

        return LearningMaterial::query()
            ->whereHas(
                'teacherAssignment',
                function ($query) use ($classIds) {
                    $query->whereIn(
                        'class_id',
                        $classIds
                    );
                }
            )
            ->with([
                'teacherAssignment.subject',
                'teacherAssignment.teacher',
                'teacherAssignment.schoolClass',
            ])
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get();
    }

    public function getMyInvoices(User $user)
    {
        $student = $this->findByUserId($user->id);

        return Invoice::query()
            ->where(
                'student_id',
                $student->id
            )
            ->with([
                'academicYear',
                'items',
            ])
            ->orderByDesc('issued_date')
            ->get();
    }
    public function getMyPayments(User $user)
    {
        $student = $this->findByUserId($user->id);

        return Payment::query()
            ->whereHas(
                'invoice',
                function ($query) use ($student) {
                    $query->where(
                        'student_id',
                        $student->id
                    );
                }
            )
            ->with([
                'invoice',
                'paymentMethod',
                'receivedBy',
            ])
            ->orderByDesc('paid_at')
            ->get();
    }
    public function getMyReceipts(User $user)
    {
        $student = $this->findByUserId($user->id);

        return Receipt::query()
            ->whereHas(
                'payment.invoice',
                function ($query) use ($student) {
                    $query->where(
                        'student_id',
                        $student->id
                    );
                }
            )
            ->with([
                'payment.invoice',
                'payment.paymentMethod',
            ])
            ->orderByDesc('issued_at')
            ->get();
    }
}
