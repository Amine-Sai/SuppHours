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
    Schema::create('holidays', function (Blueprint $table) {
        $table->id();
        $table->date('startDate');  // Start date of the holiday
        $table->integer('duration');  // Duration in days
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('holidays');
}

};
