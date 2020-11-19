<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivedPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('received_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('date');
            $table->unsignedBigInteger('number');
            $table->unique(['company_id', 'number'], 'my_unique_ref');
            $table->unsignedBigInteger('account_id');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
        Schema::create('received_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('received_payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 13, 2);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices');
            $table->foreign('received_payment_id')
                ->references('id')
                ->on('received_payments')
                ->onDelete('cascade');
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
