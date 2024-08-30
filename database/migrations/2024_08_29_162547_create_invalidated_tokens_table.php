<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvalidatedTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invalidated_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('jti')->unique(); // Unique identifier for the token
            $table->timestamp('expired_time'); // Expiry time of the token
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
        Schema::dropIfExists('invalidated_tokens');
    }
}
