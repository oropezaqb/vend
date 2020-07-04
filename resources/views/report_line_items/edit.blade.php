@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Edit Report Line Item Details</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <form method="POST" action="/report_line_items/{{ $reportLineItem->id }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="report">Report Name: </label>
                                <input 
                                    class="form-control @error('report') is-danger @enderror" 
                                    type="text" 
                                    name="report" 
                                    id="report" required
                                    value="{{ $reportLineItem->report }}">
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
                                    value="{{ $reportLineItem->section }}">
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
                                    value="{{ $reportLineItem->line_item }}">
                                @error('line_item')
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
