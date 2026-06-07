<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeria_modales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_id')->constrained('perfiles_grilla')->cascadeOnDelete();
            $table->longText('imagen_base64');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeria_modales');
    }
};
