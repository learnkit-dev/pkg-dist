<?php

use App\Models\Package;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Package::class)->index();

            $table->string('version');
            $table->string('version_normalized')->nullable();

            $table->timestamps();
        });
    }
};
