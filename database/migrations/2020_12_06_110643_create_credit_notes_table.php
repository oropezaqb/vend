<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */

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
            $table->unsignedBigInteger('invoice_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->timestamps();
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
        });
        Schema::create('credit_note_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_note_id');
            $table->unsignedBigInteger('invoice_id');
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
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('sale_id');
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('cascade');
            $table->morphs('returnable_sale');
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
