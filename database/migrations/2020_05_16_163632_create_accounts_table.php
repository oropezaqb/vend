<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @return void
     */
    public function up()
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->unique(['company_id', 'name']);
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('number')->unique();
            $table->string('title')->unique;
            $table->enum('type',
            [
                '110 - Cash and Cash Equivalents',
                '120 - Non-Cash Current Asset',
                '150 - Non-Current Asset',
                '210 - Current Liabilities',
                '250 - Non-Current Liabilities',
                '310 - Capital',
                '320 - Share Premium',
                '330 - Retained Earnings',
                '340 - Other Comprehensive Income',
                '350 - Drawing',
                '390 - Income Summary',
                '410 - Revenue',
                '420 - Other Income',
                '510 - Cost of Goods Sold',
                '520 - Operating Expense',
                '590 - Income Tax Expense'
            ]);
            $table->unsignedBigInteger('line_item_id');
            $table->boolean('subsidiary_ledger');
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('line_item_id')
                ->references('id')
                ->on('line_items');
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
//        Schema::dropIfExists('accounts');
    }
}
