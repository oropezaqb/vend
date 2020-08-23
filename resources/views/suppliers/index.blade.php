@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Suppliers</div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to add a new supplier? Click <a href="{{ url('/suppliers/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($suppliers as $supplier)
                            <div id="content">
                                <div id="name">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $supplier->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/suppliers/{{ $supplier->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/suppliers/{{ $supplier->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $supplier->name }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No suppliers recorded yet.</p>
                        @endforelse
                        @if (!empty($suppliers))
                            {{ $suppliers->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
