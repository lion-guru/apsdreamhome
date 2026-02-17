<?php

use App\Core\Database\Migration;

class AddEmailVerificationToUsersTable extends Migration {
    public function up() {
        $this->schema->table('users', function ($table) {
            $table->timestamp('email_verified_at')->nullable()->after('status');
        });
    }

    public function down() {
        $this->schema->table('users', function ($table) {
            $table->dropColumn('email_verified_at');
        });
    }
}
