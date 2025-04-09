<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('retros_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retro_id')->constrained('retros')->onDelete('cascade');
            $table->foreignId('retros_column_id')->constrained('retros_columns')->onDelete('cascade');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retros_elements');
    }
};

