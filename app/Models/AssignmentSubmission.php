<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AssignmentSubmission extends Model {
 use HasFactory;
 protected $fillable=['assignment_id','student_id','submitted_at','content','file_url','score','feedback_km','feedback_en','status'];
 protected function casts(): array { return ['assignment_id'=>'integer','student_id'=>'integer','submitted_at'=>'datetime','score'=>'decimal:2']; }
 public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
 public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
