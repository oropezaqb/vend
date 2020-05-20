@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Add a New Document Type)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <form method="POST" action="/documents">
                                @csrf
                                <div class="form-group">
                                    <label for="number">Document Name: </label>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
