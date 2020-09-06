@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Product Details</div>
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
                            <div class="form-group">
                                <label for="name">Product Name: </label>
                                <input 
                                    class="form-control @error('name') is-danger @enderror" 
                                    type="text" 
                                    name="name" 
                                    id="name" required readonly
                                    value="{!! $product->name !!}">
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            {!! Form::checkbox('track_quantity', true, $product->track_quantity, array('disabled')) !!}
                            {!! Form::label('track_quantity', 'Track Quantity') !!}
                            <br><br>
                            <div class="form-group custom-control-inline">
                                <label for="receivable_account_id">Receivable&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="receivable_account_ids" id="receivable_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('receivable_account_id') is-danger @enderror" required readonly value="{!! $product->receivableAccount->title !!}">
                            </div>
                            <div class="form-group custom-control-inline">
                                <label for="inventory_account_id">Inventory&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="inventory_account_ids" id="inventory_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('inventory_account_id') is-danger @enderror" readonly value="{!! $product->inventoryAccount->title !!}">
                            </div>
                            <br>
                            <div class="form-group custom-control-inline">
                                <label for="income_account_id">Income&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="income_account_ids" id="income_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('income_account_id') is-danger @enderror" required readonly value="{!! $product->incomeAccount->title !!}">
                            </div>
                            <div class="form-group custom-control-inline">
                                <label for="expense_account_id">Expense&nbsp;Account:&nbsp;</label>&nbsp;
                                <input list="expense_account_ids" id="expense_account_id0" onchange="setValue(this)" data-id="" class="custom-select @error('expense_account_id') is-danger @enderror" readonly value="{!! $product->expenseAccount->title !!}">
                            </div>
                            <br><br>
                        </form>
                        <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/products/{{ $product->id }}/edit';">Edit</button></div>
                        <div style="display:inline-block;"><form method="POST" action="/products/{{ $product->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Delete</button>
                        </form></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
