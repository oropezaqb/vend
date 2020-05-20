@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Edit Subsidiary Ledger Details</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/subsidiary_ledgers/{{ $subsidiaryLedger->id }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="number">Account Number: </label>
                                <input 
                                    class="form-control @error('number') is-danger @enderror" 
                                    type="text" 
                                    name="number" 
                                    id="number" required
                                    value="{{ $subsidiaryLedger->number }}">
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
                                    value="{{ $subsidiaryLedger->name }}">
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
