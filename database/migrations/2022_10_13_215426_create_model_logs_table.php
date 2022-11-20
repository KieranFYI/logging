<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->nullableMorphs('user');
            $table->string('level')
                ->index();
            $table->text('message');
            $table->longText('context')
                ->nullable();
            $table->longText('data')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['level', 'model_type', 'model_id']);
            $table->index(['level', 'model_type']);
            $table->index(['level', 'user_type', 'user_id']);
            $table->index(['level', 'model_type', 'user_type', 'user_id']);
            $table->index(['level', 'model_type', 'model_id', 'user_type', 'user_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_logs');
    }
};
