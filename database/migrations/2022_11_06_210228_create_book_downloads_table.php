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
        Schema::create('book_downloads', function (Blueprint $table) { //Creating table
            $table->id();
            $table->integer('total_downloads')->default(0);
            $table->unsignedBigInteger('book_id');
        });

        Schema::table('book_downloads', function (Blueprint $table) { //Updating table
            $table->foreign('book_id')->references('id')->on('books');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_downloads');
    }
};
