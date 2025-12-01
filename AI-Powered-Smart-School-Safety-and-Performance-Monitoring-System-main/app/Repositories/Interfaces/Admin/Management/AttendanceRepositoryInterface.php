<?php

namespace App\Repositories\Interfaces\Admin\Management;

interface AttendanceRepositoryInterface
{
    /**
     * Get all attendance records
     */
    public function getAll();

    /**
     * Get attendance by ID
     */
    public function getById($id);

    /**
     * Create new attendance record
     */
    public function create(array $data);

    /**
     * Update attendance record
     */
    public function update($id, array $data);

    /**
     * Delete attendance record
     */
    public function delete($id);

    /**
     * Get attendance for specific date
     */
    public function getByDate($date);

    /**
     * Get attendance for specific student
     */
    public function getByStudent($studentId);

    /**
     * Get today's attendance
     */
    public function getToday();

    /**
     * Get attendance by status
     */
    public function getByStatus($status);

    /**
     * Check in student
     */
    public function checkIn($studentId, array $data = []);

    /**
     * Check out student
     */
    public function checkOut($studentId, array $data = []);

    /**
     * Get attendance statistics
     */
    public function getStatistics($startDate = null, $endDate = null);

    /**
     * Get student attendance percentage
     */
    public function getStudentAttendancePercentage($studentId, $startDate = null, $endDate = null);

    /**
     * Mark student as absent for a date
     */
    public function markAbsent($studentId, $date, array $data = []);

    /**
     * Get attendance report
     */
    public function getReport($filters = []);

    /**
     * Get today's attendance for a specific student
     */
    public function getTodayAttendance($studentId);
}
