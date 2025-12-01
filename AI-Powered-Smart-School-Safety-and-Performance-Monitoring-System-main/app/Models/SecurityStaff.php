<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityStaff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'security_staff';

    protected $primaryKey = 'security_id';

    protected $fillable = [
        'user_id',
        'security_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'nationality',
        'joining_date',
        'employee_id',
        'shift',
        'position',
        'photo_path',
        'is_active',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'home_phone',
        'mobile_phone',
        'email',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    public function scopeByShift($query, $shift)
    {
        return $query->where('shift', $shift);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    // Static methods
    public static function generateSecurityCode()
    {
        $year = date('Y');
        $lastSecurity = self::whereYear('joining_date', $year)
            ->orderBy('security_id', 'desc')
            ->first();

        $sequence = $lastSecurity ? (int) substr($lastSecurity->security_code, -4) + 1 : 1;

        return 'SEC'.$year.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
