<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Assignment extends Model {
 use HasFactory;
 protected $fillable=['teacher_assignment_id','title_km','title_en','description_km','description_en','assigned_at','due_at','max_score','status'];
 protected function casts(): array { return ['teacher_assignment_id'=>'integer','assigned_at'=>'datetime','due_at'=>'datetime','max_score'=>'decimal:2']; }
 public function teacherAssignment(): BelongsTo { return $this->belongsTo(TeacherAssignment::class); }
 public function submissions(): HasMany { return $this->hasMany(AssignmentSubmission::class); }
}
