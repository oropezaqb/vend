<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryQtyAdjsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_qty_adjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->unsignedBigInteger('account_id');
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('inventory_qty_adj_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_qty_adj_id');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('change_in_qty', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('inventory_qty_adj_id')
                ->references('id')
                ->on('inventory_qty_adjs')
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
        Schema::dropIfExists('inventory_qty_adjs');
    }
}
