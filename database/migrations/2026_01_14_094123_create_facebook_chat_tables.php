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
        Schema::create('facebook_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('page_id')->nullable()->index();
            $table->string('name');
            $table->string('color')->default('#6bb9f0');
            $table->timestamps();
        });

        Schema::create('facebook_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('facebook_id')->unique(); // The conversation ID from Graph API
            $table->string('page_id')->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Owner of the page
            $table->string('participant_name')->nullable();
            $table->string('participant_id')->nullable(); // PSID
            $table->text('snippet')->nullable();
            $table->timestamp('updated_time')->nullable();
            $table->integer('unread_count')->default(0);
            $table->boolean('can_reply')->default(true);
            $table->timestamps();
        });

        Schema::create('facebook_messages', function (Blueprint $table) {
            $table->id();
            $table->string('facebook_id')->unique();
            $table->foreignId('conversation_id')->constrained('facebook_conversations')->cascadeOnDelete();
            $table->string('sender_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('message')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('created_time')->nullable();
            $table->text('sticker')->nullable(); // For stickers
            $table->timestamps();
        });

        Schema::create('facebook_conversation_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('facebook_conversations')->cascadeOnDelete();
            $table->foreignId('label_id')->constrained('facebook_labels')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_conversation_labels');
        Schema::dropIfExists('facebook_messages');
        Schema::dropIfExists('facebook_conversations');
        Schema::dropIfExists('facebook_labels');
    }
};
