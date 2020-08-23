<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @return void
     */
    public function up()
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('date');
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('document_number');
            $table->unique(['company_id', 'document_type_id', 'document_number'], 'my_unique_ref');
            $table->text('explanation');
            $table->timestamps();
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->foreign('document_type_id')
                ->references('id')
                ->on('documents');
        });
        Schema::create('postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->decimal('debit', 13, 4);
            $table->unsignedBigInteger('subsidiary_ledger_id')->nullable();
            $table->unsignedBigInteger('report_line_item_id')->nullable();
            $table->timestamps();
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
            $table->foreign('subsidiary_ledger_id')
                ->references('id')
                ->on('subsidiary_ledgers');
            $table->foreign('report_line_item_id')
                ->references('id')
                ->on('report_line_items');
        });
        Schema::create('journal_entry_posting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('posting_id');
            $table->unique(['journal_entry_id', 'posting_id']);
            $table->timestamps();
            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->onDelete('cascade');
            $table->foreign('posting_id')
                ->references('id')
                ->on('postings')
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
//        Schema::dropIfExists('journal_entries');
    }
}
