<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) { //Creating table
            $table->id();
            $table->string('isbn', 15)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('published_date')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('editorial_id');
        });

        Schema::table('books', function (Blueprint $table) { //Updating table
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('editorial_id')->references('id')->on('editorials');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}