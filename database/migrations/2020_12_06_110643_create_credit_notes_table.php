<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->timestamps();
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');
        });
        Schema::create('credit_note_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_note_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('output_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//
    }
}
