@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Product Details</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <div id="content">
                            <div id="name">
                                <p>Product Name: {{ $product->name }}</p>
                                <p>Track Quantity: {{ $product->track_quantity ? 'Yes' : 'No' }}</p>
                            </div>
                            <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/products/{{ $product->id }}/edit';">Edit</button></div>
                            <div style="display:inline-block;"><form method="POST" action="/products/{{ $product->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Delete</button>
                            </form></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
