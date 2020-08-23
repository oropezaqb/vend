@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Add a Current Company</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        <form method="POST" action="/current_company">
                            @csrf
                            @if (!empty($message))
                                <p>{{ $message }}</p>
                            @endif
                            <div class="form-group">
                                <label for="company_id">Company Name:</label>
                                <select id="company_id" class="form-control @error('company_id') is-danger @enderror" name="company_id" value="{{ old('company_id') }}">
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </form>
                        <br>
                        <p>Nothing to add? <a href="/companies/create">Create a company</a> or <a href="/applications/create">send an application</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
