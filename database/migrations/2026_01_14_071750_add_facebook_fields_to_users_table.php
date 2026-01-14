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
        Schema::table('users', function (Blueprint $table) {
            $table->text('facebook_access_token')->nullable()->after('facebook_id');
            $table->timestamp('facebook_token_expires_at')->nullable()->after('facebook_access_token');
            $table->text('facebook_refresh_token')->nullable()->after('facebook_token_expires_at');
            $table->string('facebook_profile_picture')->nullable()->after('facebook_refresh_token');
            $table->json('facebook_pages')->nullable()->after('facebook_profile_picture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_access_token',
                'facebook_token_expires_at',
                'facebook_refresh_token',
                'facebook_profile_picture',
                'facebook_pages',
            ]);
        });
    }
};
