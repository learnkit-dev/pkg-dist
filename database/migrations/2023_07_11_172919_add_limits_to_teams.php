<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->after('user_id', function (Blueprint $table) {
                $table->integer('limit_packages')->nullable();
                $table->integer('limit_version_per_package')->nullable();
                $table->integer('limit_licenses')->nullable();
                $table->integer('limit_storage_usage')->nullable();
            });
        });
    }
};
