<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perfiles_grilla', function (Blueprint $table) {
            $table->string('direccion', 255)->nullable()->after('logo_base64');
            $table->text('experiencia')->nullable()->after('direccion');
            $table->text('especializacion')->nullable()->after('experiencia');
            $table->string('contacto', 255)->nullable()->after('especializacion');
            $table->text('locales')->nullable()->after('contacto');
        });
    }

    public function down(): void
    {
        Schema::table('perfiles_grilla', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'experiencia', 'especializacion', 'contacto', 'locales']);
        });
    }
};
