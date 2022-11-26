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
            $table->string('level', 32)
                ->index();
            $table->text('message');
            $table->longText('context')
                ->nullable();
            $table->longText('data')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['level', 'model_type', 'model_id'], 'model_logs_index_1');
            $table->index(['level', 'model_type'], 'model_logs_index_2');
            $table->index(['level', 'user_type', 'user_id'], 'model_logs_index_3');
            $table->index(['level', 'model_type', 'user_type', 'user_id'], 'model_logs_index_4');
            $table->index(['level', 'model_type', 'model_id', 'user_type', 'user_id'], 'model_logs_index_5');

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
