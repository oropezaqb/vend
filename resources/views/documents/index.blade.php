@extends ('layouts.app2')
@section ('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->current_company->company->name }} (Document Types)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div
                        id="page"
                        class="container"
                    >
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <h6 class="font-weight-bold">Search</h6>
                        <form method="GET" action="/documents">
                            @csrf
                            <div class="form-group">
                                <label for="name">Document Name: </label>
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
                        <p>Want to add a new document type? Click <a href="{{ url('/documents/create') }}">here</a>!</p>
                        <p></p>
                        <h6 class="font-weight-bold">List</h6>
                        @forelse ($documents as $document)
                            <div id="content">
                                <div id="title">
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '{{ $document->path() }}';">View</button></div>
                                    <div style="display:inline-block;"><button class="btn btn-link" onclick="location.href = '/documents/{{ $document->id }}/edit';">Edit</button></div>
                                    <div style="display:inline-block;"><form method="POST" action="/documents/{{ $document->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-link" type="submit">Delete</button>
                                    </form></div><div style="display:inline-block;">&nbsp;&nbsp;{{ $document->name }}</div>
                                </div>
                            </div>
                        @empty
                            <p>No document types recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
