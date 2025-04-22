<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');  // Foreign key to teacher
            $table->date('startDate');  // Start date of the period
            $table->date('endDate');    // End date of the period
            $table->timestamps();       // Timestamps for created_at and updated_at
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('periods');
    }
};
