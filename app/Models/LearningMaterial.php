<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LearningMaterial extends Model {
 use HasFactory;
 protected $fillable=['teacher_assignment_id','title_km','title_en','description_km','description_en','material_type','file_url','published_at'];
 protected function casts(): array { return ['teacher_assignment_id'=>'integer','published_at'=>'datetime']; }
 public function teacherAssignment(): BelongsTo { return $this->belongsTo(TeacherAssignment::class); }
}
