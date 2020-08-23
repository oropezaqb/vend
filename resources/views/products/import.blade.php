@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Add Products</div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/products/upload" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="products">Select a CSV file to upload</label>
                                <br>
                                {!! Form::file('products') !!}
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Import</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
