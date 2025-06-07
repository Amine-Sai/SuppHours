<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $casts = [
    'grades' => 'json',
];
    public function up(): void
    {
        Schema::create('teachers', function(Blueprint $table) {
            $table->id();
            $table->string('fullName');
            $table->string('email');
            $table->boolean('isVacateur')->default(false);
            // each object will contain: grade_id , start_date
            $table->json('grades')->nullable();
            $table->timestamps();
        });
    }
    

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};