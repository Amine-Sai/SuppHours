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
            // $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->date('startDate');
            $table->date('endDate');   
            $table->timestamps();       
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('periods');
    }
};
