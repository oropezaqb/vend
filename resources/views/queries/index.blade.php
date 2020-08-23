@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Queries)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/queries">
                            @csrf
                            <div class="form-group">
                                <label for="title">Title: </label>
                                <input 
                                    class="form-control" 
                                    type="text" 
                                    name="title" 
                                    id="title" required
                                    value="{{ old('title') }}">
                                @error('title')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to add a new query? Click <a href="{{ url('/queries/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($queries as $query)
                            <div id="query">
                                <div style="display:inline-block;"><form method="POST" action="/queries/{{ $query->id }}/run">
                                    @csrf
                                    <button class="btn btn-link" type="submit">Run</button>
                                </form></div>
                                <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $query->path() }}';">View</button></div>
                                <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/queries/{{ $query->id }}/edit';">Edit</button></div>
                                <div style="display:inline-block;"><form method="POST" action="/queries/{{ $query->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-link" type="submit">Delete</button>
                                </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $query->title }}: {{ $query->query }}</div>
                            </div>
                        @empty
                            <p>No queries registered yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
