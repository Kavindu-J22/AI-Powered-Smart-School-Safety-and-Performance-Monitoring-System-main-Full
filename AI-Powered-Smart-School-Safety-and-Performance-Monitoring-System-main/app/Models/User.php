<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Status;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'usertype',
        'status',
        'phone',
        'address',
        'bio',
        'date_of_birth',
        'profile_image',
        'login_count',
        'last_login_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'usertype' => UserType::class,
            'status' => Status::class,
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // School Management Relationships
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function parentModel()
    {
        return $this->hasOne(\App\Models\ParentModel::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function securityStaff()
    {
        return $this->hasOne(SecurityStaff::class);
    }

    // Helper methods for school management
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function isParent()
    {
        return $this->hasRole('parent');
    }

    public function isSecurity()
    {
        return $this->hasRole('security');
    }

    public function getSchoolProfile()
    {
        if ($this->isTeacher()) {
            return $this->teacher;
        } elseif ($this->isStudent()) {
            return $this->student;
        } elseif ($this->isParent()) {
            return $this->parentModel;
        } elseif ($this->isSecurity()) {
            return $this->securityStaff;
        }

        return null;
    }

    public function getSchoolProfileType()
    {
        if ($this->isTeacher()) {
            return 'teacher';
        } elseif ($this->isStudent()) {
            return 'student';
        } elseif ($this->isParent()) {
            return 'parent';
        } elseif ($this->isSecurity()) {
            return 'security';
        }

        return 'user';
    }

    public function assignSchoolRole($roleName)
    {
        // Remove any existing school roles first
        $schoolRoles = ['teacher', 'student', 'parent', 'security'];
        foreach ($schoolRoles as $role) {
            if ($this->hasRole($role)) {
                $this->removeRole($role);
            }
        }

        // Assign the new role
        $this->assignRole($roleName);

        // Update usertype enum based on role
        $userTypeMapping = [
            'admin' => UserType::ADMIN,
            'teacher' => UserType::TEACHER,
            'student' => UserType::STUDENT,
            'parent' => UserType::PARENT,
            'security' => UserType::SECURITY,
        ];

        if (isset($userTypeMapping[$roleName])) {
            $this->usertype = $userTypeMapping[$roleName];
            $this->save();
        }
    }
}
