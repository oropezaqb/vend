@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Add Suppliers</div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/suppliers/upload" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="suppliers">Select a CSV file to upload</label>
                                <br>
                                {!! Form::file('suppliers') !!}
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
