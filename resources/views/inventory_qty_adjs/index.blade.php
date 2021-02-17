@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Inventory Qty Adjustments)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/inventory_qty_adjs">
                            @csrf
                            <div class="form-group">
                                <label for="product_name">Product Name: </label>
                                <input
                                    class="form-control @error('product_name') is-danger @enderror" 
                                    type="text"
                                    name="product_name" 
                                    id="product_name" required
                                    value="{{ old('product_name') }}">
                                @error('product_name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to record a new inventory quantity adjustment? Click <a href="{{ url('/inventory_qty_adjs/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($inventoryQtyAdjs as $inventoryQtyAdj)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $inventoryQtyAdj->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/inventory_qty_adjs/{{ $inventoryQtyAdj->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/inventory_qty_adjs/{{ $inventoryQtyAdj->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $inventoryQtyAdj->date }}, Inventory Quantity Adjustment no. {{ $inventoryQtyAdj->number }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No inventory quantity adjustments recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection