<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->json('json_file')->nullable()->after('version_normalized');
        });
    }
};
