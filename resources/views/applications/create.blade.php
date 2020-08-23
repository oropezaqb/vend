@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">File a New Application</div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/applications">
                            @csrf
                            @if (!empty($message))
                                <p>{{ $message }}</p>
                            @endif
                            <div class="form-group">
                                <label for="code">Company Code: </label>
                                <input 
                                    class="form-control @error('name') is-danger @enderror" 
                                    type="text" 
                                    name="code" 
                                    id="code" required
                                    value="{{ old('code') }}">
                                @error('code')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </form>
                        <br>
                        <p>Kindly ask for the code from the company's system admin.</p>
                        <p>Don't have a code? Add a new company instead <a href="/companies/create">here</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
