<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->unique(['name', 'company_id']);
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->unsignedBigInteger('invoice_number');
            $table->timestamps();
            $table->unique(['company_id', 'invoice_number'], 'my_unique_ref');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');
        });
        Schema::create('invoice_item_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('purchase_id');
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
            $table->foreign('purchase_id')
                ->references('id')
                ->on('purchases')
                ->onDelete('cascade');
            $table->morphs('salable');
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
