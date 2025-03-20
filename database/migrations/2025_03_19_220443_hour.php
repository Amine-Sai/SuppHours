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
    Schema::create('hours', function (Blueprint $table) {
        $table->id();
        $table->enum('type',['Cours', 'td', 'tp', 'supp']); 
        $table->enum('state', ['intern', 'extern']);
        $table->foreignId('day_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('hours');
}
};
