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
        Schema::create('notifier_event_channel_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('event_key'); // e.g., 'project.completed'
            $table->json('channels')->nullable(); // ['email', 'slack']
            $table->timestamps();

            // Unique per tenant + event
            $table->unique(['tenant_id', 'event_key'], 'notifier_ecs_tenant_event_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifier_event_channel_settings');
    }
};
