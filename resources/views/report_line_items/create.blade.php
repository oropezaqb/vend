@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Add a New Report Line Item)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                            <form method="POST" action="/report_line_items">
                                @csrf
                                <div class="form-group">
                                    <label for="report">Report Name: </label>
                                    <input 
                                        class="form-control @error('report') is-danger @enderror" 
                                        type="text" 
                                        name="report" 
                                        id="report" required
                                        value="{{ old('report') }}">
                                    @error('report')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="section">Section Name: </label>
                                    <input 
                                        class="form-control @error('section') is-danger @enderror" 
                                        type="text" 
                                        name="section" 
                                        id="section"
                                        value="{{ old('section') }}">
                                    @error('section')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="line_item">Line Item: </label>
                                    <input 
                                        class="form-control @error('line_item') is-danger @enderror" 
                                        type="text" 
                                        name="line_item" 
                                        id="line_item" required
                                        value="{{ old('line_item') }}">
                                    @error('line_item')
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
