@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Invoices)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/invoices">
                            @csrf
                            <div class="form-group">
                                <label for="customer_name">Customer Name: </label>
                                <input 
                                    class="form-control @error('customer_name') is-danger @enderror" 
                                    type="text" 
                                    name="customer_name" 
                                    id="customer_name" required
                                    value="{{ old('customer_name') }}">
                                @error('customer_name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to record a new invoice? Click <a href="{{ url('/invoices/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($invoices as $invoice)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $invoice->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/invoices/{{ $invoice->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/invoices/{{ $invoice->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $invoice->invoice_date }}, {{ $invoice->customer->name }}, Invoice no. {{ $invoice->invoice_number }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No invoices recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
