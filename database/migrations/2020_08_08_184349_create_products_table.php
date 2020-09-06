<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->boolean('track_quantity');
            $table->unsignedBigInteger('receivable_account_id');
            $table->unsignedBigInteger('inventory_account_id')->nullable();
            $table->unsignedBigInteger('income_account_id');
            $table->unsignedBigInteger('expense_account_id')->nullable();
            $table->unique(['name', 'company_id']);
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('receivable_account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('inventory_account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('income_account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('expense_account_id')
                ->references('id')
                ->on('accounts');
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
//
    }
}
