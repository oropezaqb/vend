@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Sales Receipts)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/sales_receipts">
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
                        <p>Want to record a new sales receipt? Click <a href="{{ url('/sales_receipts/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($salesReceipts as $salesReceipt)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $salesReceipt->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/sales_receipts/{{ $salesReceipt->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/sales_receipts/{{ $salesReceipt->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $salesReceipt->date }}, {{ $salesReceipt->customer->name }}, Sales Receipt no. {{ $salesReceipt->number }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No sales receipts recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
