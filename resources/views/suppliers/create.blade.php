@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Add a New Supplier</div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/suppliers">
                            @csrf
                            <div class="form-group">
                                <label for="name">Supplier Name: </label>
                                <input 
                                    class="form-control @error('name') is-danger @enderror" 
                                    type="text" 
                                    name="name" 
                                    id="name" required
                                    value="{{ old('name') }}">
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </form>
                        <br>
                        <p>Or add several suppliers using a csv file <a href="{{ url('/suppliers/import') }}">here</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
