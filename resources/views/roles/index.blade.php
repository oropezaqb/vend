@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Company Roles)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/roles">
                            @csrf
                            <div class="form-group">
                                <label for="name">User Name: </label>
                                <input 
                                    class="form-control @error('name') is-danger @enderror" 
                                    type="text" 
                                    name="name" 
                                    id="name" required
                                    value="{{ old('name') }}">
                                @error('name')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </form>
                        <p></p>
                        <h6 class="font-weight-bold">Add</h6>
                        <p>Want to add a company role? Click <a href="{{ url('/roles/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($roles as $role)
                            <div id="content">
                                <div id="name">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/roles/{{ $role->id }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/roles/{{ $role->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/roles/{{ $role->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div>
                                    <div style="display:inline-block;">&nbsp;&nbsp;{!! $role->name !!}</div>
                                </div>
                            </div>
                        @empty
                            <p>No roles recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
