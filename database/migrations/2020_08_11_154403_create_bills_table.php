<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('journal_entry_id');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entries');
        });
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('supplier_id');
            $table->date('bill_date');
            $table->date('due_date');
            $table->unsignedBigInteger('bill_number');
            $table->unsignedBigInteger('transaction_id');
            $table->unique(['company_id', 'supplier_id', 'bill_number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions');
        });
        Schema::create('bill_category_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('account_id');
            $table->text('description')->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('bill_id')
                ->references('id')
                ->on('bills')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('bill_item_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('bill_id')
                ->references('id')
                ->on('bills')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('transaction_id');
            $table->date('date');
            $table->unsignedBigInteger('product_id');
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
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
