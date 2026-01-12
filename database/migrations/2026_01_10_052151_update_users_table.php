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
            $table->string('first_name')->after('id')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('username')->unique()->after('last_name')->nullable();
            $table->string('phone')->unique()->after('username')->nullable();
            $table->string('gender')->nullable()->after('phone');
            $table->date('dob')->nullable()->after('gender');
            $table->string('profile')->nullable()->after('dob');
            $table->timestamp('last_login_at')->nullable()->after('updated_at');

            // Check if column exists before dropping to avoid errors if re-running
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
             if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->after('id');
            }
            $table->dropColumn([
                'first_name',
                'last_name',
                'username',
                'phone',
                'gender',
                'dob',
                'profile',
                'last_login_at',
            ]);
        });
    }
};
