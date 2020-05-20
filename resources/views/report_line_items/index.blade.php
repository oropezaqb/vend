@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Report Line Items)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/report_line_items">
                            @csrf
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
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to add a new report line item? Click <a href="{{ url('/report_line_items/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($reportLineItems as $reportLineItem)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $reportLineItem->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/report_line_items/{{ $reportLineItem->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/report_line_items/{{ $reportLineItem->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $reportLineItem->report }}: {{ $reportLineItem->section }} ({{ $reportLineItem->line_item }})</div>
                                </div>
                            </div>
                        @empty
                            <p>No report line items registered yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
