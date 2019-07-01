<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class Mbp15BatteryRepairProgramInitialization extends Migration
{
    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->create('mbp15_battery_repair_program', function (Blueprint $table) {
            $table->increments('id');
            $table->string('serial_number')->unique();
            $table->string('eligibility');
            $table->string('datecheck');

            $table->index('eligibility');
            $table->index('datecheck');
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->dropIfExists('mbp15_battery_repair_program');
    }
}
