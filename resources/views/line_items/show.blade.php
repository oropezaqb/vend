@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Account Line Item Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <div id="content">
                            <div id="name">
                                <p>Account Line Item Name: {{ $lineItem->name }}</p>
                            </div>
                            <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/line_items/{{ $lineItem->id }}/edit';">Edit</button></div>
                            <div style="display:inline-block;"><form method="POST" action="/line_items/{{ $lineItem->id }}">
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
