<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Role Repository
        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Setup\RoleRepositoryInterface::class,
            \App\Repositories\Admin\Setup\RoleRepository::class
        );

        // Bind User Repository
        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Setup\UserRepositoryInterface::class,
            \App\Repositories\Admin\Setup\UserRepository::class
        );

        // Bind Settings Repository
        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Setup\SettingsRepositoryInterface::class,
            \App\Repositories\Admin\Setup\SettingsRepository::class
        );

        // Bind Management Repositories
        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Management\StudentRepositoryInterface::class,
            \App\Repositories\Admin\Management\StudentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Management\TeacherRepositoryInterface::class,
            \App\Repositories\Admin\Management\TeacherRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Management\ParentRepositoryInterface::class,
            \App\Repositories\Admin\Management\ParentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Management\SchoolClassRepositoryInterface::class,
            \App\Repositories\Admin\Management\SchoolClassRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Management\SubjectRepositoryInterface::class,
            \App\Repositories\Admin\Management\SubjectRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Management\SecurityStaffRepositoryInterface::class,
            \App\Repositories\Admin\Management\SecurityStaffRepository::class
        );

        $this->app->bind(
            \App\Repositories\Interfaces\Admin\Management\AttendanceRepositoryInterface::class,
            \App\Repositories\Admin\Management\AttendanceRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
