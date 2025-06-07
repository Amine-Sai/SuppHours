<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('holidays', function (Blueprint $table) {
        $table->id();
        $table->date('startDate');  
        $table->integer('duration');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('holidays');
}

};
