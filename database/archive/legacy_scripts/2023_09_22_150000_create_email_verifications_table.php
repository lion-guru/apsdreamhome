<?php

use App\Core\Database\Migration;

class CreateEmailVerificationsTable extends Migration {
    public function up() {
        $this->schema->create('email_verifications', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('token', 64);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->index('token');
        });
    }
    
    public function down() {
        $this->schema->dropIfExists('email_verifications');
    }
}
