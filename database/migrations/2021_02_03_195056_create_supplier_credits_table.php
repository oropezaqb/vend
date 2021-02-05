<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->morphs('purchasable');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('supplier_credit_clines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_credit_id');
            $table->morphs('purchasable');
            $table->unsignedBigInteger('account_id');
            $table->text('description')->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('supplier_credit_id')
                ->references('id')
                ->on('supplier_credits')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('supplier_credit_ilines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_credit_id');
            $table->morphs('purchasable');
            $table->unsignedBigInteger('product_id');
            $table->text('description')->nullable();
            $table->float('quantity', 8, 2)->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('input_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('supplier_credit_id')
                ->references('id')
                ->on('supplier_credits')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('products');
        });
        Schema::create('purc_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
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
            $table->morphs('returnablepurc');
            $table->morphs('purchasable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_credit_clines');
        Schema::dropIfExists('supplier_credit_ilines');
        Schema::dropIfExists('purc_returns');
        Schema::dropIfExists('supplier_credits');
    }
}
