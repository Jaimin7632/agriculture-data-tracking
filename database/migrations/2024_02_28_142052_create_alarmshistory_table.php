<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alarmshistory', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('device_id');
            $table->string('alarmvalue');
            $table->string('actualvalue');
            $table->string('sensorname');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alarmshistory');
    }
};
