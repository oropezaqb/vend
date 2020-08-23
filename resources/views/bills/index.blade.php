@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Bills)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/bills">
                            @csrf
                            <div class="form-group">
                                <label for="supplier_name">Supplier Name: </label>
                                <input 
                                    class="form-control @error('supplier_name') is-danger @enderror" 
                                    type="text" 
                                    name="supplier_name" 
                                    id="supplier_name" required
                                    value="{{ old('supplier_name') }}">
                                @error('supplier_name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to record a new bill? Click <a href="{{ url('/bills/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($bills as $bill)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $bill->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/bills/{{ $bill->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/bills/{{ $bill->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $bill->bill_date }}, {{ $bill->supplier->name }}, Bill no. {{ $bill->bill_number }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No bills recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
