<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_persons', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('test_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->constrained('test_persons');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('test_booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('test_bookings');
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_booking_items');
        Schema::dropIfExists('test_bookings');
        Schema::dropIfExists('test_persons');
    }
};
