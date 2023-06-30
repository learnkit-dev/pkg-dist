<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)->index();
            $table->foreignIdFor(Team::class)->index();

            $table->timestamps();
        });
    }
};
