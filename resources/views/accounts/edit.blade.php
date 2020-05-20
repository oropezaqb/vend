@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Edit Account Title Details</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/accounts/{{ $account->id }}">
                            @csrf
                            @method('PUT')
                                <div class="form-group">
                                    <label for="number">Account Number: </label>
                                    <input 
                                        class="form-control @error('number') is-danger @enderror" 
                                        type="text" 
                                        name="number" 
                                        id="number" required
                                        value="{{ $account->number }}">
                                    @error('number')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="title">Account Title: </label>
                                    <input 
                                        class="form-control @error('title') is-danger @enderror" 
                                        type="text" 
                                        name="title" 
                                        id="title" required
                                        value="{{ $account->title }}">
                                    @error('title')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="type">Account Type: </label>
                                    {!! 
                                        Form::select('type', array(
                                            '110 - Cash and Cash Equivalents' => '110 - Cash and Cash Equivalents',
                                            '120 - Non-Cash Current Asset' => '120 - Non-Cash Current Asset',
                                            '150 - Non-Current Asset' => '150 - Non-Current Asset',
                                            '210 - Current Liabilities' => '210 - Current Liabilities',
                                            '250 - Non-Current Liabilities' => '250 - Non-Current Liabilities',
                                            '310 - Capital' => '310 - Capital',
                                            '320 - Share Premium' => '320 - Share Premium',
                                            '330 - Retained Earnings' => '330 - Retained Earnings',
                                            '340 - Other Comprehensive Income'=> '340 - Other Comprehensive Income',
                                            '350 - Drawing' => '350 - Drawing',
                                            '390 - Income Summary' => '390 - Income Summary',
                                            '410 - Revenue' => '410 - Revenue',
                                            '420 - Other Income' => '420 - Other Income',
                                            '510 - Cost of Goods Sold' => '510 - Cost of Goods Sold',
                                            '520 - Operating Expense' => '520 - Operating Expense',
                                            '590 - Income Tax Expense' => '590 - Income Tax Expense',
                                            '600 - Other Accounts' => '600 - Other Accounts'
                                        ), array('class' => 'form-control', 'value' => $account->type)) 
                                    !!}
                                    @error('type')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                {!! Form::checkbox('subsidiary_ledger', true, $account->subsidiary_ledger) !!}
                                {!! Form::label('subsidiary_ledger', 'Subsidiary Ledger') !!}
                                <br>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
