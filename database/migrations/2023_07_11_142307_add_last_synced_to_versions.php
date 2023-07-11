<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->timestamp('last_synced_at')->after('json_file')->nullable();
        });
    }
};
