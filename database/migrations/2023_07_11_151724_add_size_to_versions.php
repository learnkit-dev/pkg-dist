<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->integer('size')->after('status')->nullable();
        });
    }
};
