@extends('layouts.bowner')

@section('content')

    <h1>Purchase Material</h1>

    {!! Form::open(['method'=>'POST', 'action'=>'MaterialPurchaseController@store', 'class'=>'form-group']) !!}
    <div class="row">
        <div class="form-group col-sm-6">
            {!! Form::label('material_id', 'Material (*):') !!}
            <select name="material_id" id="material_id" class="form-control" required>
                <option value="" selected>Choose Material</option>
                @foreach($materials as $material)
                    <option value="{{ $material->id }}">{{ $material->name .' ('.$material->unit->name .')'}}</option>
                @endforeach
            </select>
        </div>  

        <div class="form-group col-sm-6">
            {!! Form::label('supplier_id', 'Supplier (*):') !!}
            {!! Form::select('supplier_id', [''=>'Choose Supplier'] + $suppliers, null, ['class'=>'form-control', 'required']) !!}
        </div>
    </div>  
    
    <div class="row">
        <div class="form-group col-sm-6">
            {!! Form::label('quantity', 'Purchase Quantity (*):') !!}
            {!! Form::number('quantity', null, ['class'=>'form-control', 'min'=>1, 'required']) !!}
        </div>
    </div>

    <div class="row">
        <div class="form-group col-sm-6">
            {!! Form::submit('Purchase Material', ['class'=>'btn btn-primary']) !!}
            <a class="btn btn-warning" href="{{URL('/bowner/inventories/material/purchase')}}">Cancel</a>
        </div>
    </div>

    {!! Form::close() !!}

@stop
