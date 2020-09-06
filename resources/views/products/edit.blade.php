@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Update Product Details</div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/products/{{ $product->id }}">
                            @csrf
                            @method('PUT')
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <datalist id="account_ids">
                                @foreach ($accounts as $account)
                                    <option data-value={{ $account->id }}>{{ $account->title }} ({{ $account->number }})</option>
                                @endforeach
                            </datalist>
                            <div class="form-group">
                                <label for="name">Product Name: </label>
                                <input 
                                    class="form-control @error('name') is-danger @enderror" 
                                    type="text" 
                                    name="name" 
                                    id="name" required
                                    value="{!! old('name'), $product->name !!}">
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            {!! Form::checkbox('track_quantity', true, $product->track_quantity, array('disabled')) !!}
                            {!! Form::label('track_quantity', 'Track Quantity') !!}
                            <br><br>
                            <div class="form-group custom-control-inline">
                                <label for="receivable_account_id">Receivable&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="receivable_account_ids" id="receivable_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('receivable_account_id') is-danger @enderror" required value="{!! old('receivable_account_title'), $product->receivableAccount->title !!}">
                                <datalist id="receivable_account_ids">
                                    @foreach ($accounts as $account)
                                        <option data-value="{{ $account->id }}">{{ $account->title }}</option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="receivable_account_id" id="receivable_account_id0-hidden" value="{!! old('receivable_account_id'), $product->receivableAccount->id !!}">
                                <input type="hidden" name="receivable_account_title" id="name-receivable_account_id0-hidden" value="{!! old('receivable_account_title'), $product->receivableAccount->title !!}">
                            </div>
                            <div class="form-group custom-control-inline">
                                <label for="inventory_account_id">Inventory&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="inventory_account_ids" id="inventory_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('inventory_account_id') is-danger @enderror" value="{!! old('inventory_account_title'), $product->inventoryAccount->title !!}">
                                <datalist id="inventory_account_ids">
                                    @foreach ($accounts as $account)
                                        <option data-value="{{ $account->id }}">{{ $account->title }}</option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="inventory_account_id" id="inventory_account_id0-hidden" value="{!! old('inventory_account_id'), $product->inventoryAccount->id !!}">
                                <input type="hidden" name="inventory_account_title" id="name-inventory_account_id0-hidden" value="{!! old('inventory_account_title'), $product->inventoryAccount->title !!}">
                            </div>
                            <br>
                            <div class="form-group custom-control-inline">
                                <label for="income_account_id">Income&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="income_account_ids" id="income_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('income_account_id') is-danger @enderror" required value="{!! old('income_account_title'), $product->incomeAccount->title !!}">
                                <datalist id="income_account_ids">
                                    @foreach ($accounts as $account)
                                        <option data-value="{{ $account->id }}">{{ $account->title }}</option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="income_account_id" id="income_account_id0-hidden" value="{!! old('income_account_id'), $product->incomeAccount->id !!}">
                                <input type="hidden" name="income_account_title" id="name-income_account_id0-hidden" value="{!! old('income_account_title'), $product->incomeAccount->title !!}">
                            </div>
                            <div class="form-group custom-control-inline">
                                <label for="expense_account_id">Expense&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="expense_account_ids" id="expense_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('expense_account_id') is-danger @enderror" value="{!! old('expense_account_title'), $product->expenseAccount->title !!}">
                                <datalist id="expense_account_ids">
                                    @foreach ($accounts as $account)
                                        <option data-value="{{ $account->id }}">{{ $account->title }}</option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="expense_account_id" id="expense_account_id0-hidden" value="{!! old('expense_account_id'), $product->expenseAccount->id !!}">
                                <input type="hidden" name="expense_account_title" id="name-expense_account_id0-hidden" value="{!! old('expense_account_title'), $product->expenseAccount->title !!}">
                            </div>
                            <br><br>
                            <button class="btn btn-primary" type="submit">Save</button>
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
@endsection
