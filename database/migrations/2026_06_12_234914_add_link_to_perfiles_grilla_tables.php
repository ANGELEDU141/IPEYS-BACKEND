<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('perfiles_grilla', function (Blueprint $table) {
        // Añadimos la columna 'link' después del campo 'locales'
        $table->string('link')->nullable()->after('locales');
    });
}

public function down(): void
{
    Schema::table('perfiles_grilla', function (Blueprint $table) {
        $table->dropColumn('link');
    });
}
};
