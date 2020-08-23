@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Add a New Subsidiary Ledger)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <form method="POST" action="/subsidiary_ledgers">
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
                                    <label for="name">Account Name: </label>
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
