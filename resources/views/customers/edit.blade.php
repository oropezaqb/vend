@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Update Customer Details</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/customers/{{ $customer->id }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="name">Customer Name: </label>
                                <input 
                                    class="form-control @error('name') is-danger @enderror" 
                                    type="text" 
                                    name="name" 
                                    id="name" required
                                    value="{{ $customer->name }}">
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
