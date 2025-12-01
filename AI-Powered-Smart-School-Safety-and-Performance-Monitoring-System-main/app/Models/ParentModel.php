<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parents';

    protected $primaryKey = 'parent_id';

    protected $fillable = [
        'user_id',
        'parent_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'nationality',
        'occupation',
        'workplace',
        'photo_path',
        'relationship_type',
        'is_emergency_contact',
        'is_active',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'home_phone',
        'mobile_phone',
        'work_phone',
        'email',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_emergency_contact' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('is_primary_contact')
            ->withTimestamps();
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim($this->first_name.' '.$this->middle_name.' '.$this->last_name);
    }

    public function getFullAddressAttribute()
    {
        return trim($this->address_line1.' '.$this->address_line2.', '.$this->city.', '.$this->state.' '.$this->postal_code.', '.$this->country);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEmergencyContacts($query)
    {
        return $query->where('is_emergency_contact', true);
    }

    public function scopeByRelationship($query, $relationship)
    {
        return $query->where('relationship_type', $relationship);
    }

    // Static methods
    public static function generateParentCode()
    {
        $lastParent = self::orderBy('parent_id', 'desc')->first();
        $sequence = $lastParent ? (int) substr($lastParent->parent_code, 3) + 1 : 1;

        return 'pa-'.str_pad($sequence, 8, '0', STR_PAD_LEFT);
    }
}
