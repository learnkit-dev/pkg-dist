<?php

use App\Models\Package;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();

            $table->string('key')->nullable();
            $table->foreignIdFor(Package::class)->index();
            $table->boolean('is_active')->default(true);

            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
