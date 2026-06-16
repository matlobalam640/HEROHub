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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained('memberships')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);

            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('id_number')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            $table->string('qr_token')->unique(); // used to verify membership via QR
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
