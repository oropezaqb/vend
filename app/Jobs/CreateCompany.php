<?php

namespace App\Jobs;

use App\Company;
use Illuminate\Http\Request;
use App\Ability;
use App\Role;
use App\CurrentCompany;
use App\Document;
use App\LineItem;
use App\Account;
use App\Notifications\CompanyCreated;
use App\Jobs\CreateCompany;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */

class CreateCompany
{
    public function run($company)
    {
        $user = \Auth::user();
        $company->employ($user);
        $approveApplication = Ability::firstOrCreate(['name'
            => 'approve_job_application', 'company_id' => $company->id]);
        $admin = Role::firstOrCreate(['name' => 'admin', 'company_id' => $company->id]);
        $admin->allowTo($approveApplication);
        $user->assignRole($admin);
        $recordJournalEntries = Ability::firstOrCreate(['name'
            => 'record_journal_entries', 'company_id' => $company->id]);
        $staff = Role::firstOrCreate(['name' => 'staff', 'company_id' => $company->id]);
        $staff->allowTo($recordJournalEntries);
        $approveApplication->save();
        $admin->save();
        $currentCompany = new CurrentCompany(['user_id' => $user->id, 'company_id' => $company->id]);
        $currentCompany->save();
        Document::firstOrCreate(['name' => 'Journal Entry', 'company_id' => $company->id]);
        Document::firstOrCreate(['name' => 'Bill', 'company_id' => $company->id]);
        \Notification::send($user, new CompanyCreated($company));
        if (empty($user->currentCompany)) {
            $currentCompany = new CurrentCompany(['user_id' => $user->id, 'company_id' => $company->id]);
            $currentCompany->save();
        } else {
            $currentCompany = CurrentCompany::where('user_id', $user->id)->first();
            $currentCompany->update(['user_id' => $user->id, 'company_id' => $company->id]);
        }
        $lineItems = array(
            'Cash and cash equivalents',
            'Trade and other receivables',
            'Inventories',
            'Financial assets',
            'Noncurrent assets held for sale',
            'Other current assets',
            'Property, plant and equipment',
            'Financial assets - net of current portion',
            'Investments in associate companies and joint ventures',
            'Investment property',
            'Right-of-use assets',
            'Deferred tax assets',
            'Intangible assets',
            'Other noncurrent assets',
            'Trade and other payables',
            'Loan payables',
            'Income tax payable',
            'Current portion of long-term debt',
            'Dividends payable',
            'Other current liabilities',
            'Long-term debt - net of current portion',
            'Lease liabilities - net of current portion',
            'Deferred tax liabilities',
            'Other noncurrent liabilities',
            'Capital',
            'Capital stock',
            'Additional paid-in capital',
            'Unappropriated retained earnings',
            'Appropriated retained earnings',
            'Non-controlling interests',
            'Sales',
            'Sales - Merchandise',
            'Sales - Real estate',
            'Service revenue',
            'Rent revenue',
            'Investment income',
            'Dividend income',
            'Gain on sale of financial assets - net',
            'Other revenues',
            'Cost of sales',
            'Cost of sales - Merchandise',
            'Cost of sales - Real estate',
            'Selling, general and administrative expenses',
            'Interest expense',
            'Interest income',
            'Gain on disposal of investments and properties - net',
            'Gain on fair value changes on derivatives - net',
            'Impairment loss on investment',
            'Foreign exchange gain (loss) - net',
            'Current income tax expense',
            'Deferred income tax expense',
            'Net unrealized gain on financial assets'
        );
        foreach($lineItems as $lineItem)
        {
            $lineItemForSaving = new LineItem([
                'company_id' => $company->id,
                'name' => $lineItem
            ]);
            $lineItemForSaving->save();
        }
        $accounts = array(
            array(
                'number' => 110001,
                'title' => 'Cash in bank',
                'type' => '110 - Cash and Cash Equivalents',
                'line_item' => 'Cash and cash equivalents',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 110002,
                'title' => 'Cash on hand',
                'type' => '110 - Cash and Cash Equivalents',
                'line_item' => 'Cash and cash equivalents',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 110003,
                'title' => 'Petty cash fund',
                'type' => '110 - Cash and Cash Equivalents',
                'line_item' => 'Cash and cash equivalents',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 120001,
                'title' => 'Accounts receivable',
                'type' => '120 - Non-Cash Current Asset',
                'line_item' => 'Trade and other receivables',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 120002,
                'title' => 'Allowance for bad debt',
                'type' => '120 - Non-Cash Current Asset',
                'line_item' => 'Trade and other receivables',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 120003,
                'title' => 'Notes receivable',
                'type' => '120 - Non-Cash Current Asset',
                'line_item' => 'Trade and other receivables',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 130001,
                'title' => 'Merchandise inventory',
                'type' => '120 - Non-Cash Current Asset',
                'line_item' => 'Inventories',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 140100,
                'title' => 'Financial assets at fair value',
                'type' => '120 - Non-Cash Current Asset',
                'line_item' => 'Financial assets',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 140200,
                'title' => 'Available for sale assets',
                'type' => '120 - Non-Cash Current Asset',
                'line_item' => 'Noncurrent assets held for sale',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 140300,
                'title' => 'Prepaid rent',
                'type' => '120 - Non-Cash Current Asset',
                'line_item' => 'Other current assets',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 1500001,
                'title' => 'Building',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Property, plant and equipment',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 150002,
                'title' => 'Land',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Property, plant and equipment',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 150003,
                'title' => 'Office equipment',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Property, plant and equipment',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 154001,
                'title' => 'Right-of-use assets',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Right-of-use assets',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 155001,
                'title' => 'Financial assets at fair value through OCI',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Financial assets - net of current portion',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 160001,
                'title' => 'Investments in associates',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Investments in associate companies and joint ventures',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 165001,
                'title' => 'Investment property',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Investment property',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 170001,
                'title' => 'Deferred tax assets',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Deferred tax assets',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 175001,
                'title' => 'Patent',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Intangible assets',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 190001,
                'title' => 'Other noncurrent assets',
                'type' => '150 - Non-Current Asset',
                'line_item' => 'Other noncurrent assets',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 210001,
                'title' => 'Accounts payable',
                'type' => '210 - Current Liabilities',
                'line_item' => 'Trade and other payables',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 210002,
                'title' => 'Notes payable',
                'type' => '210 - Current Liabilities',
                'line_item' => 'Trade and other payables',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 220001,
                'title' => 'Loans payable',
                'type' => '210 - Current Liabilities',
                'line_item' => 'Loan payables',
                'subsidiary_ledger' => true
            ),
            array(
                'number' => 221001,
                'title' => 'Current portion of long-term debt',
                'type' => '210 - Current Liabilities',
                'line_item' => 'Current portion of long-term debt',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 230001,
                'title' => 'Income tax payable',
                'type' => '210 - Current Liabilities',
                'line_item' => 'Income tax payable',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 240001,
                'title' => 'Cash dividends payable',
                'type' => '210 - Current Liabilities',
                'line_item' => 'Dividends payable',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 245001,
                'title' => 'Other current liabilities',
                'type' => '210 - Current Liabilities',
                'line_item' => 'Other current liabilities',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 250001,
                'title' => 'Long-term debt',
                'type' => '250 - Non-Current Liabilities',
                'line_item' => 'Long-term debt - net of current portion',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 260001,
                'title' => 'Lease liabilities',
                'type' => '250 - Non-Current Liabilities',
                'line_item' => 'Lease liabilities - net of current portion',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 270001,
                'title' => 'Deferred tax liabilities',
                'type' => '250 - Non-Current Liabilities',
                'line_item' => 'Deferred tax liabilities',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 290001,
                'title' => 'Other noncurrent liabilities',
                'type' => '250 - Non-Current Liabilities',
                'line_item' => 'Other noncurrent liabilities',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 310001,
                'title' => 'Capital',
                'type' => '310 - Capital',
                'line_item' => 'Capital',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 310002,
                'title' => 'Ordinary share capital',
                'type' => '310 - Capital',
                'line_item' => 'Capital stock',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 310003,
                'title' => 'Preference share capital',
                'type' => '310 - Capital',
                'line_item' => 'Capital stock',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 320001,
                'title' => 'Share premium - ordinary shares',
                'type' => '320 - Share Premium',
                'line_item' => 'Additional paid-in capital',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 320002,
                'title' => 'Share premium - preference shares',
                'type' => '320 - Share Premium',
                'line_item' => 'Additional paid-in capital',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 330001,
                'title' => 'Retained earnings',
                'type' => '330 - Retained Earnings',
                'line_item' => 'Unappropriated retained earnings',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 330002,
                'title' => 'Retained earnings appropriated for plant expansion',
                'type' => '330 - Retained Earnings',
                'line_item' => 'Appropriated retained earnings',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410001,
                'title' => 'Sales',
                'type' => '410 - Revenue',
                'line_item' => 'Sales',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410002,
                'title' => 'Sales - Merchandise',
                'type' => '410 - Revenue',
                'line_item' => 'Sales - Merchandise',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410003,
                'title' => 'Sales - Real estate',
                'type' => '410 - Revenue',
                'line_item' => 'Sales - Real estate',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410004,
                'title' => 'Service revenue',
                'type' => '410 - Revenue',
                'line_item' => 'Service revenue',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410005,
                'title' => 'Rent revenue',
                'type' => '410 - Revenue',
                'line_item' => 'Rent revenue',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410006,
                'title' => 'Investment income',
                'type' => '410 - Revenue',
                'line_item' => 'Investment income',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410007,
                'title' => 'Dividend income',
                'type' => '410 - Revenue',
                'line_item' => 'Dividend income',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410008,
                'title' => 'Gain on sale of financial assets - net',
                'type' => '410 - Revenue',
                'line_item' => 'Gain on sale of financial assets - net',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 410009,
                'title' => 'Other revenues',
                'type' => '410 - Revenue',
                'line_item' => 'Other revenues',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 510001,
                'title' => 'Cost of sales',
                'type' => '510 - Cost of Goods Sold',
                'line_item' => 'Cost of sales',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 510002,
                'title' => 'Cost of sales - Merchandise',
                'type' => '510 - Cost of Goods Sold',
                'line_item' => 'Cost of sales - Merchandise',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 510003,
                'title' => 'Cost of sales - Real estate',
                'type' => '510 - Cost of Goods Sold',
                'line_item' => 'Cost of sales - Real estate',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 520001,
                'title' => 'Salary expense',
                'type' => '520 - Operating Expense',
                'line_item' => 'Selling, general and administrative expenses',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 560001,
                'title' => 'Interest expense',
                'type' => '520 - Operating Expense',
                'line_item' => 'Selling, general and administrative expenses',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 420001,
                'title' => 'Interest income',
                'type' => '420 - Other Income',
                'line_item' => 'Interest income',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 420002,
                'title' => 'Gain on disposal of investments and properties',
                'type' => '420 - Other Income',
                'line_item' => 'Gain on disposal of investments and properties - net',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 420003,
                'title' => 'Gain on fair value changes on derivatives',
                'type' => '420 - Other Income',
                'line_item' => 'Gain on fair value changes on derivatives - net',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 570001,
                'title' => 'Impairment loss on investment',
                'type' => '520 - Operating Expense',
                'line_item' => 'Selling, general and administrative expenses',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 570002,
                'title' => 'Foreign exchange gain (loss)',
                'type' => '520 - Operating Expense',
                'line_item' => 'Selling, general and administrative expenses',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 590001,
                'title' => 'Income tax expense',
                'type' => '590 - Income Tax Expense',
                'line_item' => 'Current income tax expense',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 590002,
                'title' => 'Deferred income tax expense',
                'type' => '590 - Income Tax Expense',
                'line_item' => 'Deferred income tax expense',
                'subsidiary_ledger' => false
            ),
            array(
                'number' => 340001,
                'title' => 'Unrealized gain (loss) on financial assets',
                'type' => '340 - Other Comprehensive Income',
                'line_item' => 'Net unrealized gain on financial assets',
                'subsidiary_ledger' => false
            )
        );
        foreach($accounts as $account)
        {
            $lineItem = LineItem::where('name', $account['line_item'])->first();
            $accountForSaving = new Account([
                'company_id' => $company->id,
                'number' => $account['number'],
                'title' => $account['title'],
                'type' => $account['type'],
                'line_item_id' => $lineItem->id,
                'subsidiary_ledger' => $account['subsidiary_ledger']
            ]);
            $accountForSaving->save();
        }
    }
}
