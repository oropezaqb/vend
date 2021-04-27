<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('account_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->text('memo')->nullable();
            $table->unsignedBigInteger('cashback_account_id');
            $table->text('cashback_memo')->nullable();
            $table->decimal('cashback_amount', 13, 2);
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('cashback_account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('cash_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_receipt_id');
            $table->unsignedBigInteger('subsidiary_ledger_id');
            $table->unsignedBigInteger('account_id');
            $table->text('description')->nullable();
            $table->decimal('amount', 13, 2);
            $table->decimal('output_tax', 13, 2)->default(0);
            $table->timestamps();
            $table->foreign('cash_receipt_id')
                ->references('id')
                ->on('cash_receipts')
                ->onDelete('cascade');
            $table->foreign('subsidiary_ledger_id')
                ->references('id')
                ->on('subsidiary_ledgers');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
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
