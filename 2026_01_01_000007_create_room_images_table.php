<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('image_path', 255);
            $table->boolean('is_cover')->default(false);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->index(['room_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_images');
    }
};
