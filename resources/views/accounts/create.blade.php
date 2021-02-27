@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Add a New Account Title)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <form method="POST" action="/accounts">
                                @csrf
                                <div class="form-group">
                                    <label for="number">Account Number: </label>
                                    <input 
                                        class="form-control @error('number') is-danger @enderror" 
                                        type="text" 
                                        name="number" 
                                        id="number" required
                                        value="{{ old('number') }}">
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
                                        value="{{ old('title') }}">
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
                                        ), array('class' => 'form-control')) 
                                    !!}
                                    @error('type')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="line_item_id">Line&nbsp;Item:&nbsp;</label>&nbsp;
                                    <input list="line_item_ids" id="line_item_id0" onchange="setValue(this)" data-id="" class="custom-select @error('line_item_id') is-danger @enderror" required value="{!! old('line_item_name') !!}">
                                    <datalist id="line_item_ids">
                                        @foreach ($lineItems as $lineItem)
                                            <option data-value="{{ $lineItem->id }}">{{ $lineItem->name }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="line_item_id" id="line_item_id0-hidden" value="{!! old('line_item_id') !!}">
                                    <input type="hidden" name="line_item_name" id="name-line_item_id0-hidden" value="{!! old('line_item_name') !!}">
                                </div>
                                {!! Form::checkbox('subsidiary_ledger', true) !!}
                                {!! Form::label('subsidiary_ledger', 'Subsidiary Ledger') !!}
                                <br>
                                <button class="btn btn-primary" type="submit">Add</button>
                            </form>
                            <script>
                                function setValue (id) 
                                {
                                    var input = id,
                                        list = input.getAttribute('list'),
                                        options = document.querySelectorAll('#' + list + ' option'),
                                        hiddenInput = document.getElementById(input.getAttribute('id') + '-hidden'),
                                        hiddenInputName = document.getElementById('name-' + input.getAttribute('id') + '-hidden'),
                                        label = input.value;
                                    hiddenInputName.value = label;
                                    hiddenInput.value = label;
                                    for(var i = 0; i < options.length; i++) {
                                        var option = options[i];
                                        if(option.innerText === label) {
                                            hiddenInput.value = option.getAttribute('data-value');
                                            break;
                                        }
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
