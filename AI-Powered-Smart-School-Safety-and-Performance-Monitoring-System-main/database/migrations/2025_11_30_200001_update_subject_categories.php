<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PRIMARY EDUCATION (1-5) - First Language
        DB::table('subjects')
            ->where('grade_level', '1-5')
            ->where('subject_name', 'like', '%First Language%')
            ->update(['category' => 'First Language']);

        // PRIMARY EDUCATION (1-5) - Religion
        DB::table('subjects')
            ->where('grade_level', '1-5')
            ->where('subject_name', 'like', 'Religion%')
            ->update(['category' => 'Religion']);

        // PRIMARY EDUCATION (1-5) - Aesthetic Studies
        DB::table('subjects')
            ->where('grade_level', '1-5')
            ->where('subject_name', 'like', '%Aesthetic%')
            ->update(['category' => 'Aesthetic Studies']);

        // PRIMARY EDUCATION (1-5) - Core required (whatever is still Core)
        DB::table('subjects')
            ->where('grade_level', '1-5')
            ->where('category', 'Core')
            ->update(['is_required' => true]);

        // SECONDARY EDUCATION (6-11) - First Language
        DB::table('subjects')
            ->where('grade_level', '6-11')
            ->where('subject_name', 'like', '%First Language%')
            ->update(['category' => 'First Language']);

        // SECONDARY EDUCATION (6-11) - Religion
        DB::table('subjects')
            ->where('grade_level', '6-11')
            ->where('subject_name', 'like', 'Religion%')
            ->update(['category' => 'Religion']);

        // SECONDARY EDUCATION (6-11) - Elective subjects
        DB::table('subjects')
            ->where('grade_level', '6-11')
            ->where('type', 'Elective')
            ->update(['category' => 'Elective', 'is_required' => false]);

        // SECONDARY EDUCATION (6-11) - Core required (type=Core and still category=Core)
        DB::table('subjects')
            ->where('grade_level', '6-11')
            ->where('type', 'Core')
            ->where('category', 'Core')
            ->update(['is_required' => true]);

        // ADVANCED LEVEL (12-13) - Set stream
        $streams = ['Arts', 'Commerce', 'Science', 'Technology'];
        foreach ($streams as $stream) {
            DB::table('subjects')
                ->where('grade_level', '12-13')
                ->where('type', $stream . ' Stream')
                ->update(['stream' => $stream, 'category' => 'Elective']);
        }
    }

    public function down(): void
    {
        DB::table('subjects')->update(['category' => 'Core', 'is_required' => false, 'stream' => null]);
    }
};

