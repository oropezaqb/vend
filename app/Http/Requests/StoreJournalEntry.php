<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Account;

class StoreJournalEntry extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'document_type_id.required' => 'The document type field is required.',
            'document_type_id.exists' =>
                'The selected document type is invalid. Please choose among the recommended items.',
            'document_number.min' => 'The document number must be a positive number.',
            "postings.'account_id'.min" => 'There must be at least two account titles.',
            "postings.'account_id'.*.exists" =>
                'Some account titles are invalid. Please choose among the recommended items.',
            "postings.'debit'.*.min" => 'Debit amounts must be positive',
            "postings.'credit'.*.min" => 'Credit amounts must be positive',
            "postings.'subsidiary_ledger_id'.*.exists" =>
                'Some subsidiary ledgers are invalid. Please choose among the recommended items.',
            "postings.'report_line_item_id'.*.exists" =>
                'Some report line items are invalid. Please choose among the recommended items.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => ['required', 'date'],
            'document_type_id' => ['required', 'exists:App\Document,id'],
            'document_number' => ['required', 'numeric', 'min:1'],
            'explanation' => ['required'],
            "postings.'account_id'" => ['sometimes', 'min:2'],
            "postings.'account_id'.*" => [
                'sometimes',
                'required',
                'exists:App\Account,id'
            ],
            "postings.'debit'.*" => ['sometimes', 'numeric', 'min:0.01', 'nullable'],
            "postings.'credit'.*" => ['sometimes', 'numeric', 'min:0.01', 'nullable'],
            "postings.'subsidiary_ledger_id'.*" => ['sometimes', 'exists:App\SubsidiaryLedger,id', 'nullable'],
            "postings.'report_line_item_id'.*" => ['sometimes', 'exists:App\ReportLineItem,id', 'nullable'],
        ];
    }
    public function withValidator($validator)
    {
        if (is_null(request("postings.'account_id'"))) {
            return;
        }
        $validator->after(function ($validator) {
            $count = count(request("postings.'account_id'"));
            for ($row = 0; $row < $count; $row++) {
                if ((is_null(request("postings.'debit'.".$row)) && is_null(request("postings.'credit'.".$row)))
                    || (!is_null(request("postings.'debit'.".$row))
                    && !is_null(request("postings.'credit'.".$row)))) {
                    $validator->errors()->add('postings', 'Line ' . ($row + 1) .
                        ' must have either a debit or a credit (not both).');
                }
                if (!Account::all()->contains(request("postings.'account_id'.".$row))) {
                    continue;
                }
                StoreJournalEntry::checkSubsidiaryLedger($validator, $row);
                StoreJournalEntry::checkReportLineItem($validator, $row);
            }
            StoreJournalEntry::checkTotals($validator);
        });
    }
    public function checkSubsidiaryLedger($validator, $row)
    {
        if ((Account::find(request("postings.'account_id'.".$row))->subsidiary_ledger == true)
            && (is_null(request("postings.'subsidiary_ledger_id'.".$row)))) {
            $validator->errors()->add('postings', 'Line ' . ($row + 1) . ' requires a subsidiary ledger.');
        }
    }
    public function checkReportLineItem($validator, $row)
    {
        $account = Account::find(request("postings.'account_id'.".$row));
        if (($account->type == '110 - Cash and Cash Equivalents' ||
             $account->type == '310 - Capital' ||
             $account->type == '320 - Share Premium' ||
             $account->type == '330 - Retained Earnings' ||
             $account->type == '340 - Other Comprehensive Income')
             && (is_null(request("postings.'report_line_item_id'.".$row)))) {
            $validator->errors()->add('postings', 'Line ' . ($row + 1) . ' requires a report line item.');
        }
    }
    public function checkTotals($validator)
    {
        if (array_sum(request("postings.'debit'")) != array_sum(request("postings.'credit'"))) {
            $validator->errors()->add('postings', 'Total debits must equal total credits');
        }
    }
}
