<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes20260225073131 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Indexes for leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['agent_id', 'status'], 'leads_agent_status_index');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['priority'], 'leads_priority_index');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['created_at'], 'leads_created_at_index');
        });

        // Indexes for payouts table
        Schema::table('payouts', function (Blueprint $table) {
            $table->index(['associate_id', 'status'], 'payouts_associate_status_index');
        });
        Schema::table('payouts', function (Blueprint $table) {
            $table->index(['created_at'], 'payouts_created_at_index');
        });

        // Indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email'], 'users_email_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index(['status'], 'users_status_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index(['created_at'], 'users_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        // Drop indexes for leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_agent_status_index');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_priority_index');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_created_at_index');
        });

        // Drop indexes for payouts table
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropIndex('payouts_associate_status_index');
        });
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropIndex('payouts_created_at_index');
        });

        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_at_index');
        });
    }
}
